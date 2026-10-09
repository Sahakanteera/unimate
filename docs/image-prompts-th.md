# Prompt สำหรับเจนรูปของ UniMate

ไฟล์นี้รวม prompt สำหรับเจนรูปด้วย ChatGPT หรือ Codex ทุก prompt บอกว่ารูปไปอยู่หน้าไหนและใช้ทำอะไร เพื่อให้ GPT เข้าใจบริบท รูปเหล่านี้ยังไม่ได้ต่อเข้าโค้ด

## แนวคิดภาพ

- แนวคิดของเว็บคือ **บอร์ดประกาศในมหาวิทยาลัย** นักศึกษาแปะโน้ตชวนเพื่อนไปทำกิจกรรม หน้าเว็บจึงมีโพสต์อิทสีพาสเทลอยู่แล้ว
- รูปทั้งชุดจึงใช้สไตล์ **กระดาษตัดซ้อนชั้น** (เรียกในไฟล์นี้ว่า "Paper Mates") ทำจากกระดาษโน้ตสีเดียวกับเว็บ มีเงาระหว่างชั้นให้ดูมีมิติ
- fastwork ใช้รูปถ่ายคนจริงเพราะเป็นผลงานของฟรีแลนซ์จริง UniMate ไม่มีรูปกิจกรรมจริง รูปถ่ายคนที่เจนขึ้นมาจะดูเหมือนรูปสต็อกหรือหลักฐานปลอม จึงใช้ภาพประกอบแทน แต่ยังวางภาพคู่กับการ์ดและโน้ตแบบ fastwork
- โลโก้ใช้ภาพแบนเรียบ (flat) ไม่ใช้สไตล์กระดาษ เพราะต้องอ่านออกที่ 16px และต้องวาดใหม่เป็น SVG
- ในรูปห้ามมีตัวอักษร เพราะโมเดลเขียนภาษาไทยเพี้ยน ข้อความทั้งหมดอยู่ใน HTML

## วิธีใช้

ทางลัด: ส่ง **Prompt รวม** (หัวข้อถัดจากตาราง) ใน Codex ครั้งเดียว แล้ว Codex จะเจนครบทุกรูปตามลำดับเอง ถ้าจะส่งทีละรูป ให้ทำตามขั้นตอนนี้

1. เปิดแชตใหม่แล้วส่ง **Prompt 0** หนึ่งครั้ง เพื่อตั้งสไตล์ (ยังไม่เจนรูป) ถ้าเปิดแชตใหม่ภายหลังให้ส่งซ้ำ
2. เจนตามลำดับในตาราง โลโก้ก่อน แล้วตามด้วยไอคอนหมวด "กีฬา" (C1) ซึ่งเป็นต้นแบบสไตล์ของภาพกระดาษทุกรูป
3. ตั้งแต่รูปที่ 5 เป็นต้นไป ให้แนบรูป C1 ที่ผ่านแล้วไปด้วย แล้วพิมพ์ต่อท้าย prompt ว่า
   `Match the paper style, lighting and colours of the attached image exactly.`
4. บน Codex ให้บันทึกไฟล์ตามบรรทัด `Output file` (สร้างโฟลเดอร์ `public/images/...` ถ้ายังไม่มี) ถ้าเจนบน ChatGPT เว็บ ให้ดาวน์โหลดแล้ววางไว้ที่ path เดียวกัน
5. เก็บไฟล์ PNG ต้นฉบับไว้ ตอนต่อเข้าเว็บจะย่อขนาดและแปลงเป็น WebP

## รายการรูป

