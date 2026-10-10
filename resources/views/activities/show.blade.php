@extends('layouts.app')
@section('title', $activity->title.' | UniMate')

@php
    $isOwner = Auth::id() === $activity->user_id;
    $isCancelled = $activity->status === 'cancelled';
    $isEnded = $activity->isEnded();
    // ใช้รายชื่อสมาชิกที่โหลดมาแล้ว แทนการนับซ้ำ (ค่าเท่ากับ approvedCount())
    $approved = $approvedMembers->count();
    $remaining = max(0, $activity->capacity - $approved);
    $state = App\Support\Ui::activityState($activity, $approved);
    $pct = $activity->capacity > 0 ? min(100, (int) round($approved / $activity->capacity * 100)) : 0;
    $start = $activity->starts_at->copy()->locale('th');
    $end = $activity->ends_at->copy()->locale('th');
    $sameDay = $activity->starts_at->isSameDay($activity->ends_at);
    $avgRating = $reviews->isNotEmpty() ? number_format($reviews->avg('rating'), 1) : null;
    $tint = in_array($state, ['cancelled', 'ended'], true) ? 'bg-canvas' : App\Support\Ui::tint($activity->category_id);
    $capacityNote = match ($state) {
        'cancelled' => 'ยกเลิกแล้ว',
        'ended' => 'กิจกรรมจบแล้ว',
        'ongoing' => 'เริ่มแล้ว · ปิดรับคำขอ',
        'full' => 'เต็มแล้ว',
        default => 'ว่าง '.$remaining.' ที่',
    };
    $sections = array_filter([
        'details' => 'รายละเอียด',
        'members' => $approvedMembers->isNotEmpty() ? 'สมาชิก ('.$approvedMembers->count().')' : null,
        'reviews' => 'รีวิว ('.$reviews->count().')',
    ]);
@endphp

@section('content')
<a href="{{ route('activities.index') }}" class="btn btn-ghost btn-sm -ml-3"><x-ui.icon name="arrow-left" class="h-4 w-4" /> กลับหน้ากิจกรรม</a>

@if($isCancelled)
    <div role="status" class="mt-4 flex items-center gap-3 rounded-tile border border-bad/20 bg-bad-soft p-4 text-bad">
        <x-ui.icon name="ban" /> <p class="font-medium">กิจกรรมนี้ถูกยกเลิกแล้ว</p>
    </div>
@endif
@if($activity->isHidden())
    <div role="status" class="mt-4 flex gap-3 rounded-tile bg-night p-4 text-white">
        <x-ui.icon name="eye-slash" class="mt-0.5" />
        <div class="min-w-0">
            <p class="font-medium">กิจกรรมนี้ถูกซ่อนโดยผู้ดูแลระบบ ผู้ใช้อื่นจะมองไม่เห็น</p>
            <p class="break-anywhere mt-1 text-sm text-white/70">เหตุผล: {{ $activity->hidden_reason }}</p>
        </div>
    </div>
@endif

