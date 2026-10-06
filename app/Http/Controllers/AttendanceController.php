<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Notifications\ActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::latest()->get();
        $notifications = auth()->check() ? auth()->user()->notifications : collect();

        return view('attendance.index', compact('attendances', 'notifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => [
                'required',
                'min:9',
                'max:13',
                'regex:/^[0-9-]+$/',
            ],
        ], [
            'student_id.required' => 'กรุณากรอกรหัสนักศึกษา',
            'student_id.min' => 'รหัสนักศึกษาต้องมีอย่างน้อย 9 หลัก',
            'student_id.max' => 'รหัสนักศึกษาต้องไม่เกิน 13 หลัก',
            'student_id.regex' => 'รหัสนักศึกษาไม่ถูกต้อง (ต้องเป็นตัวเลขและมีเครื่องหมายขีด)',
        ]);

        try {
            $attendance = new Attendance;
            $attendance->student_id = $request->student_id;
            $attendance->save();

            if (auth()->check()) {
                auth()->user()->notify(new ActivityNotification('เช็กชื่อเข้ากิจกรรมด้วยรหัส ' . $request->student_id . ' สำเร็จ'));
            }

            return redirect()->back()->with('success', 'บันทึกการเช็กชื่อและส่งแจ้งเตือนสำเร็จ!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ระบบฐานข้อมูลขัดข้อง: ' . $e->getMessage());
        }
    }
}