| ลำดับ | ID | รูป | ความสำคัญ | ขนาดเจน | พื้นหลัง | ไฟล์ |
|---|---|---|---|---|---|---|
| 1 | A1 | โลโก้ 4 แบบให้เลือก | จำเป็น | 1024×1024 | `#F5F5F3` | `public/images/brand/logo-concepts.png` |
| 2 | A2 | โลโก้ตัวจริง | จำเป็น | 1024×1024 | โปร่งใส | `public/images/brand/unimate-mark.png` |
| 3 | A3 | ไอคอนแอป / favicon | จำเป็น | 1024×1024 | `#131922` | `public/images/brand/app-icon.png` |
| 4 | C1 | หมวดกีฬา (ต้นแบบสไตล์) | จำเป็น | 1024×1024 | โปร่งใส | `public/images/categories/sports.png` |
| 5 | B1 | ภาพหลักหน้าเข้าสู่ระบบ | จำเป็น | 1536×1024 | โปร่งใส | `public/images/illustrations/hero-mates.png` |
| 6 | C2 | หมวดติวหนังสือ | จำเป็น | 1024×1024 | โปร่งใส | `public/images/categories/tutoring.png` |
| 7 | C3 | หมวดท่องเที่ยว | จำเป็น | 1024×1024 | โปร่งใส | `public/images/categories/travel.png` |
| 8 | C4 | หมวดจิตอาสา | จำเป็น | 1024×1024 | โปร่งใส | `public/images/categories/volunteer.png` |
| 9 | C5 | หมวดดนตรี | จำเป็น | 1024×1024 | โปร่งใส | `public/images/categories/music.png` |
| 10 | C6 | หมวดอื่น ๆ และหมวดที่เพิ่มใหม่ | จำเป็น | 1024×1024 | โปร่งใส | `public/images/categories/other.png` |
| 11 | D1 | ค้นหาแล้วไม่พบกิจกรรม | ควรมี | 1024×1024 | โปร่งใส | `public/images/illustrations/empty-search.png` |
| 12 | D2 | นัดของฉันยังว่าง | ควรมี | 1024×1024 | โปร่งใส | `public/images/illustrations/empty-my-activities.png` |
| 13 | D3 | ไม่มีการแจ้งเตือน | ควรมี | 1024×1024 | โปร่งใส | `public/images/illustrations/empty-notifications.png` |
| 14 | D4 | ยังไม่มีคำขอเข้าร่วม | ควรมี | 1024×1024 | โปร่งใส | `public/images/illustrations/empty-requests.png` |
| 15 | E1 | หน้า 403 ไม่มีสิทธิ์ | ควรมี | 1024×1024 | โปร่งใส | `public/images/illustrations/error-403.png` |
| 16 | E2 | หน้า 404 ไม่พบหน้า | เสริม | 1024×1024 | โปร่งใส | `public/images/illustrations/error-404.png` |
| 17 | F1 | รูปตอนแชร์ลิงก์ (LINE, Facebook) | เสริม | 1536×1024 | `#F5F5F3` | `public/images/og/unimate-og.png` |

---

## Prompt รวม: ให้ Codex ไล่ทำทั้งหมด

ส่งใน Codex ครั้งเดียว ถ้าอยากใช้โลโก้แนวอื่น ให้เปลี่ยนเลขในบรรทัด "ค่าที่เลือกไว้" ก่อนส่ง ถ้างานหยุดกลางทาง (เช่น โควตาเต็ม) ให้ส่ง prompt เดิมซ้ำ แล้ว Codex จะทำต่อจากรูปที่ค้าง

