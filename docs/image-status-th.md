# สถานะรูป UniMate

โปรเจกต์: `C:\Users\Admin\Herd\unimate` · branch `kim` · โลโก้แนวที่ 1

เงื่อนไขปัจจุบัน (2026-10-09): ผู้ใช้ตรวจ A1–A3 และ C1 แล้วแจ้งว่าโอเค อนุญาตให้ใช้รูปเดิมและทำงานให้ครบโดยไม่รอตรวจระหว่างทาง ยกเลิกเงื่อนไขหยุดที่ C1; ใช้ C1 เดิมเป็นต้นแบบต่อ ตรวจคุณภาพเองและบันทึกข้อสังเกตให้ผู้ใช้ตรวจรวดเดียวตอนจบ ข้อจำกัดเรื่องไฟล์ เครื่องมือ ไม่ติดตั้ง และไม่ commit/push ยังเหมือนเดิม

สร้างด้วยเครื่องมือสร้างรูปในตัว ตาม `docs/image-prompts-th.md` โดยใช้บริบท Prompt 0 (ตัดประโยคให้ตอบพร้อมแล้วรอออก) และ prompt ของแต่ละ ID; A1–A3 ใช้เฉพาะบริบทสองย่อหน้าแรก ส่วนภาพกระดาษตั้งแต่ B1 เป็นต้นไปแนบ C1 และประโยคจับคู่สไตล์ตามคู่มือ

| ID | ไฟล์ | สถานะ | จำนวนครั้งที่เจน | หมายเหตุ |
|---|---|---|---|---|
| A1 | `public/images/brand/logo-concepts.png` | ผ่าน | 3 | ผู้ใช้ตรวจแล้วรับใช้ต่อ; PNG 1024×1024; เก็บครั้งที่ 2; ข้อสังเกตเดิม: มีน้ำหนักสี/พื้นผิวเล็กน้อย; ไม่มีอักษรนอก U และเลข 1–4; ใช้แนว 1 |
| A2 | `public/images/brand/unimate-mark.png` | ผ่าน | 3 | ผู้ใช้ตรวจแล้วรับใช้ต่อ; PNG 1024×1024 RGBA; alpha 0–255; แนบ A1 แนว 1; เก็บครั้งที่ 2; ข้อสังเกตเดิม: น้ำหนักสี/ขอบบางจุดเล็กน้อย ควรเก็บตอนวาด SVG |
| A3 | `public/images/brand/app-icon.png` | ผ่าน | 3 | ผู้ใช้ตรวจแล้วรับใช้ต่อ; PNG 1024×1024; แนบ A2; เก็บครั้งที่ 3; mark ราว 63%; ข้อสังเกตเดิม: น้ำหนักสี/สีพื้นคลาดเล็กน้อย |
| C1 | `public/images/categories/sports.png` | ผ่าน | 3 | ผู้ใช้ตรวจแล้วรับเป็นต้นแบบให้ทำต่อ; PNG 1024×1024 RGBA; alpha 0–255; เก็บครั้งที่ 3; ข้อสังเกตเดิม: คราบขาว/ขอบ alpha บางส่วนในช่องเอ็นไม้ |
| B1 | `public/images/illustrations/hero-mates.png` | มีปัญหา | 3 | PNG 1536×1024 RGBA; alpha 0–254; แนบ C1; เก็บครั้งที่ 2; คน 4 คน/props/high-five ครบ ไม่มีอักษร; หลังแก้ครบยังมีแสงฟุ้งและกลุ่มกว้างกว่าครอปสี่เหลี่ยม ควรใช้ภาพเต็มหรือตรวจการครอปตอนต่อเว็บ |
| C2 | `public/images/categories/tutoring.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; หนังสือ 3 เล่ม ดินสอและที่คั่นครบ ไม่มีอักษร สี/แสง/ผิวกระดาษเข้าชุด |
| C3 | `public/images/categories/travel.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; แผนที่พับ หมุดฟ้า กระเป๋าและเส้นทางครบ ไม่มีอักษร สี/แสง/มุมเข้าชุด |
| C4 | `public/images/categories/volunteer.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; ต้นโกงกาง รากค้ำ กองดินและหัวใจครบ ไม่มีอักษร ผิวกระดาษ/แสงเข้าชุด |
| C5 | `public/images/categories/music.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; กีตาร์ aqua/ขาว pickguard coral และโน้ตดนตรี lilac 2 ชิ้นครบ ไม่มีอักษรหรือแบรนด์ |
| C6 | `public/images/categories/other.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; โน้ต butter มุมงอ หมุดฟ้า ดาวเหลืองและเส้นดินสอเรียบ 2 เส้นครบ ไม่มีตัวอักษร |
| D1 | `public/images/illustrations/empty-search.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; แว่นขยายฟ้าและโน้ตเปล่า 3 สีครบ ไม่มีอักษร เงาอยู่ในขอบภาพ |
| D2 | `public/images/illustrations/empty-my-activities.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; ปฏิทินช่องว่าง ไม่มีตัวเลข/ข้อความ เครื่องบินกระดาษและเส้นทางฟ้าครบ |
| D3 | `public/images/illustrations/empty-notifications.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; ระฆังเหลือง พระจันทร์ lilac และดาวขาว 2 ชิ้นครบ ไม่มีตัวอักษร/badge/ตัวเลขแจ้งเตือน |
| D4 | `public/images/illustrations/empty-requests.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; ถาด aqua ว่าง ซองขาวเข้าจากซ้ายบนและเส้นเคลื่อนฟ้าครบ ไม่มีข้อความ |
| E1 | `public/images/illustrations/error-403.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; กุญแจปิด butter/ขาวบนโน้ต coral กับเทป lilac ครบ ไม่มีเลข 403/ข้อความ/สัญญาณเตือน |
| E2 | `public/images/illustrations/error-404.png` | ผ่าน | 1 | PNG 1024×1024 RGBA; alpha จริง; แนบ C1; แผนที่พับ เส้นทางฟ้าวนและหมุดริมแผนที่ครบ ไม่มีเลข 404/อักษร/เครื่องหมายคำถาม |
| F1 | `public/images/og/unimate-og.png` | ผ่าน | 2 | PNG 1536×1024 RGB; แนบ C1; เก็บครั้งที่ 2; วัตถุ 6 กลุ่มครบ กลางว่าง ไม่มีข้อความ/โลโก้ ปรับระยะเพื่อให้ครอป 1200×630 ได้ |

