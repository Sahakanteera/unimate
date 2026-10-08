{{--
    การ์ดกิจกรรมในหน้ารวมกิจกรรม: ทั้งใบกดได้ (stretched link) สีช่องวันที่แยกตามหมวดหมู่
    ต้องมี user, category และ approved_participants_count / reviews_count / reviews_avg_rating จาก query ของหน้า
--}}
@props(['activity'])
@php
    $count = $activity->approved_participants_count ?? $activity->approvedCount();
    $isCancelled = $activity->status === 'cancelled';
    $isEnded = ! $isCancelled && $activity->isEnded();
    $isFull = ! $isCancelled && ! $isEnded && $count >= $activity->capacity;
    $tints = ['bg-tint-mint', 'bg-tint-aqua', 'bg-tint-lilac', 'bg-tint-coral', 'bg-tint-butter', 'bg-tint-peach'];
    $tint = ($isCancelled || $isEnded) ? 'bg-canvas' : $tints[$activity->category_id % count($tints)];
    $pct = $activity->capacity > 0 ? min(100, (int) round($count / $activity->capacity * 100)) : 0;
    $start = $activity->starts_at->copy()->locale('th');
    $sameDay = $activity->starts_at->isSameDay($activity->ends_at);
@endphp
<article {{ $attributes->class([
    'group relative flex flex-col card transition duration-300 ease-out-expo hover:-translate-y-0.5 hover:shadow-lift has-[a:focus-visible]:ring-4 has-[a:focus-visible]:ring-brand-600/25',
    'opacity-80 hover:opacity-100' => $isCancelled || $isEnded,
]) }}>
    <div class="flex items-start gap-4 p-5 pb-0">
        <x-ui.date-tile :date="$activity->starts_at" :tint="$tint" />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="chip chip-outline">{{ $activity->category->name }}</span>
                @if($isCancelled)
                    <span class="chip chip-bad">ยกเลิกแล้ว</span>
                @elseif($isEnded)
                    <span class="chip">จบแล้ว</span>
                @elseif($isFull)
                    <span class="chip chip-warn">เต็มแล้ว</span>
                @else
                    <span class="chip chip-ok">เปิดรับ</span>
                @endif
            </div>
            <h2 class="mt-2.5 text-[17px] font-medium leading-snug text-ink line-clamp-2 break-words">
                <a href="{{ route('activities.show', $activity) }}" class="outline-none after:absolute after:inset-0 after:z-10 after:rounded-card">{{ $activity->title }}</a>
            </h2>
        </div>
    </div>

    <div class="flex flex-1 flex-col px-5 pt-3 pb-5">
        <p class="text-sm leading-relaxed text-ink-muted line-clamp-2 break-words">{{ Str::limit($activity->description, 140) }}</p>
        <dl class="mt-4 space-y-2 text-sm text-ink-soft">
            <div class="flex items-center gap-2">
                <dt class="sr-only">วันเวลา</dt>
                <x-ui.icon name="clock" class="h-4 w-4 text-ink-faint" />
                <dd>{{ $start->isoFormat('dd D MMM') }} · {{ $activity->starts_at->format('H:i') }}{{ $sameDay ? '–'.$activity->ends_at->format('H:i') : ' ถึง '.$activity->ends_at->copy()->locale('th')->isoFormat('D MMM') }}</dd>
            </div>
            <div class="flex items-center gap-2">
                <dt class="sr-only">สถานที่</dt>
                <x-ui.icon name="map-pin" class="h-4 w-4 text-ink-faint" />
                <dd class="truncate">{{ $activity->location }}</dd>
            </div>
        </dl>
        <div class="mt-auto pt-4">
            <div class="flex items-center justify-between text-xs">
                <span class="font-medium text-ink-soft">ผู้เข้าร่วม {{ $count }}/{{ $activity->capacity }} คน</span>
                @unless($isCancelled || $isEnded)
                    <span class="{{ $isFull ? 'text-bad' : 'text-ink-muted' }}">{{ $isFull ? 'เต็มแล้ว' : 'ว่าง '.max(0, $activity->capacity - $count).' ที่' }}</span>
                @endunless
            </div>
            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-canvas" role="presentation">
                <div class="h-full rounded-full {{ $isFull ? 'bg-bad' : 'bg-night' }}" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-line px-5 py-3.5">
        <div class="flex min-w-0 items-center gap-2">
            <x-ui.avatar :user="$activity->user" size="sm" />
            <span class="truncate text-sm text-ink-soft">{{ $activity->user->name }}</span>
        </div>
        @if($activity->reviews_count > 0)
            <span class="shrink-0 text-sm text-ink-soft"><span class="text-amber-500">★</span> {{ number_format($activity->reviews_avg_rating, 1) }} <span class="text-ink-muted">({{ $activity->reviews_count }} รีวิว)</span></span>
        @else
            <x-ui.icon name="arrow-right" class="h-4 w-4 text-ink-faint transition duration-300 ease-out-expo group-hover:translate-x-0.5 group-hover:text-ink" />
        @endif
    </div>
</article>