```text
เจนรูปทั้งหมดของเว็บ UniMate ใน repo นี้ให้ครบในรอบเดียว ทำต่อเองทีละรูปจนจบ ไม่ต้องรอผมสั่ง

ค่าที่เลือกไว้: โลโก้ใช้แนวที่ 1

1. อ่าน docs/image-prompts-th.md ทั้งไฟล์ก่อนเริ่ม แล้วใช้ตาราง "รายการรูป" เป็นลำดับงาน ขนาด พื้นหลัง และไฟล์ปลายทาง ถ้ามี docs/image-status-th.md อยู่แล้ว ให้ข้ามรูปที่บันทึกว่าผ่าน และทำต่อจากรูปที่ค้าง
2. ประกอบ prompt ของแต่ละรูปเอง โดยข้ามประโยคใน Prompt 0 ที่ให้ตอบ "พร้อม" แล้วรอ
   - โลโก้ A1–A3: ใช้ 2 ย่อหน้าแรกของ Prompt 0 (บริบทของเว็บ) ตามด้วย prompt ของรูปนั้น ไม่ใช้สไตล์กระดาษ
   - รูปอื่นทั้งหมด: ใช้ Prompt 0 ทั้งก้อน ตามด้วย prompt ของรูปนั้น
3. แนบรูปอ้างอิง
   - A2: แนบ A1 แทน [N] ด้วยเลขแนวโลโก้ที่เลือก และคัดคำอธิบายแนวนั้นจาก prompt ของ A1 มาใส่ด้วย
   - A3: แนบ A2
   - ตั้งแต่ B1 เป็นต้นไป: แนบ C1 แล้วต่อท้าย prompt ว่า "Match the paper style, lighting and colours of the attached image exactly."
   - ถ้าเครื่องมือแนบรูปไม่ได้ ให้ทำต่อโดยไม่แนบ แล้วจดไว้ในรายงาน
4. บันทึกเป็น PNG ที่ path ในบรรทัด Output file (สร้างโฟลเดอร์ถ้ายังไม่มี)
5. เปิดดูทุกรูปหลังเจน แล้วตรวจตาม "เกณฑ์ตรวจก่อนนำไปใช้" ถ้าในเครื่องมี Python และ Pillow อยู่แล้ว ให้ตรวจด้วยว่ารูปที่ต้องโปร่งใสมี alpha จริง
   - ถ้าไม่ผ่าน ให้ใช้ข้อความจากตาราง "Prompt แก้เมื่อผลไม่ตรง" เจนใหม่ได้ไม่เกิน 2 ครั้งต่อรูป ถ้ายังไม่ผ่าน ให้เก็บรูปที่ดีที่สุด จดปัญหา แล้วไปรูปถัดไป
   - ยกเว้น C1: ถ้าแก้ 2 ครั้งแล้วยังไม่ผ่าน ให้หยุดและรายงานผม เพราะรูปที่เหลือใช้ C1 เป็นต้นแบบสไตล์
6. หลังทำแต่ละรูป ให้อัปเดต docs/image-status-th.md เป็นตาราง ID | ไฟล์ | สถานะ (ผ่าน / มีปัญหา / ยังไม่ทำ) | จำนวนครั้งที่เจน | หมายเหตุ ถ้าโควตาหรือ rate limit เต็ม ให้หยุดตรงนั้น รอบหน้าจะได้ทำต่อจากไฟล์นี้

ข้อห้าม
- แก้ได้เฉพาะไฟล์ใน public/images/ และ docs/image-status-th.md ห้ามแก้โค้ด Blade, PHP หรือ CSS เพราะ Claude จะต่อรูปเข้าเว็บเอง
- ห้าม commit หรือ push
- ใช้เครื่องมือสร้างรูปที่มีในตัวเท่านั้น ห้ามเรียก API ด้วย API key และห้ามติดตั้งโปรแกรมหรือแพ็กเกจ ถ้าไม่มีเครื่องมือสร้างรูป ให้หยุดแล้วบอกผม
- ในรูปห้ามมีตัวอักษร ยกเว้นตัว U ในโลโก้ และเลข 1–4 ในรูป A1

เมื่อจบงาน ให้สรุปสั้น ๆ ว่าได้กี่รูป รูปไหนมีปัญหา และรูปไหนควรให้ผมดูก่อน
```

---

## Prompt 0: ตั้งสไตล์ (ส่งครั้งแรกในแชต)

```text
You are generating images for UniMate, a web app for Thai university students. Students post activities (sports, tutoring, trips, volunteering, music) and find classmates to join them. If you can read this repository: the app is Laravel + Blade, and its visual language is documented in docs/ui-design-th.md and resources/views/partials/theme.blade.php.

The interface is calm and minimal: warm off-white page #F5F5F3, white rounded cards (24 px radius), near-black text #171717, dark panels #131922, black pill buttons, brand blue #0569FF, sun yellow #F5C518 for rating stars, and pastel sticky notes written in a handwriting font. The brand idea is a campus notice board: a student pins a sticky note to invite friends to an activity.

Unless I say otherwise, every image in this chat uses the "Paper Mates" style:
- Medium: handcrafted layered paper-cut illustration, like a small paper diorama photographed in a soft studio. Every shape is cut from matte sticky-note paper with rounded corners. Each object has 2 to 4 stacked paper layers, with soft, realistic shadows between the layers and a soft contact shadow underneath, so it feels three-dimensional.
- Surface: subtle paper fibre texture, matte. No gloss, plastic, clay, glow or metal.
- Light: soft diffuse light from the top left, gentle shadows falling to the bottom right.
- Palette: stay close to these exact colours, with no colour cast. Mint #B3EFBD, aqua #B3F4EF, lilac #D3BDFF, coral #FFAFA3, butter #FFE299, peach #FDD3A8, white #FFFFFF, off-white #F5F5F3. Accents: brand blue #0569FF and sun yellow #F5C518. Use dark #131922 or #2B313B only for small details such as eyes, strings or a pencil tip. You may use slightly darker tones of these colours for depth.
- Shapes: simple, rounded, friendly silhouettes that still read at 48 px, with generous empty space around the subject.
- People (only when asked): stylised paper-doll Thai university students with simple faces (dot eyes, small smile), a mix of men and women, Southeast Asian skin tones, wearing casual campus clothes or a Thai university uniform (white shirt with black skirt or trousers). No real university logos or emblems.
- Never include: text, letters, numbers, writing on notes (use blank notes or a few soft pencil lines), logos, watermarks, UI elements, frames, borders, scenery backgrounds, gradient backgrounds, neon, confetti, or photorealistic people.
- Background: transparent PNG unless I say otherwise. If transparency is impossible, use flat #F5F5F3 with no texture or vignette.

The logo prompts are the only exception: logos are flat vector-style marks, not paper.

Reply only "พร้อม" and wait for my next prompt. Do not generate an image yet.
```

