{{-- ภาพประกอบตอนยังไม่มีข้อมูลและหน้าแจ้งข้อผิดพลาด: <x-ui.empty-art name="empty-search" /> (size="lg" สำหรับหน้า 403/404) --}}
@props(['name', 'size' => 'md'])
<img src="{{ asset('images/web/illustrations/'.$name.'.webp') }}" alt="" width="384" height="384"
     {{ $attributes->class(['mx-auto object-contain', 'h-40 w-40 sm:h-44 sm:w-44' => $size !== 'lg', 'h-48 w-48 sm:h-56 sm:w-56' => $size === 'lg']) }} loading="lazy" decoding="async">