จำนวนครั้งนับการเจนครั้งแรกและการแก้ด้วยเครื่องมือสร้างรูป รวมได้ไม่เกิน 3 ครั้งต่อรูป ตรวจภาพจริงทุกครั้ง และตรวจ PNG/ขนาด/alpha ด้วย Pillow ที่ติดตั้งอยู่แล้ว หากเครื่องมือส่งขนาดไม่ตรงตาราง จะปรับเฉพาะขนาด PNG ตามตารางโดยคงสัดส่วนและ alpha ไม่แก้ภาพหรือวาดส่วนประกอบเอง

## ผลตรวจชุดกระดาษเดิม 17 รูป (2026-10-09)

ทำครบ 17/17 รูป: ผ่าน 16 รูป (รวม A1–A3 และ C1 ที่ผู้ใช้ตรวจรับแล้ว) มีปัญหา 1 รูปคือ B1 และไม่มีรูปค้าง รวมเรียกเครื่องมือสร้างรูป 28 ครั้ง ไม่มีรูปใดเกิน 3 ครั้ง ไม่พบโควตาหรือ rate limit และแนบรูปอ้างอิงได้ครบทุกครั้งที่กำหนด

เปิดตรวจภาพจริงครบทุกไฟล์ รวมภาพย่อ 48/24/16px บนพื้นอ่อนและเข้ม ตรวจโครงสร้าง PNG และขนาดครบ: 15 รูปขนาด 1024×1024 และ B1/F1 ขนาด 1536×1024 รูปที่ต้องโปร่งใสทั้ง 14 รูปมี alpha จริง อีก 3 รูปเป็นพื้นทึบตามโจทย์ ตรวจว่าไม่มีข้อความนอกข้อยกเว้น U และเลข 1–4 ของ A1

ควรดู B1 ก่อน: เก็บผลเจนครั้งที่ 2 ซึ่งดีที่สุดหลังลองแก้ครบ 2 ครั้ง คนและอุปกรณ์ครบ แต่ยังมีแสงฟุ้งรอบกลุ่ม และการครอปกลางเป็นสี่เหลี่ยมอาจตัดอุปกรณ์หรือคนด้านข้าง ควรใช้ภาพเต็มหรือตรวจตำแหน่งครอปเมื่อต่อเว็บ ข้อสังเกตเดิมของ A1–A3/C1 ยังอยู่ในตารางเพื่อให้ตรวจตอนจบได้

ไฟล์ช่วยตรวจ (เป็นภาพรวมจากไฟล์ปลายทาง ไม่รวมใน 17 รูป และไม่ได้เพิ่มข้อความลงภาพ):

- [ภาพรวมทั้งหมด](../public/images/review/unimate-overview.png): เรียงซ้ายไปขวา บนลงล่างตามตาราง แต่ละช่องแสดงพื้นอ่อน/เข้มและภาพย่อ 48/24/16px
- [ตัวอย่าง F1 เมื่อครอป 1200×630](../public/images/review/unimate-og-crop-preview.png): วัตถุทั้ง 6 กลุ่มอยู่ครบและกลางว่าง ไฟล์ F1 สำหรับใช้งานยังเป็น 1536×1024

ตรวจขอบเขตไฟล์แล้ว: แก้เฉพาะ `public/images/` และรายงานนี้ ไม่แก้ Blade/PHP/CSS ไม่ commit/push ใช้เครื่องมือสร้างรูปในตัว ไม่มี API key หรือการติดตั้งโปรแกรม/แพ็กเกจ ตรวจ hash ยืนยันว่าเอกสาร prompt และรูปเดิม A1–A3/C1 ไม่เปลี่ยนหลังผู้ใช้ตรวจรับ งานอยู่บน branch `kim` ที่อัปเดตเท่ากับ `origin/kim` ก่อนเริ่มรอบนี้

## ชุดภาพสมจริงเพิ่มเติม (2026-10-09)

ผู้ใช้อนุญาตให้เริ่มสร้าง 7 ฉากตามข้อเสนอจากหน้าจริง: login/สมัครสมาชิก 1 ฉาก และกิจกรรม 6 หมวด ชุดนี้ใช้ภาพถ่ายสมจริงตามคำขอใหม่ จึงไม่ใช้ Prompt 0 ส่วนสไตล์กระดาษ ไม่แนบ C1 และไม่ใช้ประโยคจับคู่สไตล์กระดาษ ภาพเดิมทั้งชุดเก็บไว้ ใช้เครื่องมือสร้างรูปในตัวเท่านั้น คนและสถานที่ในภาพเป็นภาพสมมติ ไม่ใช่ภาพบันทึกงานหรือมหาวิทยาลัยที่ระบุชื่อ

ไฟล์เป็น PNG พื้นทึบ: P1 ต้นฉบับ 1024×1536 ส่วน P2–P7 ขนาด 1536×1024 วางองค์ประกอบให้ครอป 16:9 ได้ ภาพ P1 จะมีไฟล์ครอปเพิ่มสำหรับคอมพิวเตอร์และมือถือ ไฟล์เหล่านี้ไม่เพิ่มจำนวนฉาก ตรวจความสมจริง ใบหน้า/มือ จำนวนคน อุปกรณ์ บริบทสถานที่ พื้นที่ครอป และการไม่มีอักษร/ตัวเลข/โลโก้หลังเจนทุกภาพ หากจำเป็นแก้ได้ไม่เกิน 2 ครั้งต่อฉากแล้วบันทึกข้อสังเกต

