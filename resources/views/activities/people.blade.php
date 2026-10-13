@extends('layouts.app')
@section('title', 'รีวิวเพื่อนร่วมกิจกรรม — '.$activity->title.' | UniMate')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('activities.show', $activity) }}" class="btn btn-ghost btn-sm -ml-3"><x-ui.icon name="arrow-left" class="h-4 w-4" /> กลับหน้ากิจกรรม</a>

    <div class="mt-4">
        <h1 class="page-title">รีวิวเพื่อนร่วมกิจกรรม</h1>
        <p class="break-anywhere mt-2 text-ink-muted">{{ $activity->title }} · {{ $activity->starts_at->copy()->locale('th')->isoFormat('dd D MMM YYYY') }}</p>
    </div>

    @if(! $isAttendee && $myReviews->isNotEmpty())
        <p class="mt-6 flex items-start gap-2 rounded-tile bg-canvas p-4 text-sm text-ink-muted"><x-ui.icon name="alert" class="mt-0.5 h-4 w-4" /> คุณไม่ได้อยู่ในรายชื่อผู้มาร่วมจริงแล้ว จึงส่งรีวิวใหม่ไม่ได้ แต่ยังแก้ไขหรือลบรีวิวเดิมของคุณได้</p>
    @elseif(! $isAttendee)
        {{-- Admin ที่ไม่ได้ไปร่วม: ดูอย่างเดียว และลบรีวิวที่ไม่เหมาะสมได้ --}}
        <p class="mt-6 flex items-start gap-2 rounded-tile bg-canvas p-4 text-sm text-ink-muted"><x-ui.icon name="shield" class="mt-0.5 h-4 w-4" /> ผู้ดูแลระบบดูรายชื่อและรีวิวในกิจกรรมนี้ได้อย่างเดียว ลบรีวิวที่ไม่เหมาะสมได้พร้อมเหตุผล</p>
    @elseif($closedReason !== null)
        <p class="mt-6 flex items-start gap-2 rounded-tile bg-canvas p-4 text-sm text-ink-muted"><x-ui.icon name="alert" class="mt-0.5 h-4 w-4" /> {{ $closedReason }}</p>
    @else
        <p class="mt-6 flex items-start gap-2 rounded-tile bg-brand-50 p-4 text-sm text-brand-700"><x-ui.icon name="star" solid class="mt-0.5 h-4 w-4 text-sun" /> ให้คะแนนคนที่มาร่วมกิจกรรมจริง คะแนนจะแสดงในโปรไฟล์ของแต่ละคน แก้ไขหรือลบรีวิวของคุณได้ภายหลัง</p>
    @endif

    @if($people->isNotEmpty())
        <ul class="mt-6 space-y-4">
            @foreach($people as $person)
                @php
                    $role = $person->id === $activity->user_id ? 'host' : 'participant';
                    $mine = $myReviews->get($person->id);
                    // error ของแต่ละฟอร์มเปิดเฉพาะฟอร์มของคนนั้น (ดูจาก hidden review_person)
                    $formOpen = (int) old('review_person') === $person->id;
                @endphp
                <li id="person-{{ $person->id }}" class="card min-w-0 scroll-mt-28 p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$person" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-ink" title="{{ $person->name }}"><x-ui.user-link :user="$person">{{ $person->name }}</x-ui.user-link></p>
                            <span class="chip {{ $role === 'host' ? 'chip-warn' : 'chip-info' }} mt-1 h-6 px-2.5 text-xs">{{ App\Models\UserReview::ROLE_LABELS[$role] }}</span>
                        </div>
                    </div>

                    @if($isAttendee || $mine)
                        @if($mine)
                            {{-- รีวิวแล้ว: แสดงรีวิวของฉัน แก้ไขหรือลบได้ (Controller ตรวจซ้ำว่าเป็นรีวิวของผู้ใช้คนนี้) --}}
                            <div class="mt-4 rounded-tile bg-canvas p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="chip chip-info h-6 px-2.5 text-xs">รีวิวของคุณ</span>
                                    <p class="flex shrink-0" role="img" aria-label="{{ $mine->rating }} จาก 5 ดาว">@for($i = 1; $i <= 5; $i++)<x-ui.icon name="star" solid class="h-4 w-4 {{ $i <= $mine->rating ? 'text-sun' : 'text-line-strong' }}" />@endfor</p>
                                </div>
                                @if($mine->comment)<p class="break-anywhere mt-3 whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{{ $mine->comment }}</p>@endif
                                <p class="mt-2 text-xs text-ink-faint">
                                    <x-ui.time :value="$mine->created_at" mode="date" />
                                    @if($mine->updated_at && $mine->updated_at->gt($mine->created_at)) · แก้ไขแล้ว @endif
                                </p>
                            </div>
                            <div class="mt-3 flex flex-wrap items-start gap-2">
                                @if(! $activity->isHidden())
                                    @php $editRating = $formOpen ? old('rating') : $mine->rating; @endphp
                                    <details class="group w-full" @if($formOpen) open @endif>
                                        <summary class="btn btn-secondary btn-sm w-fit cursor-pointer list-none [&::-webkit-details-marker]:hidden"><x-ui.icon name="pencil" class="h-4 w-4" /> แก้ไขรีวิว</summary>
                                        <form method="POST" action="{{ route('activities.user-reviews.update', [$activity, $mine]) }}" class="mt-3 rounded-2xl bg-tint-butter/30 p-5">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="review_person" value="{{ $person->id }}">
                                            <fieldset>
                                                <legend class="font-medium text-ink">แก้ไขรีวิวถึง <span class="break-anywhere">{{ $person->name }}</span></legend>
                                                <div class="star-rating mt-2">
                                                    @for($i = 5; $i >= 1; $i--)
                                                        <input type="radio" id="ur-edit-{{ $person->id }}-{{ $i }}" name="rating" value="{{ $i }}" class="peer sr-only" @checked($editRating == $i) required>
                                                        <label for="ur-edit-{{ $person->id }}-{{ $i }}" title="{{ $i }} ดาว"><x-ui.icon name="star" solid class="h-8 w-8" /><span class="sr-only">{{ $i }} ดาว</span></label>
                                                    @endfor
                                                </div>
                                            </fieldset>
                                            @if($formOpen) @error('rating')<p class="field-error">{{ $message }}</p>@enderror @endif
                                            <label for="ur-edit-comment-{{ $person->id }}" class="sr-only">ความคิดเห็น</label>
                                            <textarea id="ur-edit-comment-{{ $person->id }}" name="comment" rows="3" maxlength="1000" placeholder="ความคิดเห็นเพิ่มเติม (ไม่บังคับ)" class="field mt-3">{{ $formOpen ? old('comment') : $mine->comment }}</textarea>
                                            @if($formOpen) @error('comment')<p class="field-error">{{ $message }}</p>@enderror @endif
                                            <button class="btn btn-primary mt-4">บันทึกการแก้ไข</button>
                                        </form>
                                    </details>
                                @endif
                                <form method="POST" action="{{ route('activities.user-reviews.destroy', [$activity, $mine]) }}" onsubmit="return confirm({{ Js::from('ยืนยันลบรีวิวที่คุณเขียนถึง '.$person->name.'?') }})">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">ลบรีวิว</button>
                                </form>
                            </div>
                        @elseif($isAttendee && $closedReason === null)
                            <form method="POST" action="{{ route('activities.people.review', [$activity, $person]) }}" class="mt-4 rounded-2xl bg-tint-butter/30 p-5">
                                @csrf
                                <input type="hidden" name="review_person" value="{{ $person->id }}">
                                <fieldset>
                                    <legend class="font-medium text-ink">ให้คะแนน <span class="break-anywhere">{{ $person->name }}</span></legend>
                                    <div class="star-rating mt-2">
                                        @for($i = 5; $i >= 1; $i--)
                                            <input type="radio" id="ur-{{ $person->id }}-{{ $i }}" name="rating" value="{{ $i }}" class="peer sr-only" @checked($formOpen && old('rating') == $i) required>
                                            <label for="ur-{{ $person->id }}-{{ $i }}" title="{{ $i }} ดาว"><x-ui.icon name="star" solid class="h-8 w-8" /><span class="sr-only">{{ $i }} ดาว</span></label>
                                        @endfor
                                    </div>
                                </fieldset>
                                @if($formOpen) @error('rating')<p class="field-error">{{ $message }}</p>@enderror @endif
                                <label for="ur-comment-{{ $person->id }}" class="sr-only">ความคิดเห็นถึง {{ $person->name }}</label>
                                <textarea id="ur-comment-{{ $person->id }}" name="comment" rows="2" maxlength="1000" placeholder="{{ $role === 'host' ? 'ผู้จัดดูแลดีไหม นัดตรงเวลาไหม (ไม่บังคับ)' : 'มาตรงเวลาไหม เป็นเพื่อนร่วมกิจกรรมที่ดีไหม (ไม่บังคับ)' }}" class="field mt-3">{{ $formOpen ? old('comment') : '' }}</textarea>
                                @if($formOpen) @error('comment')<p class="field-error">{{ $message }}</p>@enderror @endif
                                <button class="btn btn-primary mt-4">ส่งรีวิวผู้ใช้</button>
                            </form>
                        @endif
                    @else
                        @php $received = $receivedByPerson->get($person->id, collect()); @endphp
                        @if($received->isNotEmpty())
                            <ul class="mt-4 divide-y divide-line">
                                @foreach($received as $review)
                                    @php $adminFormOpen = (int) old('user_review_id') === $review->id; @endphp
                                    <li class="py-3 first:pt-0 last:pb-0">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="min-w-0 truncate text-sm text-ink-muted">จาก <x-ui.user-link :user="$review->reviewer" class="font-medium text-ink">{{ $review->reviewer->name }}</x-ui.user-link></p>
                                            <p class="flex shrink-0" role="img" aria-label="{{ $review->rating }} จาก 5 ดาว">@for($i = 1; $i <= 5; $i++)<x-ui.icon name="star" solid class="h-4 w-4 {{ $i <= $review->rating ? 'text-sun' : 'text-line-strong' }}" />@endfor</p>
                                        </div>
                                        @if($review->comment)<p class="break-anywhere mt-2 whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{{ $review->comment }}</p>@endif
                                        @include('users.partials.admin-delete-user-review', ['review' => $review, 'open' => $adminFormOpen])
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-4 text-sm text-ink-muted">ยังไม่มีรีวิวในกิจกรรมนี้</p>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <div class="mt-6 flex flex-col items-center rounded-card border border-dashed border-line-strong bg-white/60 px-6 py-10 text-center text-ink-muted">
            <x-ui.empty-art name="empty-search" />
            <p class="mt-3">ยังไม่มีคนอื่นที่ถูกเช็กชื่อว่ามาร่วมกิจกรรมนี้</p>
        </div>
    @endif
</div>
@endsection
