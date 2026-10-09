# แนวทาง UI ของ UniMate (ประยุกต์จาก fastwork.com)

เอกสารนี้สรุปภาษาออกแบบที่ใช้กับทุกหน้าใน `layouts.app` ให้ทีมใช้คลาสและ component เดียวกันเมื่อเพิ่มหน้าใหม่

## ที่มา

- ต้นแบบ: `https://fastwork.com` ซึ่ง redirect ไปที่ `/selling` (ภาษาไทย `/th/selling`) ตรวจจริงเมื่อ 8 ต.ค. 2026 ที่ความกว้าง 1440px และ 390px
- ระดับการอ้างอิง: **ประยุกต์** ใช้ภาษาออกแบบ (ฟอนต์ สี รูปทรง ระยะ และรูปแบบ component) โดยไม่คัดลอกเนื้อหา รูปภาพ โลโก้ หรือไฟล์ฟอนต์ของ fastwork
- ฟังก์ชัน ข้อมูล และกฎของระบบเดิมไม่เปลี่ยน ยกเว้นนักศึกษาที่เข้าสู่ระบบจะเข้ามาที่หน้ากิจกรรมแทนหน้าแก้โปรไฟล์

## ค่าที่วัดจากต้นแบบ

| องค์ประกอบ | ค่าบน fastwork |
|---|---|
| ฟอนต์ | Google Sans ทั้งหน้าอังกฤษและไทย (มีชุดอักษรไทย U+0E01–0E5B) |
| พื้นหลัง / การ์ด | `#f5f5f3` / `#ffffff` |
| ตัวอักษร | `#171717` หลัก, `#2b313b` หัวข้อ, `#50555e` รอง, `#737373` และ `#8d9197` จาง |
| ปุ่มหลัก | พื้น `#131922` ตัวขาว สูง 48px มุม 999px ขนาด 16px น้ำหนัก 500 |
| ปุ่มรอง | ขอบ 1px พื้นโปร่ง มุม 999px |
| หัวข้อใหญ่ | 64px น้ำหนัก 500 letter-spacing −1.6px |
| แท็บ | ใช้งานอยู่ `#171717` น้ำหนัก 500 + เส้นใต้ inset 2px, ไม่ได้เลือก `#737373` ขนาด 15px |
| การ์ด | มุม 24px (การ์ดใหญ่) และ 14px (ช่องย่อย) เงาบาง `0 2px 4px rgba(0,0,0,.08)` |
| ช่องตัวเลข | ช่องนับถอยหลังพื้นเทาอ่อน มุม 14px ตัวเลขใหญ่ + ป้ายเล็ก |
| สีเน้น | ไล่สี `#82bcff → #2483ff → #ff66f4 → #ff3029 → #fe7b02` ใช้กับคำเดียวในหัวข้อ |
| โพสต์อิท | พาสเทล `#b3efbd #b3f4ef #d3bdff #ffafa3 #ffe299 #fdd3a8` + ฟอนต์ลายมือ Playpen Sans |
| Header | แคปซูลลอยโปร่งแสง blur 12px โลโก้ซ้าย ปุ่มหลักขวา |
| Motion | ease `cubic-bezier(0.16, 1, 0.3, 1)` ระยะ 160–420ms |

## สิ่งที่ปรับให้เข้ากับ UniMate