| ID | ไฟล์ | สถานะ | จำนวนครั้งที่เจน | หมายเหตุ |
|---|---|---|---|---|
| P1 | `public/images/photos/auth-campus-friends.png` | ผ่าน | 1 | PNG RGB 1024×1536; นักศึกษา 4 คน คน/มือ/อุปกรณ์สมจริง ไม่มีอักษร; เว้นพื้นที่หัวข้อด้านบน; มีครอปคอมพิวเตอร์ 1024×1280 และมือถือ 1024×512 เป็นไฟล์แยก |
| P2 | `public/images/photos/activities/sports.png` | มีปัญหา | 3 | PNG RGB 1536×1024; เก็บครั้งที่ 3; คน 4 คน/โรงยิม/ไม้แบดสมจริงและครอป 16:9 ได้ หลังแก้ครบยังมีเครื่องหมายขาวเล็กคล้ายแบรนด์บนกางเกงและจุดเล็กบนรองเท้า/ถุงเท้า จดไว้ให้ตรวจตอนจบ |
| P3 | `public/images/photos/activities/tutoring.png` | ผ่าน | 1 | PNG RGB 1536×1024; เพื่อน 4 คนช่วยกันติวในห้องสมุด หน้า/มือและโต๊ะสมจริง หนังสือ/สมุดไม่มีอักษร องค์ประกอบครอป 16:9 ได้ |
| P4 | `public/images/photos/activities/travel.png` | ผ่าน | 1 | PNG RGB 1536×1024; เพื่อน 4 คนบนทางเดินจุดชมวิวภูเขา ฉากธรรมชาติและเป้สมจริง ไม่มีอักษร/ตรา ไม่อ้างชื่อสถานที่จริง ครอป 16:9 ได้ |
| P5 | `public/images/photos/activities/volunteer.png` | ผ่าน | 1 | PNG RGB 1536×1024; นักศึกษาจิตอาสา 3 คนปลูกโกงกาง ถุงมือ/ต้นกล้า/โคลน/ฉากป่าชายเลนสมจริง ไม่มีอักษร/ตรา ครอป 16:9 ได้ |
| P6 | `public/images/photos/activities/music.png` | ผ่าน | 2 | PNG RGB 1536×1024; เก็บครั้งที่ 2 แก้หัวกีตาร์เป็นไม้เรียบแล้ว; คน 3 คน/กีตาร์/คาฮอง/ลานมหาวิทยาลัยครบ มือและเครื่องดนตรีสมจริง ไม่มีอักษร ครอป 16:9 ได้ |
| P7 | `public/images/photos/activities/other.png` | ผ่าน | 1 | PNG RGB 1536×1024; เพื่อน 4 คนเล่นบอร์ดเกมในพื้นที่นั่งร่วมกัน มือ/โต๊ะ/หมากสมจริง กระดานและไพ่ไม่มีอักษร/ตัวเลข ครอป 16:9 ได้ |

### ผลตรวจภาพสมจริงครบชุด

ทำครบ 7/7 ฉาก: ผ่าน 6 ฉาก มีปัญหา 1 ฉากคือ P2 รวมเรียกเครื่องมือ 10 ครั้ง ไม่มีฉากใดเกิน 3 ครั้ง ไม่พบโควตาหรือ rate limit รูปต้นฉบับทั้ง 7 ไฟล์เป็น PNG RGB พื้นทึบ ตรวจโครงสร้างและขนาดแล้ว และ hash ตรงกับผลที่เลือกจากเครื่องมือสร้างรูปโดยไม่มีการแก้เนื้อหาภาพเอง ตรวจ hash ของไฟล์เดิม 20 ไฟล์ (รูปเดิม 19 ไฟล์และเอกสาร prompt เดิม) แล้วไม่เปลี่ยน

ควรดู P1 ก่อนเพื่อเลือกการจัดหน้า login และดู P2 เรื่องเครื่องหมายขนาดเล็กที่ยังติดอยู่หลังแก้ครบ 2 ครั้งแล้ว ภาพกิจกรรม P2–P7 ทดลองครอปกลางเป็น 16:9 แล้ว ใบหน้าและอุปกรณ์หลักอยู่ครบ ภาพเหล่านี้เป็นภาพประกอบสมมติของหมวดกิจกรรม ไม่ใช่หลักฐานว่าผู้ใช้ในระบบเข้าร่วมงานหรือถ่าย ณ สถานที่ที่ระบุ

ไฟล์สำหรับ login/สมัครสมาชิก:

- [ต้นฉบับ P1](../public/images/photos/auth-campus-friends.png): 1024×1536
- [ครอปคอมพิวเตอร์](../public/images/photos/auth-campus-friends-desktop.png): 1024×1280 จากกรอบ (0, 0, 1024, 1280) ของต้นฉบับ เก็บพื้นที่เหนือศีรษะสำหรับข้อความ
- [ครอปมือถือ](../public/images/photos/auth-campus-friends-mobile.png): 1024×512 จากกรอบ (0, 480, 1024, 992) ของต้นฉบับ เห็นใบหน้าทั้ง 4 คน

ไฟล์ครอปทั้ง 2 เป็นการจัดขนาดสำหรับใช้งานจากต้นฉบับเดียวกัน ไม่ใช่การเจนฉากเพิ่ม เมื่อวางข้อความบนภาพควรใช้ชั้นสีเข้มช่วยให้อ่านชัดโดยไม่บังใบหน้า

ไฟล์ช่วยตรวจ:

- [ภาพรวมชุดสมจริง](../public/images/review/unimate-photos-overview.png): ด้านซ้ายเป็น P1 คอมพิวเตอร์/มือถือ ด้านขวาเรียง P2/P3, P4/P5, P6/P7 ไม่มีข้อความเพิ่มลงภาพ
- [ตัวอย่างครอป 16:9](../public/images/review/unimate-photos-crop-preview.png): เรียง P2/P3, P4/P5, P6/P7 ใช้ตรวจภาพปก ไฟล์ต้นฉบับกิจกรรมยังเป็น 1536×1024

ชุดนี้มีไฟล์ใช้งาน 9 PNG (7 ต้นฉบับ + 2 ครอป login) และภาพช่วยตรวจอีก 2 PNG รวมกับชุดก่อนเป็น 24 ฉากที่สร้างแล้ว สถานะและข้อสังเกตของชุดกระดาษเดิมคงไว้ ทำงานบน branch `kim` แก้เฉพาะ `public/images/` กับรายงานนี้ ไม่แก้ Blade/PHP/CSS ไม่ติดตั้ง ไม่ใช้ API key ไม่ commit/push การเชื่อมภาพเข้าเว็บยังเป็นงานของ Claude ตามขอบเขตเดิม