---

## A1: โลโก้ 4 แบบให้เลือก

- **ใช้ทำอะไร:** เลือกแนวโลโก้ก่อนทำตัวจริง รูปนี้ไม่ได้ขึ้นเว็บ
- **แทนของเดิม:** ตัว U สีขาวบนกล่องเข้ม ที่มีจุดเหลืองมุมขวาบน (`resources/views/partials/nav.blade.php`) ผู้ใช้เข้าใจผิดว่าจุดนั้นคือแจ้งเตือนที่ยังไม่ได้อ่าน
- **ผมแนะนำแบบ 1** เพราะเชื่อมกับโพสต์อิททั้งเว็บ และที่ขนาด favicon ยังเห็นเป็นกระดาษโน้ตสีเหลืองกับตัว U ชัด

```text
Design the logo mark (symbol only) for UniMate. Logos are flat, not the paper style.

Where it will be used: the floating pill header on every page (36 px, left of the word "UniMate" set in Google Sans Medium as live text), the login card (48 px), the browser tab favicon (16 to 32 px) and the phone home-screen icon. It replaces the current placeholder: a white letter "U" on a dark rounded square with a small yellow dot in the top-right corner. Users read that dot as an unread-notification badge, so no concept may have a small dot or circle near a corner.

Meaning: "Uni" + "Mate", students finding friends for an activity through a note pinned on a campus board. Friendly, simple and confident, at home in a minimal interface with black pill buttons and pastel sticky notes.

Show 4 concepts in a 2x2 grid on a flat #F5F5F3 background. Put each mark large in its own white rounded tile, with a small number 1 to 4 under the tile:
1. Sticky-note U: a rounded-square sticky note in sun yellow #F5C518 with its bottom-right corner folded up (the fold a slightly darker yellow), and a bold, rounded, geometric letter U in #131922 in the middle.
2. Two mates: two rounded figures standing shoulder to shoulder whose bodies join at the bottom to form a U. Their round heads sit on top of the two stems and lean slightly toward each other. Colours #131922 and #0569FF.
3. Meet-up pin: a map-pin shape in #0569FF whose inner cut-out is a U shape, meaning "meet here".
4. Magnet U: a thick, rounded U-shaped magnet in #131922 with sun-yellow #F5C518 tips, meaning "UniMate pulls friends together".

Rules for every concept:
- Flat solid colours. No gradients, shadows, textures, 3D or paper effect.
- Built only from simple geometric shapes (circles, rounded rectangles, arcs) so it can be redrawn exactly as SVG.
- At most 2 colours plus white. No strokes thinner than 1/12 of the mark width.
- Still recognisable at 16 px.
- No text other than the 4 small numbers.

Size 1024x1024.
Output file: public/images/brand/logo-concepts.png
```

## A2: โลโก้ตัวจริง

- **ใช้ที่:** header ทุกหน้า (36px), การ์ดเข้าสู่ระบบและสมัครสมาชิก (48px), footer
- **หลังเจน:** วาดใหม่เป็น `public/images/brand/unimate-mark.svg` ให้คมทุกขนาด ตัวหนังสือ "UniMate" ข้างโลโก้ยังเป็น HTML เหมือนเดิม
- เปลี่ยน `[N]` เป็นเลขแบบที่เลือก

```text
Take concept [N] from the previous image and produce the final UniMate logo mark.
- 1024x1024, transparent background. The mark is centred and fills about 80% of the width.
- Flat solid fills in the exact colours of that concept, plus white where needed. No gradients, shadows, textures, outlines or 3D.
- Geometry: simple circles, rounded rectangles and arcs, with consistent corner radii and stroke weights. Perfectly symmetrical where the design is symmetrical. Crisp edges.
- No text. The only letterform allowed is the U that belongs to the mark.
- No dot or small circle near any corner.
- It will be shown at 16, 32, 36, 48 and 180 px, so it must still read at 16 px.
Output file: public/images/brand/unimate-mark.png
```

