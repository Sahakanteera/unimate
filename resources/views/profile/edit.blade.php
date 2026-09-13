@extends('layouts.app')

@section('title', 'จัดการโปรไฟล์ส่วนตัว - UniMate')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    
    <div class="bg-white rounded-2xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center space-x-5">
            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl shadow-lg ring-4 ring-blue-50 flex-shrink-0">
                @if($user->avatar && Storage::disk('public')->exists($user->avatar))
                    <img src="{{ asset('storage/' . $user->avatar) }}?v={{ time() }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    {{ $user->initials() }}
                @endif
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->isAdmin() ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                        {{ $user->isAdmin() ? '👑 ผู้ดูแลระบบ (Admin)' : '🎓 นักศึกษา (Student)' }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->isActive() ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                        {{ $user->isActive() ? '🟢 ปกติ (Active)' : '🔴 ถูกระงับ (Suspended)' }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">รหัสนักศึกษา: <span class="font-medium text-slate-700">{{ $user->student_id ?? 'N/A (Admin)' }}</span> | อีเมล: <span class="font-medium text-slate-700">{{ $user->email }}</span></p>
                
                @if($user->bio)
                    <p class="text-sm text-slate-600 mt-2 bg-slate-50 p-2.5 rounded-lg border border-slate-200/60">
                        💬 "{{ $user->bio }}"
                    </p>
                @endif
            </div>
        </div>

        @if($user->isAdmin())
        <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm shadow-md transition-all flex items-center gap-2 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            ไปหน้าแดชบอร์ด Admin
        </a>
        @endif
    </div>

    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
        <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            แก้ไขข้อมูลส่วนตัว (Edit Profile)
        </h2>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <input type="hidden" id="avatar_data" name="avatar_data">

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row items-center gap-5">
                <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-lg flex-shrink-0 border border-slate-300">
                    <img id="avatar-preview" src="{{ $user->avatar && Storage::disk('public')->exists($user->avatar) ? asset('storage/' . $user->avatar) : '' }}" class="{{ $user->avatar && Storage::disk('public')->exists($user->avatar) ? '' : 'hidden' }} w-full h-full object-cover">
                    <span id="avatar-placeholder" class="{{ $user->avatar && Storage::disk('public')->exists($user->avatar) ? 'hidden' : '' }}">{{ $user->initials() }}</span>
                </div>

                <div class="flex-grow text-center sm:text-left">
                    <label for="avatar" class="block text-sm font-semibold text-slate-800 mb-1">รูปโปรไฟล์ (Profile Picture)</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" onchange="processAvatar(event)"
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-xs text-slate-500 mt-1">เลือกรูปภาพจากเครื่องของคุณ (ระบบจะช่วยปรับขนาดย่อรูปให้ออโต้อัตโนมัติ)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700">ชื่อ-นามสกุล</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}"
                        class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('name') border-rose-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="student_id" class="block text-sm font-semibold text-slate-700">รหัสนักศึกษา</label>
                    <input id="student_id" name="student_id" type="text" required value="{{ old('student_id', $user->student_id) }}"
                        class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('student_id') border-rose-500 @enderror">
                    @error('student_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">อีเมลผู้ใช้</label>
                <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}"
                    class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="bio" class="block text-sm font-semibold text-slate-700">คำแนะนำตัว / ข้อมูลส่วนตัว (Bio)</label>
                <textarea id="bio" name="bio" rows="3"
                    class="mt-1 w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all"
                    placeholder="เล่าข้อมูลเกี่ยวกับตัวคุณ กิจกรรมที่ชอบ หรือเวลาที่สะดวก">{{ old('bio', $user->bio) }}</textarea>
            </div>

            <hr class="border-slate-100">

            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900">เปลี่ยนรหัสผ่าน (หากไม่ต้องการเปลี่ยน ให้เว้นว่างไว้)</h3>
                    <span class="text-xs text-slate-500 font-medium">(ขั้นต่ำ 6 ตัวอักษร)</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-600">รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)</label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password"
                                class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all @error('password') border-rose-500 @enderror"
                                placeholder="อย่างน้อย 6 ตัวอักษร">
                            <button type="button" onclick="togglePassword('password', 'eye-edit-pass')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="แสดง/ซ่อนรหัสผ่าน">
                                <svg id="eye-edit-pass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-600">ยืนยันรหัสผ่านใหม่</label>
                        <div class="mt-1 relative">
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                class="w-full px-4 py-2.5 pr-11 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm transition-all"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye-edit-confirm')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" title="แสดง/ซ่อนรหัสผ่าน">
                                <svg id="eye-edit-confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all">
                    บันทึกการเปลี่ยนแปลง
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function processAvatar(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const maxDim = 400;
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxDim) {
                        height = Math.round(height * (maxDim / width));
                        width = maxDim;
                    }
                } else {
                    if (height > maxDim) {
                        width = Math.round(width * (maxDim / height));
                        height = maxDim;
                    }
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/png', 0.9);
                
                document.getElementById('avatar_data').value = dataUrl;

                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');
                preview.src = dataUrl;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
</script>
@endsection