### Prompt ที่ใช้จริง

### P1

```text
Use case: photorealistic-natural.
Project: UniMate, a Thai university app where students find friends to join sports, study groups, trips, volunteering, music and casual activities.
Medium: a believable candid editorial photograph, with natural Southeast Asian skin tones, visible skin and fabric texture, realistic camera optics, restrained warm colour grading, gentle greens, warm whites and occasional muted blue. Natural daylight. A welcoming student community, ordinary contemporary Thai university surroundings. Fictional adults aged 20-24, no identifiable real institution.
Keep anatomy, fingers, faces, equipment and architectural perspective physically coherent.
Text: none. Every visible object, garment, bag, book, sign, screen and piece of equipment is unbranded and has no readable letters, numbers, writing or watermark. No signs or emblems. No illustration, paper-cut styling, collage, studio backdrop or artificial glow. No embedded website interface or decorative frame. Opaque photographic background extending to all edges.
Asset type: the main login and registration photograph. Portrait 2:3 canvas, 1024x1536.
Scene: a leafy Thai university courtyard after classes, a shaded walkway beside a modest modern campus building, soft late-afternoon daylight. Upper 30% is softly defocused foliage and quiet shaded architecture, naturally low-detail and somewhat darker, reserved for a separate HTML headline. No text in the photo.
Subject: exactly four Thai university friends, two women and two men, chatting warmly together, looking at each other rather than at the camera. White campus shirts with plain black skirts or trousers mixed with one muted blue casual overshirt, no ties or badges. One student carries a simple unmarked notebook and another has a plain badminton racket bag over a shoulder.
Composition: eye-level 50mm lifestyle photograph, four friends grouped naturally across the central width, not overlapping faces. Show them from roughly waist up in the middle and lower part of the portrait. All four heads at similar heights, approximately between 40% and 60% down the canvas; keep every face inside the central 80% width. Place expressions and upper bodies within one horizontal band so a short wide mobile crop can retain all four faces. Keep the top clear for copy. Leave modest side margins and do not clip any head. Relaxed ordinary smiles, natural gestures, convincing distinct faces and hands. A single coherent scene.
```

### P2

```text
Use case: photorealistic-natural.
Project: UniMate, a Thai university app where students find friends to join sports, study groups, trips, volunteering, music and casual activities.
Medium: a believable candid editorial photograph, with natural Southeast Asian skin tones, visible skin and fabric texture, realistic camera optics, restrained warm colour grading, gentle greens, warm whites and occasional muted blue. Natural daylight. A welcoming student community, ordinary contemporary Thai university surroundings. Fictional adults aged 20-24, no identifiable real institution.
Keep anatomy, fingers, faces, equipment and architectural perspective physically coherent.
Text: none. Every visible object, garment, bag, book, sign, screen and piece of equipment is unbranded and has no readable letters, numbers, writing or watermark. No signs or emblems. No illustration, paper-cut styling, collage, studio backdrop or artificial glow. No embedded website interface or decorative frame. Opaque photographic background extending to all edges.
Asset type: sports activity cover and detail-page photograph. Landscape 3:2, 1536x1024, with a safe centered 16:9 crop.
Scene: a clean but ordinary Thai university indoor badminton gym, a real net, green court markings, soft daylight through high windows.
Subject: exactly four Thai students, two women and two men in plain understated sportswear, enjoying a friendly break together beside the badminton net. Two hold credible badminton rackets; a single shuttlecock is on the court. Show casual conversation and a small natural smile, no high-five.
Composition: eye-level 50mm medium-wide photograph, group centered and viewed from about the knees up. Faces, rackets and net remain inside the central 80% of the image height so the top and bottom may be cropped for a card. A recognizable badminton scene, ordinary student recreation, coherent hands and racket geometry. No additional people or background posters.
```

### P2 — แก้ครั้งที่ 1 (แนบผลครั้งแรกเป็นภาพเป้าหมาย)

```text
Edit the attached photograph. Preserve the four adult students, their distinct faces, the candid friendly interaction, realistic badminton gym, daylight and natural photographic appearance.
Make these precise corrections: remove every brand-like mark from clothing, especially the small white mark on the woman's black shorts; remove the small background wall signs entirely so no writing, symbols, numbers or logos remain anywhere.
Frame the scene slightly wider and raise or angle the rackets naturally so every racket head and every person's head fits completely within the central horizontal 80% of the image height, safely inside a centered 16:9 crop. Keep hands and racket handles anatomically coherent.
Deliver a 1536x1024 opaque landscape PNG. No text, no watermark, no UI, no added people.
```

### P2 — แก้ครั้งที่ 2 (แนบผลครั้งที่ 2 เป็นภาพเป้าหมาย)

```text
Edit this photograph with the smallest possible visible changes. Preserve the four people, their faces, poses, badminton rackets, gym and realistic daylight.
The second person from the left, the woman with a blue towel, has a small white brand-like stitch on the outer lower right edge of her black shorts. Remove that white mark completely. Her shorts must be plain uninterrupted black cloth with only the simple white edge piping.
Make all socks plain uninterrupted white fabric. Make all footwear plain unbranded white shoes, with no letters, numbers, swooshes, badges, coloured heel marks or icons.
Remove every other label, brand-like mark, sign or readable writing from the photograph. Keep all hands, racket strings, skin and expressions natural.
Return a landscape photograph on a 3:2 canvas, 1536x1024. Keep all heads and all racket frames safely inside the picture. Opaque background. No text, no numbers, no logos or watermark.
```

### P3