- ใช้ Tailwind CDN ตามเดิม (ตรึงเวอร์ชัน `3.4.17`) จึงไม่ต้อง build ฝั่ง frontend เพิ่ม
- ปุ่มทั่วไปสูง 44px (ขนาดขั้นต่ำที่นิ้วกดง่าย) ปุ่มสำคัญ 48px
- ไล่สีบนพื้นสว่างใช้โทนเข้มขึ้น (`#0569ff → #d43bc9 → #e05a00`) ให้ contrast ≥ 3:1 สำหรับตัวใหญ่ บนพื้นเข้มใช้สีเดิมของต้นแบบ
- สีจาง (`ink-faint`) ใช้ `#737373` แทน `#8d9197` ให้ผ่าน WCAG AA บนพื้นขาว
- กิจกรรมไม่มีรูปของตัวเอง การ์ดจึงใช้แถบสีพาสเทลประจำหมวดกับไอคอนกระดาษ 3 มิติแทนภาพปก (ดูหัวข้อ "ภาพ")
- มือถือใช้เมนูล่างแบบแคปซูลลอย (กิจกรรม / สร้างโพสต์ / นัดของฉัน) แทนแถบเมนูเลื่อนข้างเดิม
- ข้อความไทยในโน้ตตกแต่งกำหนดจุดขึ้นบรรทัดเอง เพราะเบราว์เซอร์ตัดคำไทยบางคำผิด
- ส่วนหัวหน้ากิจกรรมเป็นแถบสีอ่อนเปิดโล่ง (ไม่ครอบการ์ด) ใช้สีพื้นแบบ fastwork (ฟ้าอ่อน `#b7d4ef` · เขียวเทา `#d5e8e4` · ทราย `#f3e6b8`) และย่อลงเมื่อค้นหาหรือเปิดหน้าถัดไป
- ดาวคะแนนและจุดบนโลโก้ใช้สีเหลืองของ fastwork (`sun` = `#f5c518`) เป็นไอคอน SVG ไม่ใช้ตัวอักษร ★
- ช่องกรอกใช้ตัวอักษร 16px บนมือถือ เพื่อไม่ให้ iPhone ซูมหน้าจอเมื่อแตะ
- ข้อความแจ้งสำเร็จหายเองใน 4 วินาที ส่วนข้อความผิดพลาดค้างไว้จนผู้ใช้กดปิด
- หน้ารวมกิจกรรมเรียงกิจกรรมที่ยังไม่จบไว้ก่อนตามเวลาเริ่ม ส่วนที่จบแล้วต่อท้าย (จบล่าสุดก่อน)

## ภาพ

ภาพทั้งหมดเจนด้วย AI ตาม `docs/image-prompts-th.md` (ประวัติการเจนอยู่ใน `docs/image-status-th.md`) ไฟล์ที่เว็บใช้อยู่ใน `public/images/web/` และโลโก้ `public/images/brand/unimate-mark.svg` ส่วนไฟล์ต้นฉบับ PNG ขนาดใหญ่ย้ายไปไว้ที่ `storage/app/image-sources/` ซึ่งไม่เข้า git

| ภาพ | ใช้ที่ | component |
|---|---|---|
| โลโก้ (กระดาษโน้ตเหลือง + U) | header, footer, หน้าเข้าสู่ระบบ, favicon | `<x-ui.brand-mark class="h-9 w-9" />` |
| ภาพถ่ายนักศึกษา 4 คน | แผงซ้ายหน้าเข้าสู่ระบบ/สมัครสมาชิก (มือถืออยู่เหนือฟอร์ม) | `auth/partials/showcase` |
| ไอคอนกระดาษ 3 มิติประจำหมวด | หัวการ์ดกิจกรรม, หัวหน้ารายละเอียดของหมวดที่ไม่มีภาพถ่าย, หน้าจัดการหมวด | `<x-ui.category-art>`, `<x-ui.category-icon>` |
| ภาพถ่ายบรรยากาศประจำหมวด | หัวหน้ารายละเอียดกิจกรรมเท่านั้น พร้อมป้าย "ภาพประกอบหมวด…" | `<x-ui.activity-cover>` |
| ภาพตอนยังไม่มีข้อมูล / 403 / 404 | ผลค้นหาว่าง, นัดของฉัน, แจ้งเตือน, คำขอเข้าร่วม, หน้าแจ้งข้อผิดพลาด | `<x-ui.empty-art name="…" />` |
| ภาพการ์ตูนกลุ่มเพื่อน | ข้างฟอร์มสร้าง/แก้ไขกิจกรรม (เฉพาะจอใหญ่) | `activities/form` |
| ภาพแชร์ลิงก์ | `og:image` ใน `layouts.app` | - |