## A3: ไอคอนแอปและ favicon

- **ใช้ที่:** ไอคอนบนแท็บเบราว์เซอร์ (แทนโลโก้ Laravel ใน `public/favicon.ico`, `public/favicon.svg`) และไอคอนเมื่อกด "เพิ่มไปยังหน้าจอโฮม" บนมือถือ (`public/apple-touch-icon.png`)

```text
Using the final UniMate mark from the previous image, make the app icon for the browser favicon and the iPhone home-screen icon (apple-touch-icon).
- 1024x1024, full-bleed square background #131922. No rounded corners, because iOS rounds them itself. No border.
- The mark is centred and about 62% of the width. Change colours only where needed for contrast on the dark background (for example, #131922 parts become white).
- Flat. No gradient, shadow or text.
Output file: public/images/brand/app-icon.png
```

---

## C1: หมวดกีฬา (ต้นแบบสไตล์ของภาพกระดาษ)

- **ใช้ที่:**
  - การ์ดกิจกรรม `resources/views/components/activity-card.blade.php` ประมาณ 64px
  - ส่วนหัวหน้ารายละเอียด `resources/views/activities/show.blade.php` ประมาณ 112px
  - ของตกแต่งลอยข้างโพสต์อิทในส่วนหัวหน้ากิจกรรม `resources/views/activities/index.blade.php` 96–140px
- **การจับคู่:** ชื่อหมวดหมู่ → ไฟล์ (กีฬา `sports`, ติวหนังสือ `tutoring`, ท่องเที่ยว `travel`, จิตอาสา `volunteer`, ดนตรี `music`) หมวดอื่นทั้งหมด รวมถึงหมวดที่ผู้ดูแลเพิ่มภายหลัง ใช้ `other`
- **สีพื้นช่อง:** ใช้ตามลำดับ id ของ seed ปัจจุบัน กีฬาอยู่บนช่องสีฟ้าอมเขียว (aqua) วัตถุจึงต้องไม่ใช้สีนั้นเป็นหลัก

```text
Paper Mates style. Category icon for the UniMate activity category "กีฬา" (sports).

Where it will be used:
- Beside each activity of this category on the activity cards (about 64 px).
- Large in the header of the activity detail page (about 112 px).
- As a floating decoration next to the pastel sticky notes in the hero of the activities page (96 to 140 px, slightly rotated).
This is the first of six category icons and it sets the style for the rest, so keep it clean and typical of the style.

Subject: a badminton racket crossed with a shuttlecock.
- Racket: coral #FFAFA3 frame, sun-yellow #F5C518 grip, strings suggested by a few thin white paper lines.
- Shuttlecock: white paper feathers with a brand-blue #0569FF band, slightly in front of the racket and tilted as if it was just hit.

Composition: one compact object group seen at a 3/4 angle from slightly above, centred, filling about 70% of the canvas, with a soft contact shadow underneath and nothing else.
It usually sits on an aqua #B3F4EF tile, so aqua must not be a main colour.

1024x1024, transparent background, no text.
Output file: public/images/categories/sports.png
```

## B1: ภาพหลักหน้าเข้าสู่ระบบและสมัครสมาชิก

- **ใช้ที่:** แผงสีเข้มด้านซ้าย `resources/views/auth/partials/showcase.blade.php` (แสดงบนจอคอม) ภาพอยู่ครึ่งล่างของแผง โดยมีโพสต์อิท HTML ลอยอยู่ด้านบน
- **บนมือถือ:** แผงนี้ถูกซ่อน จึงจะวางภาพนี้แบบเล็กเหนือฟอร์มเข้าสู่ระบบ ภาพต้องดูดีทั้งบนพื้นเข้มและพื้นสว่าง
- แนบรูป C1 ไปด้วย