```text
Use case: photorealistic-natural.
Project: UniMate, a Thai university app where students find friends to join sports, study groups, trips, volunteering, music and casual activities.
Medium: a believable candid editorial photograph, with natural Southeast Asian skin tones, visible skin and fabric texture, realistic camera optics, restrained warm colour grading, gentle greens, warm whites and occasional muted blue. Natural daylight. A welcoming student community, ordinary contemporary Thai university surroundings. Fictional adults aged 20-24, no identifiable real institution.
Keep anatomy, fingers, faces, equipment and architectural perspective physically coherent.
Text: none. Every visible object, garment, bag, book, sign, screen and piece of equipment is unbranded and has no readable letters, numbers, writing or watermark. No signs or emblems. No illustration, paper-cut styling, collage, studio backdrop or artificial glow. No embedded website interface or decorative frame. Opaque photographic background extending to all edges.
Asset type: tutoring and study-group activity cover and detail-page photograph. Landscape 3:2, 1536x1024, safe centered 16:9 crop.
Scene: a bright Thai university library group-study corner beside large windows, warm wood table, bookshelves softly out of focus, all spines without text.
Subject: exactly four Thai university friends, two women and two men in simple campus shirts and casual campus clothes, sitting around one table and helping each other understand a problem. One student points to a blank open notebook while the others look at it attentively. Two closed unmarked books and plain pencils, no screens.
Composition: eye-level 50mm medium-wide candid photo from the open side of the table. All four faces unobstructed in the middle horizontal band, hands and notebook clearly separated, complete coherent table edge. Calm concentration and friendly connection. No writing, formulas or numbers on any page. Keep the main action safe for a 16:9 crop.
```

### P4

```text
Use case: photorealistic-natural.
Project: UniMate, a Thai university app where students find friends to join sports, study groups, trips, volunteering, music and casual activities.
Medium: a believable candid editorial photograph, with natural Southeast Asian skin tones, visible skin and fabric texture, realistic camera optics, restrained warm colour grading, gentle greens, warm whites and occasional muted blue. Natural daylight. A welcoming student community, ordinary contemporary Thai university surroundings. Fictional adults aged 20-24, no identifiable real institution.
Keep anatomy, fingers, faces, equipment and architectural perspective physically coherent.
Text: none. Every visible object, garment, bag, book, sign, screen and piece of equipment is unbranded and has no readable letters, numbers, writing or watermark. No signs or emblems. No illustration, paper-cut styling, collage, studio backdrop or artificial glow. No embedded website interface or decorative frame. Opaque photographic background extending to all edges.
Asset type: travel and hiking activity cover and detail-page photograph. Landscape 3:2, 1536x1024, safe centered 16:9 crop.
Scene: a lush northern Thai mountain nature trail and viewpoint with layered green hills in the distance, soft clear morning light. A plausible generic setting rather than a named landmark.
Subject: exactly four Thai university-age friends, two women and two men in plain comfortable hiking clothes and daypacks, pausing together on a broad safe trail. They chat and enjoy the view; one looks toward the hills while the others smile naturally at one another. No uniforms, flags, trekking signs or phones.
Composition: eye-level 35mm environmental photograph. Show people from knees up near the center with believable mountain depth visible around them. Keep every face and backpack in the middle 80% height, heads clear of the distant ridge. Convincing ordinary student outing, no cliff-edge pose or staged victory gesture. Preserve visible landscape in a 16:9 crop.
```

### P5

```text
Use case: photorealistic-natural.
Project: UniMate, a Thai university app where students find friends to join sports, study groups, trips, volunteering, music and casual activities.
Medium: a believable candid editorial photograph, with natural Southeast Asian skin tones, visible skin and fabric texture, realistic camera optics, restrained warm colour grading, gentle greens, warm whites and occasional muted blue. Natural daylight. A welcoming student community, ordinary contemporary Thai university surroundings. Fictional adults aged 20-24, no identifiable real institution.
Keep anatomy, fingers, faces, equipment and architectural perspective physically coherent.
Text: none. Every visible object, garment, bag, book, sign, screen and piece of equipment is unbranded and has no readable letters, numbers, writing or watermark. No signs or emblems. No illustration, paper-cut styling, collage, studio backdrop or artificial glow. No embedded website interface or decorative frame. Opaque photographic background extending to all edges.
Asset type: volunteering activity cover and detail-page photograph. Landscape 3:2, 1536x1024, safe centered 16:9 crop.
Scene: an accessible shallow mangrove restoration area in coastal Thailand at low tide, soft morning daylight, green mangroves and muddy ground visibly coherent. No identifiable site markers.
Subject: exactly three Thai adult university volunteers, two women and one man in plain long-sleeve shirts, simple rubber boots and gloves, working together to plant one small mangrove sapling. One kneels to settle the root ball in the mud, one holds the stem gently upright, and the third crouches nearby with another sapling. Hands and plant are visibly separate and anatomically credible.
Composition: eye-level 50mm documentary-style medium environmental photo at the volunteers' crouching height. Faces, hands and sapling occupy the center horizontal band; show enough mud, roots and greenery to understand the place. Respectful cooperative mood, natural smiles and attention to their task. No banners, printed clothing or tools with labels. Protect important content for a 16:9 crop.
```

### P6

```text
Use case: photorealistic-natural.
Project: UniMate, a Thai university app where students find friends to join sports, study groups, trips, volunteering, music and casual activities.
Medium: a believable candid editorial photograph, with natural Southeast Asian skin tones, visible skin and fabric texture, realistic camera optics, restrained warm colour grading, gentle greens, warm whites and occasional muted blue. Natural daylight. A welcoming student community, ordinary contemporary Thai university surroundings. Fictional adults aged 20-24, no identifiable real institution.
Keep anatomy, fingers, faces, equipment and architectural perspective physically coherent.
Text: none. Every visible object, garment, bag, book, sign, screen and piece of equipment is unbranded and has no readable letters, numbers, writing or watermark. No signs or emblems. No illustration, paper-cut styling, collage, studio backdrop or artificial glow. No embedded website interface or decorative frame. Opaque photographic background extending to all edges.
Asset type: music-jam activity cover and detail-page photograph. Landscape 3:2, 1536x1024, safe centered 16:9 crop.
Scene: a shady Thai university courtyard with simple concrete seating, trees and a softly blurred modern faculty building, warm late-afternoon daylight.
Subject: exactly three Thai university friends, one woman and two men in plain casual campus clothes, enjoying a relaxed acoustic jam. One man plays a physically credible wooden acoustic guitar, the other sits on and plays a plain wooden cajon, and the woman listens and sings without a microphone. Natural friendly attention to one another. No audience.
Composition: eye-level 50mm medium-wide candid photograph, all three faces clearly visible, guitar and cajon completely in frame and inside the central safe 16:9 area. Realistic hands, guitar strings, fretboard and posture. No instrument logos, writing, posters, visible music sheets or floating music symbols.
```

