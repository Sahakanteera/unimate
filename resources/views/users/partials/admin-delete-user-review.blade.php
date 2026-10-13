{{-- Admin ลบรีวิวผู้ใช้ที่ไม่เหมาะสม ต้องใส่เหตุผล (บันทึกในประวัติการดำเนินการ) ใช้: $review, $open --}}
<details class="group mt-3" @if($open) open @endif>
    <summary class="inline-flex cursor-pointer items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium text-bad transition hover:bg-bad-soft">
        <x-ui.icon name="shield" class="h-3.5 w-3.5" /> ลบรีวิวนี้ (ผู้ดูแลระบบ)
    </summary>
    <form method="POST" action="{{ route('admin.user-reviews.destroy', $review) }}" class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-start">
        @csrf @method('DELETE')
        <input type="hidden" name="user_review_id" value="{{ $review->id }}">
        <div class="min-w-0 flex-1">
            <label for="user-review-moderation-{{ $review->id }}" class="sr-only">เหตุผลการลบรีวิว</label>
            <input id="user-review-moderation-{{ $review->id }}" name="moderation_reason" required minlength="3" maxlength="1000" value="{{ $open ? old('moderation_reason') : '' }}" placeholder="เหตุผลการลบ (บังคับ แจ้งผู้เขียนรีวิว)" class="field h-10 text-sm">
            @if($open)
                @error('moderation_reason')<p class="field-error">{{ $message }}</p>@enderror
            @endif
        </div>
        <button class="btn btn-sm shrink-0 bg-bad text-white hover:brightness-110" onclick="return confirm('ยืนยันลบรีวิวนี้?')">ลบรีวิว</button>
    </form>
</details>