```text
Paper Mates style. Hero illustration for the UniMate sign-in and sign-up pages.

Where it will be used:
- Desktop: the left brand panel of the sign-in page. It is a dark #131922 panel with 24 px rounded corners, about 590x650 px. The Thai headline "หาเพื่อนร่วมกิจกรรมได้ในไม่กี่คลิก" sits at the top, four pastel sticky notes written in HTML float in the middle, and three step pills sit at the bottom. This image goes in the lower half of that panel.
- Phones: the panel is hidden, so a smaller copy goes above the login form on the light #F5F5F3 page.
It must look good on both the dark and the light background.

Scene: four paper-cut Thai university students (two women, two men) standing close together in a friendly cluster, just met for an activity. Two of them are giving each other a high-five. Each one carries a prop that hints at an activity type:
1. Sporty outfit, holding a badminton racket and a shuttlecock.
2. Thai university uniform (white short-sleeve shirt, black skirt), hugging a small stack of books.
3. Casual T-shirt, with an acoustic guitar on their back.
4. Casual clothes and garden gloves, holding a small potted mangrove sapling.

Composition:
- Full bodies, centred, the group about 70% of the image height, standing on one soft oval shadow.
- Leave the top 25% of the image empty so the HTML sticky notes can float above.
- Keep the group compact, so that a centred square crop still shows all four people.

Colours: clothes and props from the pastel palette, with brand blue and sun yellow accents. Give the paper layers a thin light edge so the figures separate from a dark background.

1536x1024, transparent background, no text, no notes with writing, no background scenery.
Output file: public/images/illustrations/hero-mates.png
```

## C2–C6: หมวดหมู่ที่เหลือ

แนบรูป C1 ไปทุกครั้ง แต่ละ prompt ใช้บริบทเดียวกับ C1

```text
Paper Mates style. Category icon for the UniMate activity category "ติวหนังสือ" (tutoring and study groups). Same use, size and composition as the sports icon, and part of the same set.

Subject: a small stack of three closed books with covers in butter #FFE299, mint #B3EFBD and coral #FFAFA3 and white page edges. A sun-yellow pencil lies diagonally across the top, and a blank aqua sticky-note bookmark peeks out of the middle book.
It usually sits on a lilac #D3BDFF tile, so lilac must not be a main colour.

1024x1024, transparent background, no text.
Output file: public/images/categories/tutoring.png
```

```text
Paper Mates style. Category icon for the UniMate activity category "ท่องเที่ยว" (trips and outings). Same use, size and composition as the sports icon, and part of the same set.

Subject: a folded paper map with mint, aqua and butter panels and a soft dashed route. A brand-blue #0569FF map pin stands on the map, and a small butter-yellow backpack sits behind it.
It usually sits on a coral #FFAFA3 tile, so coral and peach must not be main colours.

1024x1024, transparent background, no text.
Output file: public/images/categories/travel.png
```

```text
Paper Mates style. Category icon for the UniMate activity category "จิตอาสา" (volunteering, for example planting mangroves or cleaning up campus). Same use, size and composition as the sports icon, and part of the same set.

Subject: a young mangrove sapling with mint and deeper green paper leaves and a few slim prop roots, growing from a small mound of soft brown paper soil. A small coral paper heart leans against the mound.
It usually sits on a butter #FFE299 tile, so yellow must not be a main colour.

1024x1024, transparent background, no text.
Output file: public/images/categories/volunteer.png
```

```text
Paper Mates style. Category icon for the UniMate activity category "ดนตรี" (music jams and practice). Same use, size and composition as the sports icon, and part of the same set.

Subject: an acoustic guitar at a 3/4 angle. The body is aqua and white paper layers with a coral pickguard, a white sound-hole ring and #2B313B strings drawn as thin lines. Two small lilac paper music notes float beside it.
It usually sits on a peach #FDD3A8 tile, so peach and orange must not be main colours.

1024x1024, transparent background, no text.
Output file: public/images/categories/music.png
```

```text
Paper Mates style. Category icon for "อื่น ๆ" (other), also used as the fallback for any new category an admin adds later, so it must feel generic. Same use, size and composition as the sports icon, and part of the same set.

Subject: a butter-yellow sticky note with a curled bottom corner, pinned by a brand-blue #0569FF pushpin. The note is blank except for two soft pencil lines. A small sun-yellow paper star is tucked beside it.
It usually sits on a mint #B3EFBD tile, so mint must not be a main colour.

1024x1024, transparent background, no text.
Output file: public/images/categories/other.png
```

---

## D1–D4: ภาพตอนยังไม่มีข้อมูล

- **ใช้ที่:** แทนไอคอนเล็กในกล่องสีเทา แสดงประมาณ 128–160px กลางการ์ดสีขาว เหนือข้อความไทยหนึ่งบรรทัดและปุ่ม
- **โทน:** ชวนให้ทำต่อ ไม่เศร้า แนบรูป C1 ไปด้วย