กติกา:

- ภาพถ่ายหมวดหนึ่งมีภาพเดียว ห้ามใช้เป็นปกการ์ดทุกใบ เพราะจะซ้ำกันทั้งหน้าและไม่ตรงเรื่อง (กิจกรรมวิ่งหรือบาสจะได้ภาพตีแบด) หน้ารวมจึงใช้ไอคอนประจำหมวด และหน้ารายละเอียดต้องมีป้ายบอกว่าเป็นภาพประกอบหมวด
- หมวดที่ผู้ดูแลเพิ่มใหม่ใช้ไอคอน "อื่น ๆ" และไม่แสดงภาพถ่าย (`Ui::categoryPhoto()` คืน `null`) ถ้าจะเพิ่มภาพให้หมวดใหม่ ให้เพิ่มชื่อใน `Ui::CATEGORY_SLUGS` พร้อมไฟล์ `public/images/web/categories/<slug>.webp`, `<slug>-192.webp` และ `photos/activities/<slug>-640.webp`, `<slug>-1280.webp`
- ไอคอนกระดาษใช้ที่ขนาด 40px ขึ้นไป เล็กกว่านี้รายละเอียดจะเบลอ (ไอคอนกีฬาจะดูเหมือนแว่นขยายค้นหา) ป้ายหมวดหมู่ขนาดเล็กจึงเป็นตัวหนังสืออย่างเดียว
- ภาพตกแต่งใส่ `alt=""` หรือ `aria-hidden="true"` และกำหนด `width`/`height` ทุกครั้ง ภาพที่ไม่ได้อยู่บนสุดของหน้าใส่ `loading="lazy"`
- component ภาพรับขนาดผ่าน prop หรือ class ที่ผู้เรียกกำหนดเอง อย่าใส่คลาสขนาดเริ่มต้นใน component แล้วส่งคลาสขนาดอื่นทับ เพราะคลาสที่ชนะขึ้นกับลำดับใน CSS ของ Tailwind ไม่ใช่ลำดับที่เขียน (เคยทำให้ไอคอนหน้าจัดการหมวดเหลือ 24px แทน 48px)

## Tokens (ใช้เป็นคลาส Tailwind)

- สี: `canvas`, `ink` / `ink-soft` / `ink-muted` / `ink-faint`, `line` / `line-strong`, `night`, `brand-50…700`, `sun`, `tint-mint|aqua|lilac|coral|butter|peach`, `ok` / `warn` / `bad` (+ `-soft`)
- มุม: `rounded-tile` (14px), `rounded-card` (24px) · เงา: `shadow-soft`, `shadow-lift`, `shadow-float`
- ฟอนต์: `font-sans` (Google Sans), `font-note` (Playpen Sans Thai) · easing: `ease-out-expo`

## คลาสส่วนกลาง (`resources/views/partials/theme.blade.php`)

| กลุ่ม | คลาส |
|---|---|
| ปุ่ม | `btn` + `btn-primary` / `btn-secondary` / `btn-ghost` / `btn-danger` / `btn-success`, ขนาด `btn-sm` / `btn-lg` / `btn-icon` |
| กล่อง | `card`, `panel`, `stat` + `stat-value` / `stat-label` |
| ป้าย | `chip` + `chip-ok` / `chip-warn` / `chip-bad` / `chip-info` / `chip-dark` / `chip-outline`, `badge-count` |
| ฟอร์ม | `field-label`, `field` (+ `field-invalid`), `field-hint`, `field-error`, `star-rating` |
| แท็บ | `tabs`, `tab`, `tab-active`, `tab-count` |
| ตัวอักษร | `page-title`, `section-title`, `text-gradient`, `text-gradient-bright` (บนพื้นเข้ม), `link`, `break-anywhere` (ข้อความที่ผู้ใช้พิมพ์เอง เช่น ชื่อกิจกรรม สถานที่ ให้ตัดบรรทัดได้แม้เป็นคำยาว) |
| Motion | `reveal` (ค่อย ๆ ขึ้นตามลำดับด้วย `style="--i: n"`) ปิดเองเมื่อผู้ใช้ตั้ง reduced motion |

