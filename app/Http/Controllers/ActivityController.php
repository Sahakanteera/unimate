<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:published,cancelled,all'],
        ]);
        // ดึงเจ้าของและหมวดหมู่ล่วงหน้า ลด query ซ้ำเมื่อแสดงการ์ดกิจกรรมแต่ละใบ
        $query = Activity::with(['user', 'category'])
            ->withCount(['approvedParticipants as approved_participants_count', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->whereNull('hidden_at') // ส่วนที่ 5: ไม่แสดงกิจกรรมที่ Admin ซ่อน
            ->orderBy('starts_at')->orderBy('id');
        $status = $filters['status'] ?? 'published';
        if ($status !== 'all') {
            $query->where('status', $status);
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

        // เก็บตัวกรองไว้ใน URL เมื่อผู้ใช้เปลี่ยนหน้าผลการค้นหา
        return view('activities.index', ['activities' => $query->paginate(12)->withQueryString(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('activities.form', ['activity' => new Activity, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(SaveActivityRequest $request): RedirectResponse
    {
        $activity = new Activity($request->validated());
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
        $reviewBlockReason = $activity->reviewBlockReason($request->user());

        return view('activities.show', compact('activity', 'myParticipation', 'approvedMembers', 'pendingCount', 'reviews', 'reviewBlockReason'));
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
        $activity->update($request->validated());

        return redirect()->route('activities.show', $activity)->with('success', 'แก้ไขกิจกรรมเรียบร้อยแล้ว');
    }

    public function cancel(Activity $activity): RedirectResponse
    {
        Gate::authorize('cancel', $activity);
        // เก็บโพสต์ไว้เป็นประวัติ โดยเปลี่ยนสถานะแทนการลบข้อมูลกิจกรรม
        $activity->status = 'cancelled';
        $activity->save();

        return redirect()->route('activities.show', $activity)->with('success', 'ยกเลิกกิจกรรมเรียบร้อยแล้ว');
    }
}
