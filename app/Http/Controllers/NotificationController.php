<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /** แสดงรายการแจ้งเตือนของผู้ใช้ ใหม่สุดก่อน แบ่งหน้าละ 20 */
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->paginate(20);
        $unreadCount = $request->user()->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /** กดการแจ้งเตือน: ทำเครื่องหมายว่าอ่านแล้ว แล้วพาไปหน้าที่เกี่ยวข้อง */
    public function open(Request $request, string $notification): RedirectResponse
    {
        // ค้นเฉพาะในการแจ้งเตือนของตัวเอง กันการเปิดของคนอื่นด้วยการเดา id
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        $url = $item->data['url'] ?? null;

        // รับเฉพาะ path ภายในเว็บ (ขึ้นต้นด้วย / แต่ไม่ใช่ //) กัน open redirect
        if (is_string($url) && str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return redirect($url);
        }

        return redirect()->route('notifications.index');
    }

    /** กดปุ่มทำเครื่องหมายว่าอ่านทั้งหมดแล้ว */
    public function markAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'ทำเครื่องหมายว่าอ่านทั้งหมดแล้ว');
    }
}
