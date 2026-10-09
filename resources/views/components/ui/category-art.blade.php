{{--
    แถบหมวดหมู่: พื้นสีพาสเทลประจำหมวด + ไอคอนกระดาษ 3 มิติ ใช้เป็นหัวการ์ดกิจกรรม
    เป็นสัญลักษณ์ของหมวด ไม่ใช่ภาพของกิจกรรมนั้น จึงซ่อนจากโปรแกรมอ่านหน้าจอ
    ใช้: <x-ui.category-art :category="$activity->category->name" :tint="$tint" class="rounded-t-card" />
--}}
@props(['category' => null, 'tint' => 'bg-canvas', 'muted' => false, 'size' => 'sm'])
<div aria-hidden="true" {{ $attributes->class(['relative overflow-hidden', $tint, 'h-24' => $size === 'sm', 'h-40 sm:h-48' => $size !== 'sm']) }}>
    <img src="{{ asset(App\Support\Ui::categoryIcon($category, 192)) }}" alt="" width="192" height="192" loading="lazy" decoding="async"
         @class([
             'absolute -rotate-6 object-contain motion-safe:transition motion-safe:duration-500 motion-safe:ease-out-expo',
             'right-5 top-2 h-20 w-20 motion-safe:group-hover:-translate-y-1 motion-safe:group-hover:rotate-0' => $size === 'sm',
             'right-8 top-4 h-32 w-32 sm:h-40 sm:w-40' => $size !== 'sm',
             'opacity-50 grayscale' => $muted,
         ])>
</div>
