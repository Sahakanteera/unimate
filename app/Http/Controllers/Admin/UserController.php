<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $statusFilter = $request->query('status');
        $roleFilter = $request->query('role');

        $query = User::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        if (! empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (! empty($roleFilter)) {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total' => User::count(),
            'students' => User::where('role', 'student')->count(),
            'active' => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'search', 'statusFilter', 'roleFilter'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', '❌ คุณไม่สามารถระงับบัญชีของตัวเองได้');
        }

        if ($user->status === 'active') {
            $user->status = 'suspended';
            $message = "ระงับการใช้งานบัญชีของคุณ {$user->name} เรียบร้อยแล้ว";
        } else {
            $user->status = 'active';
            $message = "เปิดใช้งานบัญชีของคุณ {$user->name} เรียบร้อยแล้ว";
        }

        $user->save();

        return back()->with('success', $message);
    }
}
