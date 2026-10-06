<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'student_id')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'student_id.required' => 'กรุณากรอกรหัสนักศึกษา',
            'student_id.unique' => 'รหัสนักศึกษานี้ถูกใช้งานโดยผู้ใช้อื่นแล้ว',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.unique' => 'อีเมลนี้ถูกใช้งานโดยผู้ใช้อื่นแล้ว',
            'password.min' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'password.confirmed' => 'ยืนยันรหัสผ่านใหม่ไม่ตรงกัน',
        ]);

        if ($request->filled('avatar_data')) {
            $imageData = $request->input('avatar_data');

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $data = substr($imageData, strpos($imageData, ',') + 1);
                $data = base64_decode($data);

                if ($data !== false) {
                    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }

                    $filename = 'avatars/avatar_'.$user->id.'_'.time().'.png';
                    Storage::disk('public')->put($filename, $data);
                    $user->avatar = $filename;
                }
            }
        } elseif ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            if ($file && $file->isValid()) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $path = $file->store('avatars', 'public');
                $user->avatar = $path;
            }
        }

        $user->name = $validated['name'];
        $user->student_id = $validated['student_id'];
        $user->email = $validated['email'];
        $user->bio = $validated['bio'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'บันทึกการแก้ไขโปรไฟล์และรูปภาพเรียบร้อยแล้ว');
    }
}
