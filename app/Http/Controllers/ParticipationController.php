<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Notifications\ActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParticipationController extends Controller
{
    /** ส่งคำขอเข้าร่วมกิจกรรม */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('join', $activity);

        $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ], [
            'message.max' => 'ข้อความประกอบคำขอต้องไม่เกิน 500 ตัวอักษร',
        ]);

        // ตรวจว่ากิจกรรมยังไม่เต็ม
        if ($activity->isFull()) {
            return back()->with('error', 'กิจกรรมนี้เต็มแล้ว ไม่สามารถส่งคำขอได้');
        }

        // ตรวจว่าไม่มีคำขอ active (pending/approved) อยู่แล้ว
        $existing = $activity->participants()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return back()->with('error', 'คุณมีคำขอเข้าร่วมกิจกรรมนี้อยู่แล้ว');
        }

        // ถ้าเคยถูก rejected/cancelled มาก่อน ให้ลบแล้วสร้างใหม่
        $activity->participants()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['rejected', 'cancelled'])
            ->delete();

        $participant = new ActivityParticipant($request->only('message'));
        $participant->activity()->associate($activity);
        $participant->user()->associate($request->user());
        $participant->status = 'pending';
        $participant->save();

        return back()->with('success', 'ส่งคำขอเข้าร่วมกิจกรรมเรียบร้อยแล้ว รอผู้จัดอนุมัติ');
    }

    /** ถอนคำขอ (pending) หรือถอนตัว (approved) */
    public function cancel(Request $request, Activity $activity): RedirectResponse
    {
        $participant = $activity->participants()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'approved'])
            ->firstOrFail();

        $wasApproved = $participant->isApproved();
        $participant->status = 'cancelled';
        $participant->save();

        $message = $wasApproved
            ? 'ถอนตัวจากกิจกรรมเรียบร้อยแล้ว'
            : 'ถอนคำขอเข้าร่วมเรียบร้อยแล้ว';

        return back()->with('success', $message);
    }

    /** แสดงรายการคำขอ (สำหรับ Host) */
    public function requests(Request $request, Activity $activity): View
    {
        Gate::authorize('manageRequests', $activity);

        $statusFilter = $request->input('status', 'pending');
        $allowedStatuses = ['pending', 'approved', 'rejected', 'all'];

        if (! in_array($statusFilter, $allowedStatuses)) {
            $statusFilter = 'pending';
        }

        $query = $activity->participants()->with('user')->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $participants = $query->get();

        // นับแต่ละสถานะสำหรับแท็บ
        $counts = $activity->participants()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('activities.requests', compact('activity', 'participants', 'statusFilter', 'counts'));
    }

    /** Host อนุมัติคำขอ — ใช้ DB::transaction ป้องกัน race condition */
    public function approve(Activity $activity, ActivityParticipant $participant): RedirectResponse
    {
        Gate::authorize('manageRequests', $activity);

        if ($participant->activity_id !== $activity->id) {
            abort(404);
        }

        if (! $participant->isPending()) {
            return back()->with('error', 'คำขอนี้ไม่ได้อยู่ในสถานะรออนุมัติ');
        }

        $approved = DB::transaction(function () use ($activity, $participant) {
            // ล็อกแถว activity เพื่อป้องกันการอนุมัติพร้อมกัน
            $lockedActivity = Activity::lockForUpdate()->find($activity->id);

            if ($lockedActivity->isFull()) {
                return false;
            }

            $participant->status = 'approved';
            $participant->save();

            return true;
        });

        if (! $approved) {
            return back()->with('error', 'กิจกรรมเต็มแล้ว ไม่สามารถอนุมัติเพิ่มได้');
        }

        // ส่งการแจ้งเตือนว่าได้รับอนุมัติ
        $participant->user->notify(new ActivityNotification("คำขอเข้าร่วมกิจกรรม '{$activity->title}' ของคุณได้รับการอนุมัติแล้ว!"));

        return back()->with('success', 'อนุมัติคำขอของ '.$participant->user->name.' เรียบร้อยแล้ว');
    }

    /** Host ปฏิเสธคำขอ */
    public function reject(Activity $activity, ActivityParticipant $participant): RedirectResponse
    {
        Gate::authorize('manageRequests', $activity);

        if ($participant->activity_id !== $activity->id) {
            abort(404);
        }

        if (! $participant->isPending()) {
            return back()->with('error', 'คำขอนี้ไม่ได้อยู่ในสถานะรออนุมัติ');
        }

        $participant->status = 'rejected';
        $participant->save();

        // ส่งการแจ้งเตือนว่าถูกปฏิเสธ
        $participant->user->notify(new ActivityNotification("คำขอเข้าร่วมกิจกรรม '{$activity->title}' ของคุณไม่ได้รับการอนุมัติ"));

        return back()->with('success', 'ปฏิเสธคำขอของ '.$participant->user->name.' เรียบร้อยแล้ว');
    }

   public function updateAttendance(Request $request, Activity $activity, ActivityParticipant $participant): RedirectResponse
    {
        Gate::authorize('manageRequests', $activity);

        if ($participant->activity_id !== $activity->id) {
            abort(404);
        }

        // เพิ่มการตรวจสอบตรงนี้: ห้ามเช็กชื่อคนที่ยังไม่ผ่านการอนุมัติ!
        if (! $participant->isApproved()) {
            return back()->with('error', 'ไม่สามารถเช็กชื่อได้ เนื่องจากผู้ใช้นี้ยังไม่ได้รับอนุมัติให้เข้าร่วมกิจกรรม');
        }

        $request->validate([
            'attendance' => ['required', 'in:present,absent'],
        ]);

        $participant->attendance = $request->attendance;
        $participant->save();

        return back()->with('success', 'บันทึกการเช็กชื่อเรียบร้อยแล้ว');
    }
}
