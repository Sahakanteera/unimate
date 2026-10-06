<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /** แสดงรายการแจ้งเตือนทั้งหมดของผู้ใช้ */
    public function index(): View
    {
        $notifications = auth()->user()->notifications;
        return view('notifications.index', compact('notifications'));
    }

    /** กดปุ่มทำเครื่องหมายว่าอ่านทั้งหมดแล้ว */
    public function markAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'ทำเครื่องหมายว่าอ่านทั้งหมดแล้ว');
    }
}