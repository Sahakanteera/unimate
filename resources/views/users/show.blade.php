@extends('layouts.app')
@section('title', $user->name.' | UniMate')

@php
    $possessive = $isSelf ? 'คุณ' : 'ผู้ใช้นี้';
@endphp

@section('content')
<div class="mx-auto max-w-6xl">

    {{-- ส่วนหัวโปรไฟล์ (หน้าตาเดียวกับหน้าแก้ไขโปรไฟล์) ไม่แสดงอีเมลและรหัสนักศึกษา --}}
    <section class="card relative overflow-hidden">
        <div aria-hidden="true" class="h-28 sm:h-32" style="background: radial-gradient(70% 120% at 0% 0%, #b7d4ef, transparent 62%), radial-gradient(60% 120% at 100% 0%, #d5e8e4, transparent 62%), radial-gradient(60% 100% at 50% 100%, #f3e6b8, transparent 70%), #f5f5f3;"></div>
        <div class="px-6 pb-6 sm:px-8 sm:pb-8">
            <div class="-mt-12 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <span class="block w-fit rounded-full ring-4 ring-white"><x-ui.avatar :user="$user" size="xl" /></span>
                @if($isSelf)
                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary w-full sm:w-auto"><x-ui.icon name="pencil" class="h-4 w-4" /> แก้ไขโปรไฟล์</a>
                @endif
            </div>

            <h1 class="break-anywhere mt-4 text-2xl font-medium tracking-tight text-ink-soft sm:text-3xl">{{ $user->name }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                <span class="chip {{ $user->isAdmin() ? 'chip-warn' : 'chip-info' }}">{{ $user->isAdmin() ? 'ผู้ดูแลระบบ' : 'นักศึกษา' }}</span>
                @if($user->isSuspended())
                    <span class="chip chip-bad">ถูกระงับ (เห็นเฉพาะผู้ดูแลระบบ)</span>
                @endif
                @if($user->created_at)
                    <span class="flex items-center gap-1.5 pl-1 text-sm text-ink-muted"><x-ui.icon name="calendar" class="h-4 w-4 text-ink-faint" /> สมัครเมื่อ <x-ui.time :value="$user->created_at" mode="date" /></span>
                @endif
            </div>
            @if($user->bio)
                <p class="break-anywhere mt-4 whitespace-pre-wrap rounded-tile bg-canvas px-4 py-3 text-sm leading-relaxed text-ink-soft">“{{ $user->bio }}”</p>
            @endif

            <dl class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
                <div class="stat min-w-0">
                    <dt class="stat-label mb-1 mt-0">กิจกรรมที่จัด</dt>
                    <dd class="stat-value">{{ $stats['hosted'] }}</dd>
                    <dd class="stat-label">ไม่นับที่ยกเลิกหรือถูกซ่อน</dd>
                </div>
                <div class="stat min-w-0">
                    <dt class="stat-label mb-1 mt-0">เข้าร่วมแล้ว</dt>
                    <dd class="stat-value">{{ $stats['attended'] }}</dd>
                    <dd class="stat-label">มาจริงตามการเช็กชื่อ และกิจกรรมจบแล้ว</dd>
                </div>
                {{-- คะแนนจากรีวิวคน (user_reviews) แยกตามบทบาท ไม่ใช้รีวิวกิจกรรม --}}
                @foreach(['host' => 'คะแนนในฐานะผู้จัด', 'participant' => 'คะแนนในฐานะผู้เข้าร่วม'] as $role => $label)
                    @php $rating = $stats[$role.'_rating']; @endphp
                    <div class="stat min-w-0">
                        <dt class="stat-label mb-1 mt-0">{{ $label }}</dt>
                        @if($rating)
                            <dd class="stat-value flex items-center gap-1.5"><x-ui.icon name="star" solid class="h-5 w-5 text-sun" /> {{ number_format($rating['rating'], 1) }}</dd>
                            <dd class="stat-label">จาก {{ $rating['total'] }} รีวิว</dd>
                        @else
                            <dd class="stat-value text-ink-muted">–</dd>
                            <dd class="stat-label">ยังไม่มีรีวิว</dd>
                        @endif
                    </div>
                @endforeach
                <div class="stat col-span-2 min-w-0 lg:col-span-1">
                    <dt class="stat-label mb-1 mt-0">มาตามนัด</dt>
                    @if($stats['attendance'])
                        <dd class="stat-value tabular-nums">{{ $stats['attendance']['present'] }}<span class="text-base text-ink-faint">/{{ $stats['attendance']['total'] }} ครั้ง</span></dd>
                        <dd class="stat-label">จากกิจกรรมที่จบและเช็กชื่อแล้ว</dd>
                    @else
                        <dd class="stat-value text-ink-muted">–</dd>
                        <dd class="stat-label">ยังไม่มีประวัติการเช็กชื่อ</dd>
                    @endif
                </div>
            </dl>
        </div>
    </section>

    {{-- ประวัติกิจกรรมที่จัด (เจ้าของและ Admin เห็นกิจกรรมที่ถูกซ่อนด้วย) --}}
    <section id="hosted" class="mt-10 scroll-mt-28" aria-labelledby="hosted-heading">
        <h2 id="hosted-heading" class="mb-4 flex items-center gap-2 text-xl font-medium text-ink">กิจกรรมที่จัด <span class="tab-count">{{ $hosted->total() }}</span></h2>
        @if($hosted->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($hosted as $activity)
                    <div class="relative min-w-0">
                        @if($activity->isHidden())
                            <span class="chip chip-dark absolute left-3 top-3 z-20 h-6 px-2.5 text-xs"><x-ui.icon name="eye-slash" class="h-3.5 w-3.5" /> ถูกซ่อน</span>
                        @endif
                        <x-activity-card :activity="$activity" class="h-full" />
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $hosted->links('partials.pagination') }}</div>
        @else
            <p class="rounded-card border border-dashed border-line-strong bg-white/60 px-6 py-10 text-center text-ink-muted">{{ $possessive }}ยังไม่ได้จัดกิจกรรม</p>
        @endif
    </section>

    {{-- ประวัติกิจกรรมที่เข้าร่วมแล้ว --}}
    <section id="attended" class="mt-10 scroll-mt-28" aria-labelledby="attended-heading">
        <h2 id="attended-heading" class="mb-4 flex items-center gap-2 text-xl font-medium text-ink">กิจกรรมที่เข้าร่วมแล้ว <span class="tab-count">{{ $attended->total() }}</span></h2>
        @if($attended->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($attended as $activity)
                    <x-activity-card :activity="$activity" class="min-w-0" />
                @endforeach
            </div>
            <div class="mt-6">{{ $attended->links('partials.pagination') }}</div>
        @else
            <p class="rounded-card border border-dashed border-line-strong bg-white/60 px-6 py-10 text-center text-ink-muted">{{ $possessive }}ยังไม่มีกิจกรรมที่เข้าร่วมและจบแล้ว</p>
        @endif
    </section>

    {{-- รีวิวคนที่ได้รับ แยก 2 แท็บด้วย query string (?tab=host|participant) ไม่ใช้ JS --}}
    @php
        $reviewTabs = ['host' => 'ในฐานะผู้จัด', 'participant' => 'ในฐานะผู้เข้าร่วม'];
        $viewer = Auth::user();
    @endphp
    <section id="received-reviews" class="card mt-10 scroll-mt-28 p-6 sm:p-8" aria-labelledby="reviews-heading">
        <h2 id="reviews-heading" class="section-title">รีวิวที่ได้รับ</h2>
        <nav class="tabs mt-3" aria-label="ประเภทรีวิวที่ได้รับ">
            @foreach($reviewTabs as $key => $label)
                <a href="{{ route('users.show', ['user' => $user, 'tab' => $key]) }}#received-reviews" @if($tab === $key) aria-current="page" @endif
                   class="tab {{ $tab === $key ? 'tab-active' : '' }}">{{ $label }} <span class="tab-count">{{ $stats[$key.'_rating']['total'] ?? 0 }}</span></a>
            @endforeach
        </nav>

        @if($receivedReviews->isNotEmpty())
            <ul class="mt-5 divide-y divide-line">
                @foreach($receivedReviews as $review)
                    <li class="py-4 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2.5">
                                <x-ui.avatar :user="$review->reviewer" size="sm" />
                                <p class="truncate text-sm font-medium text-ink" title="{{ $review->reviewer->name }}"><x-ui.user-link :user="$review->reviewer">{{ $review->reviewer->name }}</x-ui.user-link></p>
                            </div>
                            <p class="flex shrink-0" role="img" aria-label="{{ $review->rating }} จาก 5 ดาว">@for($i = 1; $i <= 5; $i++)<x-ui.icon name="star" solid class="h-4 w-4 {{ $i <= $review->rating ? 'text-sun' : 'text-line-strong' }}" />@endfor</p>
                        </div>
                        @if($review->comment)<p class="break-anywhere mt-3 whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{{ $review->comment }}</p>@endif
                        <p class="break-anywhere mt-2 text-xs text-ink-faint">
                            กิจกรรม <a href="{{ route('activities.show', $review->activity) }}" class="link">{{ $review->activity->title }}</a>
                            · <x-ui.time :value="$review->created_at" mode="date" />
                            @if($review->updated_at && $review->updated_at->gt($review->created_at)) · แก้ไขแล้ว @endif
                        </p>

                        @if($review->reviewer_id === $viewer->id)
                            {{-- รีวิวที่ฉันเขียน แก้ไข/ลบได้ที่หน้ารีวิวเพื่อนร่วมกิจกรรม --}}
                            <a href="{{ route('activities.people', $review->activity) }}#person-{{ $user->id }}" class="link mt-2 inline-flex items-center gap-1 text-xs">แก้ไขรีวิวของคุณ <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></a>
                        @elseif($viewer->isAdmin())
                            @include('users.partials.admin-delete-user-review', ['review' => $review, 'open' => (int) old('user_review_id') === $review->id])
                        @else
                            {{-- รายงานรีวิวที่ไม่เหมาะสม error เปิดเฉพาะฟอร์มของรีวิวที่ส่ง (hidden report_user_review_id) --}}
                            @php $reportFormOpen = (int) old('report_user_review_id') === $review->id && $errors->has('user_review_reason'); @endphp
                            <details class="group mt-2" @if($reportFormOpen) open @endif>
                                <summary class="inline-flex cursor-pointer items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium text-ink-muted transition hover:bg-bad-soft hover:text-bad">
                                    <x-ui.icon name="flag" class="h-3.5 w-3.5" /> รายงาน
                                </summary>
                                <form method="POST" action="{{ route('user-reviews.report', $review) }}" class="mt-2 space-y-2">
                                    @csrf
                                    <input type="hidden" name="report_user_review_id" value="{{ $review->id }}">
                                    <label for="user-review-report-{{ $review->id }}" class="sr-only">เหตุผลที่รายงานรีวิวนี้</label>
                                    <textarea id="user-review-report-{{ $review->id }}" name="user_review_reason" rows="2" minlength="5" maxlength="1000" required placeholder="รีวิวนี้ไม่เหมาะสมอย่างไร เช่น ใช้คำหยาบ หรือให้ข้อมูลเท็จ" class="field text-sm @if($reportFormOpen) field-invalid @endif" @if($reportFormOpen) aria-invalid="true" aria-describedby="user-review-report-error-{{ $review->id }}" @endif>{{ $reportFormOpen ? old('user_review_reason') : '' }}</textarea>
                                    @if($reportFormOpen)
                                        @error('user_review_reason')<p id="user-review-report-error-{{ $review->id }}" class="field-error">{{ $message }}</p>@enderror
                                    @endif
                                    <button class="btn btn-danger btn-sm">ส่งรายงานรีวิว</button>
                                </form>
                            </details>
                        @endif
                    </li>
                @endforeach
            </ul>
            <div class="mt-6">{{ $receivedReviews->links('partials.pagination') }}</div>
        @else
            <p class="mt-4 rounded-tile bg-canvas p-4 text-center text-sm text-ink-muted">{{ $possessive }}ยังไม่มีรีวิว{{ $reviewTabs[$tab] }}</p>
        @endif
    </section>
</div>
@endsection