### P6 — แก้ครั้งที่ 1 (แนบผลครั้งแรกเป็นภาพเป้าหมาย)

```text
Edit the attached photograph. Keep the three adult students, their faces, bodies, hands, clothes, acoustic jam, cajon, campus setting, framing and daylight exactly as they are.
Replace the ENTIRE front face of the guitar's headstock, the small dark wood section at the right end of the guitar neck, with plain uninterrupted dark brown wood grain. It must be absolutely blank: no white mark, cursive line, lettering, emblem, brand, symbol or inlay anywhere on that wood face. Retain the six physical metal tuning machines and the strings as credible hardware. The neck and guitar body remain unchanged.
A natural photorealistic image on a 1536x1024 landscape 3:2 canvas with an opaque background. No text or watermark anywhere.
```

### P7

```text
Use case: photorealistic-natural.
Project: UniMate, a Thai university app where students find friends to join sports, study groups, trips, volunteering, music and casual activities.
Medium: a believable candid editorial photograph, with natural Southeast Asian skin tones, visible skin and fabric texture, realistic camera optics, restrained warm colour grading, gentle greens, warm whites and occasional muted blue. Natural daylight. A welcoming student community, ordinary contemporary Thai university surroundings. Fictional adults aged 20-24, no identifiable real institution.
Keep anatomy, fingers, faces, equipment and architectural perspective physically coherent.
Text: none. Every visible object, garment, bag, book, sign, screen and piece of equipment is unbranded and has no readable letters, numbers, writing or watermark. No signs or emblems. No illustration, paper-cut styling, collage, studio backdrop or artificial glow. No embedded website interface or decorative frame. Opaque photographic background extending to all edges.
Asset type: casual activities and board-game activity cover and detail-page photograph. Landscape 3:2, 1536x1024, safe centered 16:9 crop.
Scene: a welcoming ordinary campus co-working lounge, a small round wood table near a window, warm natural daylight, softly blurred shelves and indoor plants.
Subject: exactly four Thai university friends, two women and two men in plain campus casual clothes, leaning in and enjoying a tabletop game together. Simple unmarked coloured wooden pieces and plain face-down cards on an abstract board with simple geometric spaces. No dice, letters, numbers, recognizable commercial game branding or printed graphics.
Composition: eye-level 50mm candid medium-wide photo from the open side of the table. Four distinct unobstructed faces and relaxed natural smiles, one hand moving one wooden piece. Keep the table action and faces centered for a safe 16:9 crop. Coherent hands, fingers, chairs, table and pieces. A believable friendly student gathering, no commercial cafe signs or screens.
```


## ประวัติการหยุดรอบแรก (ผู้ใช้อนุญาตให้ทำต่อแล้ว)

หยุดที่ C1 ตามข้อ 5 ของคำสั่งผู้ใช้: แก้ C1 ครบ 2 ครั้งแล้ว (รวมเจน 3 ครั้ง) แต่ยังไม่ผ่านเรื่องคราบขาวและความสะอาดของ alpha ในช่องเอ็นไม้ จึงยังไม่เจน B1 และรูปที่เหลือ 13 รูป เพื่อไม่ใช้ต้นแบบที่ยังมีปัญหา

บันทึกแล้ว 4 รูปจาก 17 รูป; ทั้ง 4 รูปอยู่ในสถานะมีปัญหาตามตาราง ไม่มีรูปที่ทำเครื่องหมายผ่าน รวมเรียกเครื่องมือสร้างรูป 12 ครั้ง ไม่พบโควตาหรือ rate limit ในรอบนี้ แนบรูปอ้างอิงได้ตามที่ต้องใช้ ไม่มีกรณีข้ามการแนบ

ควรตรวจ C1 ก่อน แล้วตรวจ A2/A3 โดยเฉพาะความเรียบของสีและขอบก่อนวาด SVG/สร้าง favicon ต่อ A1 เป็นภาพร่างเลือกแนวและไม่ได้ขึ้นเว็บ โค้ด Blade/PHP/CSS ไม่ได้แก้ และไม่ได้ commit หรือ push

ตรวจไฟล์สุดท้ายแล้วเมื่อ 2026-10-09: ทั้ง 4 ไฟล์เป็น PNG 1024×1024; A2/C1 เป็น RGBA มีทั้งพิกเซลโปร่งใสจริงและทึบจริง (alpha 0–255) เปิดตรวจภาพเต็มและภาพย่อ 48/24/16px บนพื้นอ่อนและพื้นเข้มแล้ว U และรูปทรงกีฬาอ่านออก แต่คราบขาวใน C1 เห็นบนพื้นเข้ม จึงยังไม่เปลี่ยนสถานะเป็นผ่าน ภาพย่อใช้ตรวจในหน่วยความจำเท่านั้น ไม่เพิ่มไฟล์นอกตาราง


## การเชื่อมภาพกับเว็บตามคำขอล่าสุด (2026-10-09)

คำขอ “จากรูปทั้งหมดที่สร้างมา ให้ใช้สกิล Frontend และ design ไปเพิ่มในโปรเจ็คนี้” ขยายขอบเขตจากการเตรียมภาพอย่างเดียวและการรอ Claude เชื่อมเว็บในบันทึกก่อนหน้า มาเป็นการเชื่อมภาพเดิมเข้ากับ Blade/PHP ของ UniMate บน branch `kim` แล้ว ข้อความเดิม ตารางสถานะ prompt จำนวนครั้งที่เจน และประวัติการหยุดยังเก็บเป็นประวัติตามเดิม รอบนี้ไม่มีการเจนฉากเพิ่ม ต้นฉบับทั้ง 24 ฉากยังอยู่ครบ

