<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use App\Support\ActivityDate;
use App\Support\ActivityTime;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        ActivityTime::normalize($request, 'time_from');
        ActivityTime::normalize($request, 'time_to');
        if ($request->has('date')) {
            $request->merge(['date' => ActivityDate::toIso($request->input('date'))]);
        }
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'time_from' => ['nullable', 'date_format:H:i'],
            'time_to' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', 'in:available,full,ongoing,ended,cancelled,all'],
        ]);
        $now = now();
        // ดึงเจ้าของและหมวดหมู่ล่วงหน้า ลด query ซ้ำเมื่อแสดงการ์ดกิจกรรมแต่ละใบ
        $query = Activity::with(['user', 'category'])
            ->withCount(['approvedParticipants as approved_participants_count', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->whereNull('hidden_at') // ส่วนที่ 5: ไม่แสดงกิจกรรมที่ Admin ซ่อน
            // กิจกรรมที่ยังไม่จบขึ้นก่อนตามเวลาเริ่ม ส่วนที่จบแล้วต่อท้ายโดยจบล่าสุดก่อน
            ->orderByRaw('CASE WHEN ends_at < ? THEN 1 ELSE 0 END', [$now])
            ->orderByRaw('CASE WHEN ends_at < ? THEN NULL ELSE starts_at END', [$now])
            ->orderByRaw('CASE WHEN ends_at < ? THEN starts_at END DESC', [$now])
            ->orderBy('id');
        $status = $filters['status'] ?? 'available';
        if (in_array($status, ['available', 'full'], true)) {
            // เฉพาะกิจกรรมที่ยังรับคำขอได้ นับเฉพาะผู้ที่อนุมัติแล้ว ไม่รวมคำขอรออนุมัติ
            $query->where('status', 'published')->where('starts_at', '>', $now);
            $approvedCount = "SELECT COUNT(*) FROM activity_participants WHERE activity_participants.activity_id = activities.id AND activity_participants.status = 'approved'";
            $query->whereRaw('('.$approvedCount.') '.($status === 'full' ? '>=' : '<').' activities.capacity');
        } elseif ($status === 'ongoing') {
            $query->where('status', 'published')->where('starts_at', '<=', $now)->where('ends_at', '>', $now);
        } elseif ($status === 'ended') {
            $query->where('status', 'published')->where('ends_at', '<=', $now);
        } elseif ($status === 'cancelled') {
            // เก็บกิจกรรมที่ยกเลิกไว้ให้ค้นหาได้ตลอด ไม่มีเงื่อนไขจำกัด 24 ชั่วโมง
            $query->where('status', 'cancelled');
        }
        if (! empty($filters['q'])) {
            // ครอบ OR ด้วยวงเล็บ เพื่อให้ทั้งชื่อและรายละเอียดอยู่ภายใต้ตัวกรองอื่นด้วย
            $query->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%'.$filters['q'].'%')
                    ->orWhere('description', 'like', '%'.$filters['q'].'%');
            });
        }
        if (! empty($filters['location'])) {
            $query->where('location', 'like', '%'.$filters['location'].'%');
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['date'])) {
            $query->whereDate('starts_at', $filters['date']);
        }
        $from = ! empty($filters['time_from']) ? $filters['time_from'].':00' : null;
        $to = ! empty($filters['time_to']) ? $filters['time_to'].':00' : null;
        if ($from && $to && $from > $to) {
            $query->where(function ($query) use ($from, $to) {
                $query->whereTime('starts_at', '>=', $from)->orWhereTime('starts_at', '<=', $to);
            });
        } else {
            if ($from) {
                $query->whereTime('starts_at', '>=', $from);
            }
            if ($to) {
                $query->whereTime('starts_at', '<=', $to);
            }
        }

        // เก็บตัวกรองไว้ใน URL เมื่อผู้ใช้เปลี่ยนหน้าผลการค้นหา
        return view('activities.index', ['activities' => $query->paginate(12)->withQueryString(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('activities.form', ['activity' => new Activity, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(SaveActivityRequest $request): RedirectResponse
    {
        $activity = new Activity($request->safe()->only(['category_id', 'title', 'description', 'location', 'google_maps_url', 'starts_at', 'ends_at', 'capacity']));
        if ($request->hasFile('location_image')) {
            $imagePath = $request->file('location_image')->store('activity-locations', 'public');
            if ($imagePath === false) {
                abort(503, 'บันทึกรูปสถานที่ไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
            }
            $activity->location_image_path = $imagePath;
        }
        // กำหนดเจ้าของและสถานะที่เซิร์ฟเวอร์ ไม่รับค่าที่ผู้ใช้ปลอมส่งมาจากฟอร์ม
        $activity->user()->associate($request->user());
        $activity->status = 'published';
        $activity->save();

        return redirect()->route('activities.show', $activity)->with('success', 'ประกาศกิจกรรมเรียบร้อยแล้ว');
    }

    public function show(Request $request, Activity $activity): View
    {
        // ส่วนที่ 5: กิจกรรมที่ถูกซ่อน เห็นได้เฉพาะเจ้าของและ Admin
        abort_if($activity->isHidden() && $request->user()->id !== $activity->user_id && ! $request->user()->isAdmin(), 404);

        $activity->load(['user', 'category']);

        // ส่วนที่ 3: ข้อมูลการเข้าร่วมสำหรับหน้ารายละเอียด
        $myParticipation = $activity->participants()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        $approvedMembers = $activity->approvedParticipants()->with('user')->get();

        $pendingCount = $activity->participants()->where('status', 'pending')->count();

        // ส่วนที่ 5: รีวิวและการรายงาน
        $reviews = $activity->reviews()->with('user')->latest()->get();
        // รีวิวของฉันขึ้นบนสุด ตามด้วยรีวิวอื่นจากใหม่ไปเก่า
        [$mine, $others] = $reviews->partition(fn ($review) => $review->user_id === $request->user()->id);
        $reviews = $mine->concat($others)->values();
        $myReview = $mine->first();
        $reviewBlockReason = $activity->reviewBlockReason($request->user());

        // รีวิวเพื่อนร่วมกิจกรรม: null = ไม่แสดงปุ่ม (ไม่ได้ไปจริง หรือกิจกรรมยังรีวิวไม่ได้)
        // ไม่งั้นเป็นจำนวนคนที่ยังไม่ได้รีวิว ใช้รายชื่อสมาชิกที่โหลดแล้วแทนการ query ซ้ำ
        $peopleToReview = null;
        $me = $request->user();
        $presentIds = $approvedMembers->where('attendance', 'present')->pluck('user_id');
        $isAttendee = $me->id === $activity->user_id || $presentIds->contains($me->id);
        if ($isAttendee && $activity->userReviewClosedReason() === null) {
            $others = $presentIds->push($activity->user_id)->reject(fn ($id) => $id === $me->id);
            $reviewed = $activity->userReviews()->where('reviewer_id', $me->id)->whereIn('reviewee_id', $others)->count();
            $peopleToReview = max(0, $others->count() - $reviewed);
        }

        return view('activities.show', compact('activity', 'myParticipation', 'approvedMembers', 'pendingCount', 'reviews', 'myReview', 'reviewBlockReason', 'peopleToReview'));
    }

    public function edit(Activity $activity): View
    {
        // ป้องกันการพิมพ์ URL แก้ไขของคนอื่นโดยตรง แม้หน้าเว็บจะซ่อนปุ่มแล้ว
        Gate::authorize('update', $activity);
        abort_if($activity->status === 'cancelled', 409, 'กิจกรรมนี้ถูกยกเลิกแล้ว');

        return view('activities.form', ['activity' => $activity, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(SaveActivityRequest $request, Activity $activity): RedirectResponse
    {
        // SaveActivityRequest ตรวจสิทธิ์เจ้าของก่อนเข้าเมธอดนี้ ทั้งคำขอ PUT และ PATCH
        abort_if($activity->status === 'cancelled', 409, 'กิจกรรมนี้ถูกยกเลิกแล้ว');
        abort_if($activity->isEnded(), 409, 'กิจกรรมนี้จบแล้ว ไม่สามารถแก้ไขรายละเอียดได้');
        $oldImage = $activity->location_image_path;
        $activity->fill($request->safe()->only(['category_id', 'title', 'description', 'location', 'google_maps_url', 'starts_at', 'ends_at', 'capacity']));
        if ($request->has('google_maps_url')) {
            // เมื่อเจ้าของเปลี่ยนหรือล้างลิงก์ ให้เลิกใช้หมุดเดิมของโพสต์ด้วย
            $activity->latitude = null;
            $activity->longitude = null;
        }
        if ($request->hasFile('location_image')) {
            $imagePath = $request->file('location_image')->store('activity-locations', 'public');
            if ($imagePath === false) {
                abort(503, 'บันทึกรูปสถานที่ไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
            }
            $activity->location_image_path = $imagePath;
        }
        // เปลี่ยนวันเวลาหรือสถานที่ ต้องบอกคนที่จะมาร่วม (ตรวจก่อน save เพราะหลัง save isDirty จะเป็น false)
        $scheduleChanged = $activity->isDirty(['starts_at', 'ends_at', 'location']);
        $activity->save();
        if ($scheduleChanged) {
            $activity->notifyParticipants("กิจกรรม '{$activity->title}' มีการเปลี่ยนวันเวลาหรือสถานที่ กรุณาตรวจสอบรายละเอียดอีกครั้ง");
        }
        if ($oldImage && $oldImage !== $activity->location_image_path) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('activities.show', $activity)->with('success', 'แก้ไขกิจกรรมเรียบร้อยแล้ว');
    }

    public function deleteLocationImage(Activity $activity): JsonResponse
    {
        Gate::authorize('update', $activity);
        $deleted = DB::transaction(function () use ($activity) {
            $current = Activity::query()->whereKey($activity->getKey())->lockForUpdate()->firstOrFail();
            abort_if($current->status === 'cancelled', 409, 'กิจกรรมนี้ถูกยกเลิกแล้ว');
            abort_if($current->isEnded(), 409, 'กิจกรรมนี้จบแล้ว ไม่สามารถลบรูปสถานที่ได้');
            $path = $current->location_image_path;
            if ($path && Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
                return false;
            }
            $current->location_image_path = null;
            $current->save();

            return true;
        });
        if (! $deleted) {
            return response()->json(['message' => 'ลบรูปไม่สำเร็จ กรุณาลองใหม่อีกครั้ง'], 503);
        }

        return response()->json(['message' => 'ลบรูปสถานที่แล้ว']);
    }

    public function cancel(Activity $activity): RedirectResponse
    {
        Gate::authorize('cancel', $activity);
        // เก็บโพสต์ไว้เป็นประวัติ โดยเปลี่ยนสถานะแทนการลบข้อมูลกิจกรรม
        $activity->status = 'cancelled';
        $activity->save();
        $activity->notifyParticipants("กิจกรรม '{$activity->title}' ถูกยกเลิกโดยผู้จัด", 'danger');

        return redirect()->route('activities.show', $activity)->with('success', 'ยกเลิกกิจกรรมเรียบร้อยแล้ว');
    }
}
