@extends('layouts.app')
@section('title', $activity->title.' | UniMate')
@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('activities.index') }}" class="text-blue-600">← กลับหน้ากิจกรรม</a>
    <article class="mt-5 bg-white border rounded-2xl p-6 sm:p-8">
        @if($activity->status === 'cancelled')<div role="status" class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-4 mb-5">กิจกรรมนี้ถูกยกเลิกแล้ว</div>@endif
        @if($activity->isHidden())<div role="status" class="bg-slate-800 text-white rounded-xl p-4 mb-5">กิจกรรมนี้ถูกซ่อนโดยผู้ดูแลระบบ ผู้ใช้อื่นจะมองไม่เห็น<br><span class="text-sm text-slate-300">เหตุผล: {{ $activity->hidden_reason }}</span></div>@endif
        <p class="text-blue-600 mb-2">{{ $activity->category->name }}</p>
        <h1 class="text-3xl font-bold break-words">{{ $activity->title }}</h1>
        <p class="text-slate-500 mt-3">ประกาศโดย {{ $activity->user->name }} · อัปเดต {{ $activity->updated_at->format('d/m/Y H:i') }}</p>
        <p class="mt-2 text-sm">
            @if($reviews->isNotEmpty())
                <span class="text-amber-500 font-semibold">★ {{ number_format($reviews->avg('rating'), 1) }}</span>
                <span class="text-slate-500">จาก {{ $reviews->count() }} รีวิว</span>
            @else
                <span class="text-slate-400">ยังไม่มีรีวิว</span>
            @endif
        </p>
        <dl class="grid sm:grid-cols-2 gap-5 bg-slate-50 rounded-xl p-5 my-6">
            <div><dt class="text-slate-500">เวลาเริ่ม (เวลาไทย)</dt><dd>{{ $activity->starts_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-500">เวลาสิ้นสุด (เวลาไทย)</dt><dd>{{ $activity->ends_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt class="text-slate-500">สถานที่</dt><dd class="break-words">{{ $activity->location }}</dd></div>
            <div>
                <dt class="text-slate-500">จำนวนผู้เข้าร่วม (ไม่รวมผู้ประกาศ)</dt>
                <dd class="font-semibold">{{ $activity->approvedCount() }} / {{ $activity->capacity }} คน</dd>
                @php $pct = $activity->capacity > 0 ? min(100, round($activity->approvedCount() / $activity->capacity * 100)) : 0; @endphp
                <div class="mt-2 w-full bg-slate-200 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full {{ $pct >= 100 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs mt-1 {{ $activity->isFull() ? 'text-rose-600' : 'text-slate-500' }}">
                    {{ $activity->isFull() ? 'เต็มแล้ว' : 'ว่าง '.$activity->remainingSlots().' ที่' }}
                </p>
            </div>
        </dl>
        <h2 class="font-semibold text-xl mb-3">รายละเอียดกิจกรรม</h2>
        <p class="whitespace-pre-wrap break-words leading-relaxed">{{ $activity->description }}</p>

        {{-- ส่วนที่ 3: ปุ่มขอเข้าร่วม / ถอนคำขอ / ถอนตัว --}}
        @if($activity->status === 'published' && $activity->starts_at->isFuture())
            @if(Auth::id() !== $activity->user_id)
                @if($myParticipation && in_array($myParticipation->status, ['pending', 'approved']))
                    {{-- ผู้ใช้มีคำขอ active อยู่แล้ว --}}
                    <div class="mt-8 border-t pt-6">
                        <div class="flex items-center gap-3 mb-3">
                            @if($myParticipation->isPending())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-50 text-amber-700 border border-amber-200">รอการอนุมัติ</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">เข้าร่วมแล้ว</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('activities.cancel-request', $activity) }}" onsubmit="return confirm('{{ $myParticipation->isApproved() ? 'ยืนยันถอนตัวจากกิจกรรม?' : 'ยืนยันถอนคำขอเข้าร่วม?' }}')">
                            @csrf @method('PATCH')
                            <button class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl px-5 py-3 hover:bg-rose-100 transition-colors">
                                {{ $myParticipation->isApproved() ? 'ถอนตัวจากกิจกรรม' : 'ถอนคำขอเข้าร่วม' }}
                            </button>
                        </form>
                    </div>
                @elseif(! $activity->isFull())
                    {{-- ฟอร์มส่งคำขอเข้าร่วม --}}
                    <div class="mt-8 border-t pt-6">
                        <h3 class="font-semibold text-lg mb-3">ขอเข้าร่วมกิจกรรม</h3>
                        <form method="POST" action="{{ route('activities.join', $activity) }}">
                            @csrf
                            <label class="block mb-4">
                                <span class="text-sm text-slate-600">ข้อความถึงผู้จัด (ไม่บังคับ)</span>
                                <textarea name="message" rows="2" maxlength="500" placeholder="เช่น อยากร่วมด้วยครับ เล่นบาสได้" class="block w-full border rounded-xl p-3 mt-1">{{ old('message') }}</textarea>
                            </label>
                            <button class="bg-blue-600 text-white rounded-xl px-6 py-3 hover:bg-blue-700 transition-colors">ส่งคำขอเข้าร่วม</button>
                        </form>
                    </div>
                @else
                    <p class="mt-8 pt-5 border-t text-sm text-rose-600 font-medium">กิจกรรมนี้เต็มแล้ว ไม่สามารถส่งคำขอได้</p>
                @endif
            @endif
        @endif

        {{-- รายชื่อสมาชิก (approved) --}}
        @if($approvedMembers->isNotEmpty())
        <div class="mt-8 border-t pt-6">
            <h3 class="font-semibold text-lg mb-4">สมาชิกที่เข้าร่วม ({{ $approvedMembers->count() }})</h3>
            <div class="space-y-3">
                @foreach($approvedMembers as $member)
                <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm overflow-hidden flex-shrink-0">
                        @if($member->user->avatar && Storage::disk('public')->exists($member->user->avatar))
                            <img src="{{ asset('storage/' . $member->user->avatar) }}" alt="{{ $member->user->name }}" class="w-full h-full object-cover">
                        @else
                            {{ $member->user->initials() }}
                        @endif
                    </div>
                    <div>
                        <p class="font-medium text-sm">{{ $member->user->name }}</p>
                        <p class="text-xs text-slate-500">เข้าร่วมเมื่อ {{ $member->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ส่วนที่ 5: รีวิวกิจกรรม --}}
        <div class="mt-8 border-t pt-6">
            <h3 class="font-semibold text-lg mb-4">รีวิวจากผู้เข้าร่วม ({{ $reviews->count() }})</h3>

            @if($reviewBlockReason === null)
                <form method="POST" action="{{ route('activities.reviews.store', $activity) }}" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5">
                    @csrf
                    <p class="font-medium mb-2">ให้คะแนนกิจกรรมนี้</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @for($i = 5; $i >= 1; $i--)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked(old('rating') == $i) required>
                                <span class="inline-block px-3 py-1.5 rounded-lg border bg-white peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500">{{ str_repeat('★', $i) }}</span>
                            </label>
                        @endfor
                    </div>
                    @error('rating')<p class="text-sm text-rose-600 mb-2">{{ $message }}</p>@enderror
                    <textarea name="comment" rows="2" maxlength="1000" placeholder="ความคิดเห็นเพิ่มเติม (ไม่บังคับ)" class="block w-full border rounded-xl p-3 bg-white">{{ old('comment') }}</textarea>
                    <button class="mt-3 bg-amber-500 text-white rounded-xl px-5 py-2 hover:bg-amber-600 transition-colors">ส่งรีวิว</button>
                </form>
            @elseif($activity->isEnded() && Auth::id() !== $activity->user_id)
                <p class="text-sm text-slate-500 bg-slate-50 rounded-xl p-3 mb-5">{{ $reviewBlockReason }}</p>
            @endif

            <div class="space-y-3">
                @forelse($reviews as $review)
                    <div class="bg-slate-50 rounded-xl p-4">
                        <div class="flex justify-between items-center">
                            <p class="font-medium text-sm">{{ $review->user->name }}</p>
                            <p class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                        </div>
                        @if($review->comment)<p class="text-sm text-slate-700 mt-2 whitespace-pre-wrap break-words">{{ $review->comment }}</p>@endif
                        <p class="text-xs text-slate-400 mt-1">{{ $review->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">{{ $activity->isEnded() ? 'ยังไม่มีรีวิว' : 'รีวิวได้หลังกิจกรรมจบ' }}</p>
                @endforelse
            </div>
        </div>

        {{-- ส่วนที่ 5: รายงานกิจกรรมหรือผู้ใช้ (ไม่แสดงให้เจ้าของกิจกรรม) --}}
        @if(Auth::id() !== $activity->user_id)
        <details class="mt-8 border-t pt-6 group" @if($errors->has('reason') || $errors->has('target')) open @endif>
            <summary class="cursor-pointer text-sm text-rose-600 hover:text-rose-700 font-medium">🚩 รายงานปัญหา</summary>
            <form method="POST" action="{{ route('activities.reports.store', $activity) }}" class="mt-4 bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-3">
                @csrf
                <label class="block">
                    <span class="text-sm text-slate-600">ต้องการรายงาน</span>
                    <select name="target" class="block w-full border rounded-xl p-2 mt-1 bg-white">
                        <option value="activity">กิจกรรมนี้</option>
                        <option value="user:{{ $activity->user_id }}">ผู้ประกาศ: {{ $activity->user->name }}</option>
                        @foreach($approvedMembers as $member)
                            @if($member->user_id !== Auth::id())
                                <option value="user:{{ $member->user_id }}">สมาชิก: {{ $member->user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm text-slate-600">เหตุผล</span>
                    <textarea name="reason" rows="3" maxlength="1000" required placeholder="อธิบายปัญหาที่พบ เช่น เนื้อหาไม่เหมาะสม หรือไม่มาตามนัด" class="block w-full border rounded-xl p-3 mt-1 bg-white">{{ old('reason') }}</textarea>
                </label>
                @error('reason')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                <button class="bg-rose-600 text-white rounded-xl px-5 py-2 hover:bg-rose-700 transition-colors">ส่งรายงาน</button>
            </form>
        </details>
        @endif

        {{-- ซ่อนปุ่มตาม Policy; ฝั่ง Controller/FormRequest ยังตรวจสิทธิ์ทุกคำขอด้วย --}}
        @can('update', $activity)
            @if($activity->status !== 'cancelled')
            <div class="flex flex-wrap gap-4 mt-8 border-t pt-6">
                <a href="{{ route('activities.edit', $activity) }}" class="bg-blue-600 text-white rounded-xl px-5 py-3">แก้ไขกิจกรรม</a>
                <a href="{{ route('activities.requests', $activity) }}" class="bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-xl px-5 py-3 hover:bg-indigo-100 transition-colors">
                    จัดการคำขอ
                    @if($pendingCount > 0)
                        <span class="ml-1 inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold">{{ $pendingCount }}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('activities.cancel', $activity) }}" onsubmit="return confirm('ยืนยันยกเลิกกิจกรรม? เมื่อยกเลิกแล้วจะไม่สามารถแก้ไขได้')">@csrf @method('PATCH')<button class="bg-rose-50 text-rose-700 rounded-xl px-5 py-3">ยกเลิกกิจกรรม</button></form>
            </div>
            @endif
        @else
            @if(!($activity->status === 'published' && $activity->starts_at->isFuture() && Auth::id() !== $activity->user_id))
                <p class="mt-8 pt-5 border-t text-sm text-slate-500">เฉพาะเจ้าของโพสต์เท่านั้นที่แก้ไขหรือยกเลิกกิจกรรมนี้ได้</p>
            @endif
        @endcan
    </article>
</div>
@endsection