<div class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-8">
    {{-- ส่วนหัว: หมวดหมู่ ชื่อ ผู้ประกาศ และตัวเลขสำคัญ (แบบการ์ดโปรไฟล์ของ fastwork) --}}
    <header class="card min-w-0 lg:col-start-1 lg:row-start-1">
        <x-ui.category-art :category="$activity->category->name" :tint="$tint" size="lg" class="rounded-t-card" />
        <div class="p-6 sm:p-8">
        <div class="flex items-start gap-4">
            <x-ui.date-tile :date="$activity->starts_at" :tint="$tint" class="hidden sm:flex" />
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="chip chip-outline"><x-ui.icon name="tag" class="h-3.5 w-3.5 text-ink-faint" /> {{ $activity->category->name }}</span>
                    <x-ui.activity-status :state="$state" :remaining="$remaining" />
                </div>
                <h1 class="break-anywhere mt-3 text-[1.75rem] font-medium leading-tight tracking-tight text-ink-soft sm:text-4xl">{{ $activity->title }}</h1>
                <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-ink-muted">
                    <span class="flex min-w-0 items-center gap-2"><x-ui.avatar :user="$activity->user" size="sm" /> ประกาศโดย <span class="truncate font-medium text-ink">{{ $activity->user->name }}</span></span>
                    <span aria-hidden="true" class="hidden text-line-strong sm:inline">•</span>
                    <span>อัปเดต <x-ui.time :value="$activity->updated_at" mode="relative" /></span>
                </div>
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="stat">
                <dt class="stat-label mb-1 mt-0">วันที่</dt>
                <dd class="text-lg font-medium leading-tight text-ink">{{ $start->isoFormat('dd D MMM') }}</dd>
                <dd class="stat-label">{{ $start->isoFormat('YYYY') }}{{ $sameDay ? '' : ' ถึง '.$end->isoFormat('D MMM') }}</dd>
            </div>
            <div class="stat">
                <dt class="stat-label mb-1 mt-0">เวลา (เวลาไทย)</dt>
                <dd class="text-lg font-medium leading-tight tabular-nums text-ink">{{ $activity->starts_at->format('H:i') }}–{{ $activity->ends_at->format('H:i') }}</dd>
                <dd class="stat-label">{{ $isEnded ? 'จบไปแล้ว' : $activity->starts_at->copy()->locale('th')->diffForHumans() }}</dd>
            </div>
            <div class="stat">
                <dt class="stat-label mb-1 mt-0">ผู้เข้าร่วม</dt>
                <dd class="text-lg font-medium leading-tight tabular-nums text-ink">{{ $approved }} / {{ $activity->capacity }} คน</dd>
                <dd class="stat-label">ไม่รวมผู้ประกาศ</dd>
            </div>
            <div class="stat">
                <dt class="stat-label mb-1 mt-0">คะแนนรีวิว</dt>
                @if($avgRating)
                    <dd class="flex items-center gap-1 text-lg font-medium leading-tight tabular-nums text-ink"><x-ui.icon name="star" solid class="h-4 w-4 text-sun" /> {{ $avgRating }}</dd>
                    <dd class="stat-label">จาก {{ $reviews->count() }} รีวิว</dd>
                @else
                    <dd class="text-lg font-medium leading-tight text-ink-muted">–</dd>
                    <dd class="stat-label">ยังไม่มีรีวิว</dd>
                @endif
            </div>
        </dl>

        <p class="mt-4 flex items-start gap-2 text-sm text-ink-soft">
            <x-ui.icon name="map-pin" class="mt-0.5 h-4 w-4 text-ink-faint" />
            <span class="break-anywhere min-w-0"><span class="text-ink-muted">สถานที่:</span> {{ $activity->location }}</span>
        </p>
        </div>
    </header>

    {{-- การ์ดดำเนินการ: ติดด้านขวาบนจอใหญ่ และอยู่ถัดจากส่วนหัวบนมือถือ --}}
    <aside class="min-w-0 space-y-4 lg:sticky lg:top-24 lg:col-start-2 lg:row-span-2 lg:row-start-1 lg:self-start">
        <div class="card p-5 sm:p-6">
            <div class="flex items-end justify-between gap-3">
                <p class="text-sm text-ink-muted">จำนวนผู้เข้าร่วม</p>
                <p class="text-sm {{ $state === 'full' ? 'font-medium text-bad' : 'text-ink-muted' }}">{{ $capacityNote }}</p>
            </div>
            <p class="mt-1 text-3xl font-medium tracking-tight tabular-nums text-ink">{{ $approved }}<span class="text-lg text-ink-faint"> / {{ $activity->capacity }} คน</span></p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-canvas" role="progressbar" aria-valuenow="{{ $approved }}" aria-valuemin="0" aria-valuemax="{{ $activity->capacity }}" aria-label="จำนวนผู้เข้าร่วม">
                <div class="h-full rounded-full transition-all {{ $pct >= 100 ? 'bg-bad' : 'bg-night' }}" style="width: {{ $pct }}%"></div>
            </div>

            {{-- ส่วนที่ 3: ปุ่มขอเข้าร่วม / ถอนคำขอ / ถอนตัว --}}
            @if($activity->status === 'published' && $activity->starts_at->isFuture())
                @if(Auth::id() !== $activity->user_id)
                    @if($myParticipation && in_array($myParticipation->status, ['pending', 'approved']))
                        {{-- ผู้ใช้มีคำขอ active อยู่แล้ว --}}
                        <div class="mt-5 border-t border-line pt-5">
                            <div class="flex items-center gap-3">
                                @if($myParticipation->isPending())
                                    <span class="grid h-10 w-10 place-items-center rounded-full bg-warn-soft text-warn"><x-ui.icon name="clock" /></span>
                                    <div><p class="font-medium text-ink">รอการอนุมัติ</p><p class="text-xs text-ink-muted">ผู้จัดจะแจ้งผลผ่านการแจ้งเตือน</p></div>
                                @else
                                    <span class="grid h-10 w-10 place-items-center rounded-full bg-ok-soft text-ok"><x-ui.icon name="check" /></span>
                                    <div><p class="font-medium text-ink">เข้าร่วมแล้ว</p><p class="text-xs text-ink-muted">เจอกัน {{ $start->isoFormat('dd D MMM') }} เวลา {{ $activity->starts_at->format('H:i') }}</p></div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('activities.cancel-request', $activity) }}" class="mt-4" onsubmit="return confirm('{{ $myParticipation->isApproved() ? 'ยืนยันถอนตัวจากกิจกรรม?' : 'ยืนยันถอนคำขอเข้าร่วม?' }}')">
                                @csrf @method('PATCH')
                                <button class="btn btn-danger w-full">
                                    {{ $myParticipation->isApproved() ? 'ถอนตัวจากกิจกรรม' : 'ถอนคำขอเข้าร่วม' }}
                                </button>
                            </form>
                        </div>
                    @elseif($state !== 'full')
                        {{-- ฟอร์มส่งคำขอเข้าร่วม --}}
                        <form method="POST" action="{{ route('activities.join', $activity) }}" class="mt-5 border-t border-line pt-5">
                            @csrf
                            <h2 class="font-medium text-ink">ขอเข้าร่วมกิจกรรม</h2>
                            <label for="join-message" class="mt-3 block text-sm text-ink-muted">ข้อความถึงผู้จัด (ไม่บังคับ)</label>
                            <textarea id="join-message" name="message" rows="3" maxlength="500" placeholder="เช่น อยากร่วมด้วยครับ เล่นบาสได้" class="field mt-1.5">{{ old('message') }}</textarea>
                            <button class="btn btn-primary btn-lg mt-4 w-full">ส่งคำขอเข้าร่วม</button>
                        </form>
                    @else
                        <p class="mt-5 rounded-tile bg-bad-soft p-3 text-sm font-medium text-bad">กิจกรรมนี้เต็มแล้ว ไม่สามารถส่งคำขอได้</p>
                    @endif
                @endif
            @endif

            {{-- ซ่อนปุ่มตาม Policy; ฝั่ง Controller/FormRequest ยังตรวจสิทธิ์ทุกคำขอด้วย --}}
            @can('update', $activity)
                @if($activity->status !== 'cancelled')
                    <div class="mt-5 space-y-2 border-t border-line pt-5">
                        @if($activity->starts_at->isPast())
                            {{-- กิจกรรมเริ่มแล้ว: งานหลักของผู้จัดคือเช็กชื่อ (ผู้ที่ถูกเช็กว่ามาเท่านั้นที่รีวิวได้) --}}
                            <a href="{{ route('activities.requests', ['activity' => $activity, 'status' => 'approved']) }}" class="btn btn-primary w-full"><x-ui.icon name="check-circle" class="h-4 w-4" /> เช็กชื่อผู้เข้าร่วม</a>
                            <p class="pb-1 text-center text-xs text-ink-muted">ผู้ที่ถูกเช็กว่า “มา” เท่านั้นจึงรีวิวกิจกรรมได้</p>
                        @endif
                        <a href="{{ route('activities.requests', $activity) }}" class="btn {{ $activity->starts_at->isPast() ? 'btn-secondary' : 'btn-primary' }} w-full">
                            <x-ui.icon name="clipboard" class="h-4 w-4" />
                            จัดการคำขอ
                            @if($pendingCount > 0)
                                <span class="badge-count bg-sun text-night">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('activities.edit', $activity) }}" class="btn btn-secondary w-full"><x-ui.icon name="pencil" class="h-4 w-4" /> แก้ไขกิจกรรม</a>
                        <form method="POST" action="{{ route('activities.cancel', $activity) }}" onsubmit="return confirm('ยืนยันยกเลิกกิจกรรม? เมื่อยกเลิกแล้วจะไม่สามารถแก้ไขได้')">@csrf @method('PATCH')<button class="btn btn-ghost w-full text-bad hover:bg-bad-soft hover:text-bad">ยกเลิกกิจกรรม</button></form>
                    </div>
                @endif
            @else
                @if(!($activity->status === 'published' && $activity->starts_at->isFuture() && Auth::id() !== $activity->user_id))
                    {{-- ไม่รับคำขอแล้ว: บอกเหตุผลตามสถานะ แทนข้อความเรื่องสิทธิ์แก้ไข --}}
                    <div class="mt-5 space-y-3 border-t border-line pt-5 text-sm">
                        <p class="flex items-start gap-2 text-ink-soft">
                            @if($isCancelled)
                                <x-ui.icon name="ban" class="mt-0.5 h-4 w-4 text-bad" /> กิจกรรมนี้ถูกยกเลิกแล้ว จึงไม่รับคำขอเข้าร่วม
                            @elseif($isEnded)
                                <x-ui.icon name="check-circle" class="mt-0.5 h-4 w-4 text-ink-faint" /> กิจกรรมนี้จบแล้ว ขอบคุณทุกคนที่มาร่วม
                            @else
                                <x-ui.icon name="clock" class="mt-0.5 h-4 w-4 text-brand-600" /> กิจกรรมเริ่มไปแล้ว จึงปิดรับคำขอเข้าร่วม
                            @endif
                        </p>
                        @if($myParticipation?->isApproved())
                            <p class="flex items-center gap-2 rounded-tile bg-ok-soft p-3 font-medium text-ok"><x-ui.icon name="check" class="h-4 w-4" /> คุณอยู่ในรายชื่อผู้เข้าร่วม</p>
                        @endif
                        @if($reviewBlockReason === null)
                            <a href="#reviews" class="btn btn-primary w-full"><x-ui.icon name="star" solid class="h-4 w-4 text-sun" /> ให้คะแนนกิจกรรมนี้</a>
                        @endif
                    </div>
                @endif
            @endcan
        </div>

        {{-- ผู้ประกาศ --}}
        <div class="card flex items-start gap-4 p-5">
            <x-ui.avatar :user="$activity->user" size="lg" />
            <div class="min-w-0">
                <p class="text-xs text-ink-muted">ผู้ประกาศกิจกรรม</p>
                <p class="truncate font-medium text-ink" title="{{ $activity->user->name }}">{{ $activity->user->name }}</p>
                @if($activity->user->bio)
                    <p class="break-anywhere mt-1 text-sm text-ink-muted line-clamp-3">{{ $activity->user->bio }}</p>
                @endif
            </div>
        </div>
    </aside>

    <div class="min-w-0 lg:col-start-1 lg:row-start-2">
        {{-- แท็บขีดเส้นใต้ เลื่อนไปยังแต่ละส่วน และไฮไลต์ตามตำแหน่งที่เลื่อนอยู่ --}}
        <nav class="tabs sticky top-[84px] z-10 -mx-1 bg-canvas/90 px-1 backdrop-blur" aria-label="ส่วนของหน้ากิจกรรม" data-section-tabs>
            @foreach($sections as $id => $label)
                <a href="#{{ $id }}" class="tab {{ $loop->first ? 'tab-active' : '' }}" data-tab="{{ $id }}">{{ $label }}</a>
            @endforeach
        </nav>

        <section id="details" class="card mt-5 scroll-mt-40 p-6 sm:p-8">
            <h2 class="section-title">รายละเอียดกิจกรรม</h2>
            <p class="break-anywhere mt-3 whitespace-pre-wrap leading-relaxed text-ink-soft">{{ $activity->description }}</p>
            @if($activity->location_image_path)
                <figure class="mt-5">
                    <img src="{{ asset('storage/'.$activity->location_image_path) }}" alt="รูปสถานที่: {{ $activity->location }}" class="max-h-96 w-full rounded-tile object-contain" loading="lazy">
                    <figcaption class="mt-2 text-sm text-ink-muted">{{ $activity->location }}</figcaption>
                </figure>
            @endif
            @if($activity->googleMapsUrl())
                <div class="mt-5">
                    <h3 class="font-medium">จุดนัดพบบนแผนที่</h3>
                    <p class="mt-2 font-medium text-ink">{{ $activity->location }}</p>
                    <a href="{{ $activity->googleMapsUrl() }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm mt-3">เปิดสถานที่ใน Google Maps</a>
                </div>
            @endif
            <dl class="mt-6 grid gap-3 border-t border-line pt-5 text-sm sm:grid-cols-2">
                <div><dt class="text-ink-muted">เวลาเริ่ม (เวลาไทย)</dt><dd class="mt-0.5 font-medium tabular-nums text-ink">{{ $start->isoFormat('dd D MMM YYYY') }} · {{ $activity->starts_at->format('H:i') }}</dd></div>
                <div><dt class="text-ink-muted">เวลาสิ้นสุด (เวลาไทย)</dt><dd class="mt-0.5 font-medium tabular-nums text-ink">{{ $end->isoFormat('dd D MMM YYYY') }} · {{ $activity->ends_at->format('H:i') }}</dd></div>
            </dl>
        </section>

        {{-- รายชื่อสมาชิก (approved) --}}
        @if($approvedMembers->isNotEmpty())
            <section id="members" class="card mt-5 scroll-mt-40 p-6 sm:p-8">
                <h2 class="section-title">สมาชิกที่เข้าร่วม ({{ $approvedMembers->count() }})</h2>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($approvedMembers as $member)
                        <li class="flex items-center gap-3 rounded-tile bg-canvas p-3">
                            <x-ui.avatar :user="$member->user" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-ink" title="{{ $member->user->name }}">{{ $member->user->name }}</p>
                                <p class="text-xs text-ink-muted">เข้าร่วมเมื่อ <x-ui.time :value="$member->updated_at" mode="relative" /></p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- ส่วนที่ 5: รีวิวกิจกรรม --}}
        <section id="reviews" class="card mt-5 scroll-mt-40 p-6 sm:p-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="section-title">รีวิวจากผู้เข้าร่วม ({{ $reviews->count() }})</h2>
                @if($avgRating)
                    @php $rounded = (int) round($reviews->avg('rating')); @endphp
                    <p class="flex items-center gap-2" aria-label="คะแนนเฉลี่ย {{ $avgRating }} จาก 5">
                        <span class="text-3xl font-medium tracking-tight tabular-nums text-ink">{{ $avgRating }}</span>
                        <span class="flex" aria-hidden="true">@for($i = 1; $i <= 5; $i++)<x-ui.icon name="star" solid class="h-4 w-4 {{ $i <= $rounded ? 'text-sun' : 'text-line-strong' }}" />@endfor</span>
                    </p>
                @endif
            </div>

            @if($reviewBlockReason === null)
                <form method="POST" action="{{ route('activities.reviews.store', $activity) }}" class="mt-5 rounded-2xl bg-tint-butter/30 p-5">
                    @csrf
                    <fieldset>
                        <legend class="font-medium text-ink">ให้คะแนนกิจกรรมนี้</legend>
                        <div class="star-rating mt-2">
                            @for($i = 5; $i >= 1; $i--)
                                <input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" class="peer sr-only" @checked(old('rating') == $i) required>
                                <label for="rating-{{ $i }}" title="{{ $i }} ดาว"><x-ui.icon name="star" solid class="h-9 w-9" /><span class="sr-only">{{ $i }} ดาว</span></label>
                            @endfor
                        </div>
                    </fieldset>
                    @error('rating')<p class="field-error">{{ $message }}</p>@enderror
                    <label for="review-comment" class="sr-only">ความคิดเห็น</label>
                    <textarea id="review-comment" name="comment" rows="3" maxlength="1000" placeholder="ความคิดเห็นเพิ่มเติม (ไม่บังคับ)" class="field mt-3">{{ old('comment') }}</textarea>
                    <button class="btn btn-primary mt-4">ส่งรีวิว</button>
                </form>
            @elseif($activity->isEnded() && Auth::id() !== $activity->user_id)
                <p class="mt-4 flex items-center gap-2 rounded-tile bg-canvas p-3 text-sm text-ink-muted"><x-ui.icon name="alert" class="h-4 w-4" />{{ $reviewBlockReason }}</p>
            @endif

            @if($reviews->isNotEmpty())
                <ul class="mt-5 divide-y divide-line">
                    @foreach($reviews as $review)
                        <li class="py-4 first:pt-0 last:pb-0">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <x-ui.avatar :user="$review->user" size="sm" />
                                    <p class="truncate text-sm font-medium text-ink" title="{{ $review->user->name }}">{{ $review->user->name }}</p>
                                </div>
                                <p class="flex shrink-0" role="img" aria-label="{{ $review->rating }} จาก 5 ดาว">@for($i = 1; $i <= 5; $i++)<x-ui.icon name="star" solid class="h-4 w-4 {{ $i <= $review->rating ? 'text-sun' : 'text-line-strong' }}" />@endfor</p>
                            </div>
                            @if($review->comment)<p class="break-anywhere mt-3 whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{{ $review->comment }}</p>@endif
                            <p class="mt-2 text-xs text-ink-faint"><x-ui.time :value="$review->created_at" mode="date" /></p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 rounded-tile bg-canvas p-4 text-center text-sm text-ink-muted">{{ $activity->isEnded() ? 'ยังไม่มีรีวิว' : 'รีวิวได้หลังกิจกรรมจบ' }}</p>
            @endif
        </section>

        {{-- ส่วนที่ 5: รายงานกิจกรรมหรือผู้ใช้ (ไม่แสดงให้เจ้าของกิจกรรม) --}}
        @if(Auth::id() !== $activity->user_id)
            <details class="group mt-5" @if($errors->has('reason') || $errors->has('target')) open @endif>
                <summary class="inline-flex cursor-pointer items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-bad transition hover:bg-bad-soft">
                    <x-ui.icon name="flag" class="h-4 w-4" /> รายงานปัญหา
                    <x-ui.icon name="chevron-down" class="h-4 w-4 transition group-open:rotate-180" />
                </summary>
                <form method="POST" action="{{ route('activities.reports.store', $activity) }}" class="card mt-3 space-y-4 p-5 sm:p-6">
                    @csrf
                    <div>
                        <label for="report-target" class="field-label">ต้องการรายงาน</label>
                        <select id="report-target" name="target" class="field">
                            <option value="activity">กิจกรรมนี้</option>
                            <option value="user:{{ $activity->user_id }}">ผู้ประกาศ: {{ $activity->user->name }}</option>
                            @foreach($approvedMembers as $member)
                                @if($member->user_id !== Auth::id())
                                    <option value="user:{{ $member->user_id }}">สมาชิก: {{ $member->user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="report-reason" class="field-label">เหตุผล</label>
                        <textarea id="report-reason" name="reason" rows="3" maxlength="1000" required placeholder="อธิบายปัญหาที่พบ เช่น เนื้อหาไม่เหมาะสม หรือไม่มาตามนัด" class="field @error('reason') field-invalid @enderror" @error('reason') aria-invalid="true" aria-describedby="report-reason-error" @enderror>{{ old('reason') }}</textarea>
                        @error('reason')<p id="report-reason-error" class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <button class="btn btn-danger">ส่งรายงาน</button>
                </form>
            </details>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ไฮไลต์แท็บตามส่วนที่กำลังอ่านอยู่: ส่วนสุดท้ายที่หัวข้อเลื่อนผ่านเส้น 40% ของจอ (ค่าเริ่มต้นคือแท็บแรก)
    (() => {
        const tabs = [...document.querySelectorAll('[data-section-tabs] [data-tab]')];
        const sections = tabs.map((t) => document.getElementById(t.dataset.tab)).filter(Boolean);
        if (!sections.length) return;
        let ticking = false;
        const update = () => {
            ticking = false;
            const line = window.innerHeight * 0.4;
            const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
            let current = sections[0].id;
            sections.forEach((s) => { if (s.getBoundingClientRect().top <= line) current = s.id; });
            if (atBottom) current = sections[sections.length - 1].id;
            tabs.forEach((t) => t.classList.toggle('tab-active', t.dataset.tab === current));
        };
        window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
        update();
    })();
</script>
@endsection