ต่อเติมหน้าตาเดิมที่กำหนดใน `resources/views/partials/theme.blade.php` และ `docs/ui-design-th.md`: Google Sans สำหรับไทย/อังกฤษ, Playpen Sans Thai สำหรับโน้ตในหน้ากิจกรรม, พื้นออฟไวต์ `#f5f5f3`, การ์ดขาวมุม 24px, ปุ่มแคปซูลสีเข้ม, กระดาษพาสเทล และคำไล่สีที่มีอยู่ โลโก้ใช้แนวที่ 1 ไม่ได้สร้างระบบออกแบบใหม่ เอกสารและไฟล์ธีมเดิมคงไว้; ข้อความเดิมที่ว่ากิจกรรมไม่มีรูปหมายถึงสภาพก่อนเพิ่มภาพปกในรอบนี้

### ตำแหน่งที่เชื่อมภาพ

| กลุ่มภาพ | ตำแหน่งใช้งานและรูปแบบ | ไฟล์ใช้งานหลัก |
|---|---|---|
| A1 และแผ่น QA | เก็บเป็นภาพอ้างอิงแนวโลโก้และการตรวจครอป | `public/images/brand/logo-concepts.png`, `public/images/review/` |
| A2/A3 | โลโก้ SVG สะอาดตามแนว 1 ใน header/footer/auth; favicon และ Apple touch icon | `public/images/brand/unimate-mark.svg`, `public/favicon.svg`, `public/favicon.ico`, `public/apple-touch-icon.png` (180×180) |
| P1 | หน้าเข้าสู่ระบบและสมัครสมาชิกใช้ภาพแนวตั้งบนจอใหญ่ ภาพแถบแนวนอนเมื่อกว้างไม่ถึง 1024px เห็นเพื่อนครบ 4 คน; ชั้นสีเข้มส่วนบนช่วยให้อ่านคำอธิบายเล็กชัด | `public/images/web/photos/auth-campus-friends-desktop.webp` (960×1200), `auth-campus-friends-mobile.webp` (768×384) |
| P2–P7 | ภาพปก 6 หมวดบนการ์ดกิจกรรมและหน้ารายละเอียด อัตราส่วน 16:9 พร้อมป้าย HTML “ภาพประกอบ” | `public/images/web/photos/activities/{sports,tutoring,travel,volunteer,music,other}-{640,1280}.webp` (640×360 / 1280×720) |
| C1–C6 | ไอคอนหมวดในตัวกรอง การ์ด/รายละเอียด นัดของฉัน และหน้าจัดการหมวดของผู้ดูแล | `public/images/web/categories/{sports,tutoring,travel,volunteer,music,other}.webp` (96×96) |
| B1 | ภาพเพื่อนกระดาษเต็มกลุ่มใน sidebar ฟอร์มสร้าง/แก้ไขกิจกรรม วางบนพื้นขาวและคงสัดส่วนโดยไม่ครอปสี่เหลี่ยม | `public/images/web/illustrations/hero-mates.webp` (768×512) |
| D1/D2 | D1 เมื่อค้นหาไม่พบและยังไม่ขอเข้าร่วม; D2 เมื่อยังไม่ได้สร้างกิจกรรม | `public/images/web/illustrations/empty-search.webp`, `empty-my-activities.webp` (384×384) |
| D3/D4 | รายการแจ้งเตือนว่างและคำขอเข้าร่วมว่าง | `public/images/web/illustrations/empty-notifications.webp`, `empty-requests.webp` (384×384) |
| E1/E2 | หน้า 403/404 ของแอปพร้อมลิงก์กลับไปทำงานต่อ | `public/images/web/illustrations/error-403.webp`, `error-404.webp` (384×384) |
| F1 | เชื่อมภาพ OG/Twitter ผ่าน metadata ของ layout | `public/images/web/og/unimate-og.png` (1200×630) |

ใช้ component ร่วม `ui/brand-mark`, `ui/category-icon`, `ui/activity-cover`, `ui/empty-art` และ helper `App\Support\Ui::categorySlug/categoryIcon` จับคู่จากชื่อหมวดภาษาไทย; ชื่อที่ไม่ตรงหมวดเดิมใช้ `other` จึงรองรับการเปลี่ยนชื่อหมวดโดยไม่ผูกกับเลข ID ภาพปกใช้ `srcset`/`sizes` เลือกขนาดตามพื้นที่แสดง รูปตกแต่งกำหนดขนาดและ alt ว่าง ส่วน P1 มีคำอธิบายภาพ ภาพเหล่านี้เป็นภาพประกอบสมมติ ไม่ใช่รูปสมาชิกหรือหลักฐานการเข้าร่วมกิจกรรม

### ไฟล์สำหรับเว็บและที่มา

`public/images/web/manifest.json` บันทึกไฟล์ต้นทาง ไฟล์ส่งออก ขนาด น้ำหนัก และที่มาครบ: raster สำหรับเว็บ 29 ไฟล์ (28 ไฟล์ใน `public/images/web/` และ Apple touch icon ที่ public root) รวม 2,191,149 bytes เทียบกับ PNG ต้นทาง 43,440,335 bytes ที่นับแยกตามแต่ละ variant ใน manifest ลด 95.0% ตัวเลขนี้เป็นการเปรียบเทียบชุดส่งออก ไม่ใช่การลบต้นฉบับหรือการประหยัดพื้นที่ทั้งโฟลเดอร์; SVG และ favicon ICO อยู่นอกยอด raster นี้

เก็บเนื้อหา PNG ต้นฉบับเดิมไว้ทั้งหมด WebP ของไอคอนและภาพกระดาษยังมี alpha โปร่งใสจริง การตรวจที่มาของ raster ในโฟลเดอร์เว็บ 28 ไฟล์ไม่พบไฟล์ที่ขาดต้นทาง ไม่มีการเปลี่ยนรูป avatar ของสมาชิกหรือข้อมูลฐานข้อมูลจริง

### ผลตรวจและขอบเขตหลักฐาน

