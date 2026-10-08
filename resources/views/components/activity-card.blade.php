{{--
    การ์ดกิจกรรมในหน้ารวมกิจกรรม: ทั้งใบกดได้ (stretched link) สีช่องวันที่แยกตามหมวดหมู่
    ต้องมี user, category และ approved_participants_count / reviews_count / reviews_avg_rating จาก query ของหน้า
--}}
@props(['activity', 'heading' => 'h3'])
@php
    $count = $activity->approved_participants_count ?? $activity->approvedCount();
    $state = App\Support\Ui::activityState($activity, $count);
    $closed = in_array($state, ['cancelled', 'ended'], true);
    $tint = $closed ? 'bg-canvas' : App\Support\Ui::tint($activity->category_id);
    $pct = $activity->capacity > 0 ? min(100, (int) round($count / $activity->capacity * 100)) : 0;
    $start = $activity->starts_at->copy()->locale('th');
    $sameDay = $activity->starts_at->isSameDay($activity->ends_at);
    $capacityNote = match ($state) {
        'open' => 'ว่าง '.max(0, $activity->capacity - $count).' ที่',
        'full' => 'เต็มแล้ว',
        'ongoing' => 'เริ่มแล้ว · ปิดรับคำขอ',
        default => null,
    };
@endphp
<article {{ $attributes->class([
    'group relative flex flex-col card transition duration-300 ease-out-expo hover:-translate-y-0.5 hover:shadow-lift has-[a:focus-visible]:ring-4 has-[a:focus-visible]:ring-brand-600/25',
    'opacity-80 hover:opacity-100' => $closed,
]) }}>
    <div class="flex items-start gap-4 p-5 pb-0">
        <x-ui.date-tile :date="$activity->starts_at" :tint="$tint" />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="chip chip-outline">{{ $activity->category->name }}</span>
                <x-ui.activity-status :state="$state" />
            </div>
            <{{ $heading }} class="break-anywhere mt-2.5 text-[1.0625rem] font-medium leading-snug text-ink line-clamp-2">
                <a href="{{ route('activities.show', $activity) }}" class="outline-none after:absolute after:inset-0 after:z-10 after:rounded-card">{{ $activity->title }}</a>
            </{{ $heading }}>
        </div>
    </div>

    <div class="flex flex-1 flex-col px-5 pb-5 pt-3">
        <p class="break-anywhere text-sm leading-relaxed text-ink-muted line-clamp-2">{{ Str::limit($activity->description, 140) }}</p>
        <dl class="mt-4 space-y-2 text-sm text-ink-soft">
            <div class="flex items-center gap-2">
                <dt class="sr-only">วันเวลา</dt>
                <x-ui.icon name="clock" class="h-4 w-4 text-ink-faint" />
                <dd class="tabular-nums">{{ $start->isoFormat('dd D MMM') }} · {{ $activity->starts_at->format('H:i') }}{{ $sameDay ? '–'.$activity->ends_at->format('H:i') : ' ถึง '.$activity->ends_at->copy()->locale('th')->isoFormat('D MMM') }}</dd>
            </div>
            <div class="flex min-w-0 items-center gap-2">
                <dt class="sr-only">สถานที่</dt>
                <x-ui.icon name="map-pin" class="h-4 w-4 text-ink-faint" />
                <dd class="truncate" title="{{ $activity->location }}">{{ $activity->location }}</dd>
            </div>
        </dl>
        <div class="mt-auto pt-4">
            <div class="flex items-center justify-between gap-3 text-xs">
                <span class="font-medium tabular-nums text-ink-soft">ผู้เข้าร่วม {{ $count }}/{{ $activity->capacity }} คน</span>
                @if($capacityNote)
                    <span class="{{ $state === 'full' ? 'text-bad' : 'text-ink-muted' }}">{{ $capacityNote }}</span>
                @endif
            </div>
            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-canvas" role="presentation">
                <div class="h-full rounded-full {{ $state === 'full' ? 'bg-bad' : 'bg-night' }}" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-line px-5 py-3.5">
        <div class="flex min-w-0 items-center gap-2">
            <x-ui.avatar :user="$activity->user" size="sm" />
            <span class="truncate text-sm text-ink-soft" title="{{ $activity->user->name }}">{{ $activity->user->name }}</span>
        </div>
        @if($activity->reviews_count > 0)
            <span class="flex shrink-0 items-center gap-1 text-sm tabular-nums text-ink-soft"><x-ui.icon name="star" solid class="h-4 w-4 text-sun" /> {{ number_format($activity->reviews_avg_rating, 1) }} <span class="text-ink-muted">({{ $activity->reviews_count }} รีวิว)</span></span>
        @else
            <x-ui.icon name="arrow-right" class="h-4 w-4 text-ink-faint transition duration-300 ease-out-expo group-hover:translate-x-0.5 group-hover:text-ink" />
        @endif
    </div>
</article>
