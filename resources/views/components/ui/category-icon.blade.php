{{-- ไอคอนกระดาษ 3 มิติของหมวด ใช้ที่ขนาด 40px ขึ้นไป (เล็กกว่านี้รายละเอียดจะเบลอ) กำหนดขนาดผ่าน class --}}
@props(['category' => null])
<img src="{{ asset(App\Support\Ui::categoryIcon($category)) }}" alt="" width="96" height="96"
     {{ $attributes->class(['shrink-0 object-contain']) }} loading="lazy" decoding="async">
