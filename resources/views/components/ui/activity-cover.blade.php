{{--
    ภาพหัวหน้ารายละเอียดกิจกรรม: ภาพถ่ายบรรยากาศของหมวด พร้อมป้ายบอกว่าเป็นภาพประกอบ
    หมวดที่ไม่มีภาพถ่ายของตัวเอง (เช่น หมวดที่ผู้ดูแลเพิ่มใหม่) ใช้แถบไอคอนแทน เพื่อไม่ให้ภาพผิดเรื่อง
--}}
@props(['category' => null, 'tint' => 'bg-canvas', 'sizes' => '100vw'])
@php $photo = App\Support\Ui::categoryPhoto($category); @endphp
@if($photo)
    <div aria-hidden="true" {{ $attributes->class(['relative aspect-video overflow-hidden bg-canvas']) }}>
        <img src="{{ asset($photo.'-640.webp') }}"
             srcset="{{ asset($photo.'-640.webp') }} 640w, {{ asset($photo.'-1280.webp') }} 1280w"
             sizes="{{ $sizes }}" alt="" width="1280" height="720" fetchpriority="high" decoding="async"
             class="h-full w-full object-cover">
        <span class="absolute bottom-3 right-3 rounded-full bg-night/80 px-2.5 py-1 text-xs leading-none text-white">ภาพประกอบหมวด{{ $category }}</span>
    </div>
@else
    <x-ui.category-art :category="$category" :tint="$tint" size="lg" class="{{ $attributes->get('class') }}" />
@endif
