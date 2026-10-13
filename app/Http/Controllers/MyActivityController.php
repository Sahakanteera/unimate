<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyActivityController extends Controller
{
    /** แสดงกิจกรรมที่ฉันสร้าง + กิจกรรมที่ฉันขอเข้าร่วม */
    public function index(Request $request): View
    {
        $user = $request->user();

        // กิจกรรมที่ฉันสร้าง พร้อมนับคำขอ pending และ approved
        $myActivities = $user->activities()
            ->with('category')
            ->withCount([
                'participants as pending_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'participants as approved_count' => function ($query) {
                    $query->where('status', 'approved');
                },
            ])
            ->latest()
            ->get();

        // กิจกรรมที่ฉันขอเข้าร่วม (ทุกสถานะ)
        $myParticipations = $user->participations()
            ->with(['activity.user', 'activity.category'])
            ->latest()
            ->get();

        // กิจกรรมที่ฉันรีวิวแล้ว ใช้ซ่อนปุ่ม "ให้คะแนน" โดยไม่ต้อง query ทีละการ์ด
        $reviewedActivityIds = $user->reviews()->pluck('activity_id')->all();

        // งานรีวิวคนที่ค้าง ต่อกิจกรรม (ทั้งที่ฉันจัดและที่ฉันมาร่วมจริง) เงื่อนไขเดียวกับ Activity::userReviewClosedReason
        $open = fn (Activity $activity) => $activity->status !== 'cancelled' && $activity->isEnded() && ! $activity->isHidden();
        $activityIds = $myActivities->filter($open)->pluck('id')
            ->merge($myParticipations
                ->filter(fn (ActivityParticipant $p) => $p->isApproved() && $p->attendance === 'present' && $open($p->activity))
                ->pluck('activity_id'))
            ->unique()->values()->all();

        // คนอื่นในกลุ่มที่ไปจริง = ผู้เข้าร่วมที่มา (นับรวมฉันถ้าฉันเป็นผู้เข้าร่วม) + ผู้จัด - ฉัน = จำนวนผู้เข้าร่วมที่มา
        $presentCounts = ActivityParticipant::whereIn('activity_id', $activityIds)
            ->where('status', 'approved')
            ->where('attendance', 'present')
            ->selectRaw('activity_id, count(*) as total')
            ->groupBy('activity_id')
            ->pluck('total', 'activity_id');
        $writtenCounts = $user->reviewsWritten()
            ->whereIn('activity_id', $activityIds)
            // รีวิวของคนที่ถูกแก้เป็นขาดแล้ว ไม่หักงานของคนอื่นที่ยังมาร่วมจริง
            ->where(function ($query) {
                $query->whereHas('activity', fn ($activity) => $activity->whereColumn('activities.user_id', 'user_reviews.reviewee_id'))
                    ->orWhereExists(function ($participant) {
                        $participant->selectRaw('1')->from('activity_participants')
                            ->whereColumn('activity_participants.activity_id', 'user_reviews.activity_id')
                            ->whereColumn('activity_participants.user_id', 'user_reviews.reviewee_id')
                            ->where('status', 'approved')->where('attendance', 'present');
                    });
            })
            ->selectRaw('activity_id, count(*) as total')
            ->groupBy('activity_id')
            ->pluck('total', 'activity_id');

        /** @var array<int, int> $pendingPeople activity_id => จำนวนคนที่ยังไม่ได้รีวิว */
        $pendingPeople = [];
        foreach ($activityIds as $id) {
            $pendingPeople[$id] = max(0, (int) ($presentCounts[$id] ?? 0) - (int) ($writtenCounts[$id] ?? 0));
        }

        return view('my-activities.index', compact('myActivities', 'myParticipations', 'reviewedActivityIds', 'pendingPeople'));
    }
}