## Blade components และ partials

- `<x-ui.icon name="clock" class="h-4 w-4" />` ดูชื่อไอคอนทั้งหมดใน `components/ui/icon.blade.php` ไอคอนทึบใช้ `solid` เช่น `<x-ui.icon name="star" solid />`
- `<x-ui.activity-status :state="App\Support\Ui::activityState($activity, $approvedCount)" />` ป้ายสถานะ เปิดรับ / เต็มแล้ว / กำลังดำเนินการ / จบแล้ว / ยกเลิกแล้ว (ใส่ `:remaining` เพื่อบอกที่ว่าง)
- `<x-ui.time :value="$model->created_at" />` วันเวลาแบบไทยในแท็ก `<time>` (`mode="relative"` เช่น 2 ชั่วโมงที่แล้ว, `mode="date"`)
- `App\Support\Ui::tint($id)` สีพาสเทลประจำหมวดหมู่หรือผู้ใช้ ใช้ร่วมกันทุกหน้า
- `<x-ui.avatar :user="$user" size="sm" />` ขนาด `xs` / `sm` / `md` / `lg` / `xl` ถ้าไม่มีรูปจะแสดงอักษรย่อบนพื้นพาสเทล
- `<x-ui.date-tile :date="$activity->starts_at" tint="bg-tint-mint" />`
- `<x-activity-card :activity="$activity" />` ต้องโหลด `user`, `category`, `withCount` และ `withAvg` แบบเดียวกับ `ActivityController@index`
- partials: `nav`, `bottom-nav`, `flash`, `footer`, `pagination` (`{{ $items->links('partials.pagination') }}`), `admin.partials.header`

ตัวอย่างหน้าใหม่:

```blade
@extends('layouts.app')
@section('title', 'หัวข้อ | UniMate')
@section('content')
    <h1 class="page-title">หัวข้อหน้า</h1>
    <p class="mt-2 text-ink-muted">คำอธิบายสั้น ๆ</p>
    <div class="card mt-6 p-6">
        <label for="name" class="field-label">ชื่อ</label>
        <input id="name" name="name" class="field">
        <button class="btn btn-primary mt-4">บันทึก</button>
    </div>
@endsection
```

## ข้อควรรู้

- Tailwind CDN สร้าง CSS จากคลาสที่อยู่ใน HTML จริง จึงประกอบชื่อคลาสแบบไดนามิกได้ แต่ keyframes ใน config จะถูกสร้างเมื่อมีคลาส `animate-*` ใช้งานเท่านั้น
- ลิงก์ที่ครอบทั้งการ์ด (`after:absolute after:inset-0`) ต้องมี `after:z-10` เพราะ avatar เป็น `relative` จะบังจุดคลิก
- ตัวตรวจ impeccable จะเตือนเรื่อง gradient text และโทนม่วงชมพู ซึ่งตั้งใจให้ตรงกับต้นแบบ และใช้เพียงคำเดียวต่อหน้า ส่วนคำเตือนป้ายในแผงตัวกรองที่ยังพับอยู่ และชื่อ/bio ที่ตัดด้วย … เป็นการตรวจที่คลาดเคลื่อน
- อย่าครอบการ์ดซ้อนการ์ด: รายการย่อยในการ์ดใช้เส้นคั่น (`divide-y`) หรือพื้น `bg-canvas` แทนกรอบ
- กิจกรรมที่เริ่มแล้วปิดรับคำขอ ผู้จัดจะเห็นปุ่ม "เช็กชื่อผู้เข้าร่วม" ไปที่แท็บอนุมัติแล้ว เพราะผู้ที่ถูกเช็กว่ามาเท่านั้นจึงรีวิวได้