- ก่อนแก้ชั้นสีเข้ม auth รอบสุดท้าย: Herd PHP 8.4.26, Pint สำหรับ PHP ที่เปลี่ยนผ่าน, PHPStan ทั้งแอป 0 errors, `php artisan test` ผ่าน 50/50 tests (213 assertions), `view:cache` และ `git diff --check` ผ่าน หลังแก้ชั้นสีเข้ม: `UiStatesTest` ผ่าน 7/7 tests (26 assertions) และ `view:cache` ผ่าน
- หลักฐานหลักอยู่ใน `.impeccable/review/`: ชุดที่ reviewer ตรวจเดิม 25 ภาพ ครอบคลุม login/register, รายการ/รายละเอียดกิจกรรม, หน้าว่าง, ฟอร์ม, 403/404 และหมวดผู้ดูแล ทั้ง desktop/mobile รวมภาพกว้าง 1920px; ตรวจรูปที่มองเห็นโหลดครบ ไม่มีหน้าล้นแนวนอน และตัวกรองค้นหา/แสดงซ่อนรหัสผ่านทำงาน ภาพที่จับแยกด้านบน/ล่างใช้พิกเซล viewport จริงโดยไม่ปรับสเกลหรือเติมพื้นที่ ส่วนดิบของชุดรีวิวเก็บใน `capture-parts/`
- เพิ่ม `user-1003.png` หลังการรีวิว เป็นหลักฐานจาก Codex preview ปัจจุบันที่ viewport 1003×884: เห็นเพื่อนครบ 4 คนและ layout เรียงภาพเหนือฟอร์ม ภาพนี้เป็นหลักฐานเพิ่มเติม จึงรวมภาพหลักปัจจุบัน 26 ไฟล์ ไม่ใช่ส่วนหนึ่งของรีวิวเต็ม 25 ภาพก่อนหน้า รายละเอียดอยู่ใน `capture-checks.json` และ `asset-and-capture-checks.json`
- หน้า login/register ตรวจจากแอปจริงบน Herd; หน้า private ตรวจ controller/view จริงด้วย fixture ใน SQLite `:memory:` ผ่าน router ชั่วคราวบน loopback ที่รับเฉพาะ GET ไม่ได้ตรวจ session สมาชิกจริงหรือส่งฟอร์มสร้างบัญชีจริง router และ server ชั่วคราวถูกนำออกแล้ว เปิด preview `http://unimate.test/login` ไว้
- `.impeccable/review/finish-review.md` บันทึก `disposition: ship` สำหรับรายการแก้ contrast ของคำอธิบาย auth: reviewer ตรวจภาพ desktop/register/1920 ที่จับใหม่แล้ว ยอมรับชั้นสีเข้มส่วนบนตามเกณฑ์ 4.5:1 ใบหน้าครบและไม่พบ regression จากการแก้นี้ ผลตรวจเต็มก่อนหน้าไม่มีรายการแก้อื่นค้าง การยอมรับรอบสุดท้ายอยู่ในขอบเขตรายการแก้นี้ ไม่ใช่การอนุมัติใหม่ทั้ง repository

ข้อจำกัดของต้นทางยังคงอยู่: B1 มีแสงฟุ้งรอบกลุ่ม จึงเลือกแสดงภาพเต็มบนพื้นขาว; P2 ยังมีเครื่องหมายขาวเล็กคล้ายแบรนด์บนเสื้อผ้าและจุดเล็กบนรองเท้า/ถุงเท้า การจัดวางและลดขนาดไม่ได้แก้เนื้อหาส่วนนี้ สถานะ “มีปัญหา” ของสองภาพและข้อสังเกตเก่ายังคงเดิม ไม่มีการนำข้อบกพร่องมาเป็นกฎของระบบออกแบบ

งานรอบนี้ไม่ติดตั้ง ไม่ commit/push และเพิ่มบันทึกเฉพาะท้ายเอกสารโดยรักษาประวัติเดิม

## ปรับหลังตรวจโดย Claude (2026-10-09)

- **ย้ายไฟล์ต้นฉบับ:** PNG ต้นฉบับ ภาพช่วยตรวจ และ metadata (`manifest.json`, `*.webp.json`) ย้ายจาก `public/images/` ไปที่ `storage/app/image-sources/` โดยคงโครงสร้างโฟลเดอร์เดิม โฟลเดอร์นี้ไม่เข้า git และไม่ถูกเสิร์ฟบนเว็บ ทำให้ `public/images/` เหลือ 2.3MB จาก 40MB ลิงก์ไปยังไฟล์ต้นฉบับในเอกสารนี้จึงชี้ไปที่ตำแหน่งเดิมก่อนย้าย
- **การ์ดกิจกรรม:** เลิกใช้ภาพถ่ายเป็นปกการ์ด เพราะภาพเดียวต่อหมวดทำให้ภาพซ้ำกันทั้งหน้า (ภาพตีแบด 3 ใบ) และไม่ตรงเรื่อง (กิจกรรมวิ่งและบาสได้ภาพตีแบด) จึงเปลี่ยนเป็นแถบสีประจำหมวดกับไอคอน C1–C6 (`x-ui.category-art`) ไอคอนส่งออกใหม่แบบครอปชิดวัตถุที่ 96 และ 192px
- **หน้ารายละเอียด:** ยังใช้ภาพถ่าย P2–P7 แต่ป้ายเปลี่ยนเป็น "ภาพประกอบหมวด…" หมวดที่ไม่มีภาพของตัวเอง (หมวดที่ผู้ดูแลเพิ่มใหม่) ใช้แถบไอคอนแทนภาพบอร์ดเกม
- **ไอคอนในป้ายหมวดหมู่ขนาด 20–24px:** เอาออก เพราะเบลอ และไอคอนกีฬาดูเหมือนแว่นขยายค้นหา
- **หน้าจัดการหมวด:** ไอคอนแสดงจริงแค่ 24px เพราะคลาสขนาดใน component ชนกัน แก้เป็นไอคอน 40px ในช่องสีประจำหมวด
- **หน้าสร้างโพสต์บนมือถือ:** ภาพ B1 ไปอยู่ใต้ปุ่มส่ง จึงแสดงเฉพาะจอใหญ่
