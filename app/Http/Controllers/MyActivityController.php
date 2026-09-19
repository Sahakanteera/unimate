<?php

namespace App\Http\Controllers;

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

        return view('my-activities.index', compact('myActivities', 'myParticipations'));
    }
}