```text
Paper Mates style. Small empty-state illustration for the UniMate activities page, shown when a search or filter finds no activity. The Thai message under it says "ไม่พบกิจกรรมที่ตรงกับตัวกรอง", followed by buttons to clear the filters or create an activity. It is shown at about 140 px, centred on a white card.

Subject: a brand-blue paper magnifying glass leaning against two or three blank pastel sticky notes (aqua, butter, lilac), one note slightly curled. The mood is curious and light: nothing found yet, so try another search.
Composition: centred, filling about 65% of the canvas, soft contact shadow, nothing else.

1024x1024, transparent background, no text.
Output file: public/images/illustrations/empty-search.png
```

```text
Paper Mates style. Small empty-state illustration for the UniMate page "นัดของฉัน" (my activities), shown when the student has not created or joined any activity yet. The Thai messages say "คุณยังไม่ได้สร้างกิจกรรม" or "คุณยังไม่ได้ขอเข้าร่วมกิจกรรมใด", followed by a button. It is shown at about 140 px, centred on a white card.

Subject: a white paper desk-calendar page with a coral header strip and an empty grid (no numbers). A white paper airplane is taking off from it with a short dotted trail in brand blue. The mood says "start your first meet-up".
Composition: centred, filling about 65% of the canvas, soft contact shadow, nothing else.

1024x1024, transparent background, no text.
Output file: public/images/illustrations/empty-my-activities.png
```

```text
Paper Mates style. Small empty-state illustration for the UniMate notifications page, shown when there are no notifications. The Thai message says "ไม่มีการแจ้งเตือนในขณะนี้". It is shown at about 140 px, centred on a white card.

Subject: a sun-yellow paper bell resting calmly, tilted slightly, with a small lilac paper crescent moon and two tiny white paper stars beside it. The mood is quiet: all caught up.
Do not add any badge, dot, number or check-mark bubble on or near the bell, because that looks like an unread notification.
Composition: centred, filling about 65% of the canvas, soft contact shadow, nothing else.

1024x1024, transparent background, no text.
Output file: public/images/illustrations/empty-notifications.png
```

```text
Paper Mates style. Small empty-state illustration for the organiser's join-request page in UniMate, shown when no request has this status yet. The Thai message says "ไม่มีคำขอในสถานะนี้". It is shown at about 140 px, centred on a white card.

Subject: an empty, open aqua paper inbox tray. A white paper envelope glides toward it from the upper left, with a short dotted motion line in brand blue. The mood is calm and patient: requests will arrive.
Composition: centred, filling about 65% of the canvas, soft contact shadow, nothing else.

1024x1024, transparent background, no text.
Output file: public/images/illustrations/empty-requests.png
```

---

## E1–E2: หน้าแจ้งข้อผิดพลาด

- **E1 ใช้ที่:** `resources/views/errors/403.blade.php` แทนไอคอนกุญแจในกล่องสีชมพูส้ม แสดงประมาณ 160px
- **E2 ใช้ที่:** หน้า 404 ที่ยังไม่มี ต้องสร้าง `resources/views/errors/404.blade.php` เพิ่มตอนต่อเข้าเว็บ (ตอนนี้ใช้หน้าเริ่มต้นของ Laravel)

```text
Paper Mates style. Illustration for the UniMate "403: you don't have permission" page. It sits above the Thai headline "คุณไม่มีสิทธิ์ดำเนินการนี้", which tells the user that only the post owner can edit an activity and only admins can open admin pages. It is shown at about 160 px, centred on a white card.

Subject: a closed paper padlock (butter body, white shackle) resting on a coral sticky note, with a strip of lilac washi tape across the note. The meaning is "this note is reserved for its owner". Calm and polite, not alarming: no red warning signs or exclamation marks.
Composition: centred, filling about 65% of the canvas, soft contact shadow, nothing else.

1024x1024, transparent background, no text.
Output file: public/images/illustrations/error-403.png
```

```text
Paper Mates style. Illustration for the UniMate "404: page not found" page, shown above a short Thai message and a button back to the activities page. It is shown at about 160 px, centred on a white card.

Subject: a folded paper map (mint, aqua, butter panels). A brand-blue dotted path wanders and loops across it without reaching a brand-blue map pin standing at one edge. The mood is light: "we took a wrong turn".
Composition: centred, filling about 65% of the canvas, soft contact shadow, nothing else.

1024x1024, transparent background, no text, no question marks.
Output file: public/images/illustrations/error-404.png
```

---

## F1: รูปตอนแชร์ลิงก์

- **ใช้ที่:** แท็ก `og:image` ใน `layouts.app` เป็นรูปตัวอย่างเวลาแชร์ลิงก์ใน LINE หรือ Facebook
- **หลังเจน:** ตัดเป็น 1200×630 แล้ววางโลโก้และสโลแกนไทยตรงกลางด้วยโค้ด เพราะโมเดลเขียนภาษาไทยเพี้ยน

```text
Paper Mates style. Social share image for UniMate, used as the og:image preview when a link is shared in LINE or Facebook.

Subject: on a flat #F5F5F3 background, six small paper objects in a loose ring around the edges, each slightly rotated:
- a badminton racket with a shuttlecock
- a stack of books with a pencil
- a folded map with a blue pin
- a mangrove sapling
- an acoustic guitar
- a blank butter sticky note with a blue pushpin
Leave the central 50% completely empty. The logo and a Thai tagline will be added there in code later.

Composition:
- The image will be cropped to 1200x630 (1.91:1), so keep every object inside the central horizontal band and away from the outer 6% of the edges.
- Soft contact shadows only.

1536x1024, solid #F5F5F3 background (not transparent), no text.
Output file: public/images/og/unimate-og.png
```

---

## Prompt แก้เมื่อผลไม่ตรง

| ปัญหา | ส่งต่อในแชตเดิม |
|---|---|
| มีตัวอักษรหรือลายมือโผล่ | `Remove every letter, number and scribble that looks like writing. Keep everything else identical.` |
| พื้นหลังไม่โปร่งใส | `Same image with a fully transparent background (PNG alpha). Keep the soft shadow under the object.` |
| สีเพี้ยนหรือติดเหลือง | `Correct the colours to the UniMate palette exactly (mint #B3EFBD, aqua #B3F4EF, lilac #D3BDFF, coral #FFAFA3, butter #FFE299, peach #FDD3A8, blue #0569FF, yellow #F5C518). No warm colour cast.` |
| ดูเป็นดินน้ำมันหรือพลาสติก | `This looks like clay or plastic. Make it cut matte paper: flat paper layers with visible cut edges and soft shadows between the layers.` |
| รายละเอียดเยอะ ย่อแล้วดูไม่ออก | `Simplify: fewer, larger paper pieces. It must read clearly at 48 px.` |
| สไตล์ไม่เข้าชุดกับรูปก่อน | `Match the paper style, lighting, camera angle and colours of the attached image exactly.` |

## เกณฑ์ตรวจก่อนนำไปใช้

- ไม่มีตัวอักษร ตัวเลข หรือโลโก้อื่นในรูป (ยกเว้นตัว U ในโลโก้)
- พื้นหลังตรงตามตาราง และเงาไม่โดนตัดขอบ
- ย่อดูที่ 48px แล้วยังรู้ว่าเป็นอะไร (ไอคอนหมวดหมู่ 24px ยังพอเห็นรูปร่าง)
- สไตล์ แสง และมุมกล้องเข้าชุดกับ C1
- โลโก้ไม่มีจุดหรือวงกลมเล็กตรงมุม

## ตอนต่อเข้าเว็บ (ทำหลังได้รูปครบ)

- วาดโลโก้ใหม่เป็น SVG แล้วสร้าง `favicon.svg`, `favicon.ico`, `apple-touch-icon.png` (180px) ใหม่ และเปลี่ยนโลโก้ใน `partials/nav.blade.php`, หน้า login/register และ footer
- ย่อรูปเป็น 2 เท่าของขนาดที่แสดงแล้วแปลงเป็น WebP ใส่ `width`/`height` และ `loading="lazy"` ยกเว้นภาพหลัก
- เพิ่มฟังก์ชันใน `App\Support\Ui` ให้คืนรูปของหมวดหมู่จากชื่อหมวด ถ้าไม่รู้จักชื่อให้ใช้ `other`
- ภาพตกแต่งใส่ `alt=""` และ `aria-hidden="true"` เพราะข้อความในหน้าบอกความหมายครบแล้ว
- ตรวจทั้งคอม (1440px) และมือถือ (390px) พร้อมรัน Pest และ E2E ชุดเดิม
