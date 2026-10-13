<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use App\Models\UserReview;
use App\Notifications\ActivityNotification;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ข้อมูลสำหรับนำเสนอ ครอบคลุมทุกระบบ (บัญชี, โพสต์/ค้นหา, คำขอเข้าร่วม, เช็กชื่อ/แจ้งเตือน, รีวิว/รายงาน, ภาพรวมข้อมูล)
 * ใช้กับฐานข้อมูลว่างเท่านั้น รันคำสั่งเดียว (ลบข้อมูลเดิมทั้งหมด):
 *   php artisan migrate:fresh --seed --seeder=PresentationDemoSeeder
 *
 * เวลาทุกอย่างอิงกับเวลาที่รัน จึงควรรันใหม่ในวันนำเสนอ (กิจกรรม "กำลังจัด" มีอายุแค่ 2 ชั่วโมงหลังรัน)
 * ทุกบัญชีใช้รหัสผ่าน password ดูบทเดโมที่ docs/demo-guide-th.md
 */
class PresentationDemoSeeder extends Seeder
{
    private CarbonImmutable $now;

    /** @var array<string, int> ชื่อหมวด => id */
    private array $categories = [];

    public function run(): void
    {
        $this->call([UserSeeder::class, CategorySeeder::class]);
        // ประวัติย้อนหลัง 9 เดือนสำหรับหน้าภาพรวมข้อมูล (ผู้ใช้ insights.*) และเติมกิจกรรมในหน้ารายการ
        $this->call(AdminInsightsDemoSeeder::class);

        $this->now = CarbonImmutable::now()->startOfMinute();
        $this->categories = Category::pluck('id', 'name')->all();

        DB::transaction(fn () => $this->story());

        $this->command?->info('PresentationDemoSeeder: ข้อมูลนำเสนอพร้อมแล้ว (ทุกบัญชีใช้รหัสผ่าน password)');
    }

    private function story(): void
    {
        // ─────────── บัญชี (student@ / suspended@ / admin@ มาจาก UserSeeder) ───────────
        $narin = User::where('email', 'student@unimate.ac.th')->firstOrFail();
        $ploy = User::where('email', 'suspended@unimate.ac.th')->firstOrFail();
        $admin = User::where('email', 'admin@unimate.ac.th')->firstOrFail();
        $this->backdate($narin, 120);
        $this->backdate($ploy, 100);
        $this->backdate($admin, 200);

        $mind = $this->user('host@unimate.ac.th', 'มายด์ ศิริพร', '653020010-0', 'ชอบจัดกิจกรรมวิ่งและปั่นจักรยาน ชวนเพื่อนออกกำลังกายทุกสัปดาห์', 150);
        $phum = $this->user('friend@unimate.ac.th', 'ภูมิ ทองดี', '653020011-1', 'เด็กวิศวะคอม ชอบแบดมินตันกับบอร์ดเกม', 110);
        $praew = $this->user('newbie@unimate.ac.th', 'แพรว กมลชนก', '673020012-2', 'ปี 1 เพิ่งย้ายมาขอนแก่น อยากหาเพื่อนทำกิจกรรมด้วยกัน', 12);
        $fah = $this->user('fah@unimate.ac.th', 'ฟ้า รัตนา', '653020013-3', 'ชมรมดนตรี เล่นกีตาร์โปร่งกับอูคูเลเล่', 140);
        $ton = $this->user('ton@unimate.ac.th', 'ต้น ศักดิ์ดา', '653020014-4', 'ชอบเที่ยวธรรมชาติและถ่ายรูปฟิล์ม', 90);
        $june = $this->user('june@unimate.ac.th', 'จูน พิมพ์ชนก', '643020015-5', 'ติวเตอร์คณิตศาสตร์ ยินดีช่วยติวทุกวิชาตัวเลข', 160);
        $oat = $this->user('oat@unimate.ac.th', 'โอ๊ต ร.', '663020016-6', null, 45);

        // ─────────── นรินทร์เป็นผู้จัด ───────────

        // คำขอรออนุมัติ 3 คน: เดโมอนุมัติ / ปฏิเสธ
        $badminton = $this->activity($narin, 'กีฬา', 'แบดมินตันหลังเลิกเรียน มือใหม่ยินดีต้อนรับ',
            "ตีแบดสบาย ๆ หลังเลิกเรียน มีลูกขนไก่ให้ ใครมีไม้ถือมาเองได้เลย ไม่มีก็ยืมได้\nเน้นสนุก ไม่ต้องเก่งก็มาได้",
            'ศูนย์กีฬาในร่ม มข.', 3, '17:00', 120, 8, maps: 'ศูนย์กีฬา มหาวิทยาลัยขอนแก่น');
        $this->join($badminton, $phum, 'approved', 'ไปด้วยครับ เดี๋ยวเอาไม้ไปเผื่อ');
        $this->join($badminton, $fah, 'approved');
        $this->join($badminton, $praew, 'pending', 'เพิ่งหัดเล่นค่ะ ขอไปด้วยนะคะ', hoursAgo: 1);
        $this->join($badminton, $ton, 'pending', 'ว่างพอดีครับ', hoursAgo: 3);
        $this->join($badminton, $june, 'pending', null, hoursAgo: 5);
        $this->join($badminton, $oat, 'rejected', 'ขอเล่นด้วย');

        // จบเมื่อวานแต่ยังไม่เช็กชื่อ: เดโมเช็กชื่อ มา/ขาด แล้วผู้ที่มาจึงรีวิวได้
        $calculus = $this->activity($narin, 'ติวหนังสือ', 'ติวแคลคูลัส 1 ก่อนสอบมิดเทอม',
            'ทบทวนลิมิต อนุพันธ์ และโจทย์ข้อสอบเก่า เอาเครื่องคิดเลขกับสรุปของตัวเองมาด้วย',
            'หอสมุดกลาง มข. ชั้น 3', -1, '18:00', 180, 6, maps: 'สำนักหอสมุด มหาวิทยาลัยขอนแก่น');
        foreach ([$phum, $praew, $june, $fah] as $member) {
            $this->join($calculus, $member, 'approved');
        }

        // จบแล้วพร้อมรีวิว: คะแนนผู้จัดในหน้าโปรไฟล์ของนรินทร์
        $basketball = $this->activity($narin, 'กีฬา', 'บาสเย็นวันพุธ ขาดอีก 3',
            'เล่นบาสเต็มสนาม แบ่งทีมกันหน้างาน', 'สนามบาสหน้าหอพัก 5', -16, '17:30', 120, 10);
        foreach ([$phum, $ton, $fah, $june] as $member) {
            $this->join($basketball, $member, 'approved', attendance: 'present');
        }
        $this->join($basketball, $oat, 'approved', attendance: 'absent');
        $this->review($basketball, $phum, 5, 'นัดตรงเวลา เล่นสนุกมาก ไว้มาอีกแน่นอน');
        $this->review($basketball, $ton, 4, 'สนุกดีครับ สนามคนเยอะไปนิด');
        $this->review($basketball, $fah, 5);

        $python = $this->activity($narin, 'ติวหนังสือ', 'ติวโปรแกรมมิง Python พื้นฐาน',
            'สอนตั้งแต่ติดตั้งโปรแกรมจนเขียน loop ได้ เอาโน้ตบุ๊กมาด้วย', 'Co-working คณะวิทยาศาสตร์', -30, '19:00', 120, 5);
        $this->join($python, $praew, 'approved', attendance: 'present');
        $this->join($python, $june, 'approved', attendance: 'present');
        $this->review($python, $praew, 5, 'อธิบายเข้าใจง่ายมาก ขอบคุณนะคะ');
        $this->review($python, $june, 4);

        // ─────────── นรินทร์เป็นผู้เข้าร่วม ───────────

        // ได้รับอนุมัติแล้ว (ถอนตัวได้) และผู้จัดเพิ่งเปลี่ยนเวลา
        $run = $this->activity($mind, 'กีฬา', 'วิ่งรอบบึงสีฐานยามเย็น 5 กม.',
            "วิ่งเบา ๆ รอบบึง เพซ 7-8 ไม่ทิ้งกัน\nเลื่อนเวลาเป็น 17:30 เพราะแดดร้อน", 'บึงสีฐาน มข.', 2, '17:30', 90, 15, maps: 'บึงสีฐาน มหาวิทยาลัยขอนแก่น');
        $this->join($run, $narin, 'approved', 'ขอไปด้วยครับ');
        $this->join($run, $phum, 'approved');
        $this->join($run, $ton, 'pending', 'มือใหม่ครับ วิ่งช้าหน่อย', hoursAgo: 6);

        // รออนุมัติ (ถอนคำขอได้)
        $bike = $this->activity($mind, 'ท่องเที่ยว', 'ปั่นจักรยานชมเมืองขอนแก่น',
            'ปั่นจากมอไปบึงแก่นนคร แวะกินข้าวเช้า ระยะทางรวมประมาณ 20 กม. ใส่หมวกกันน็อกด้วยนะ',
            'จุดนัดหน้าประตูมอ (ประตู 1)', 5, '07:00', 240, 10, maps: 'บึงแก่นนคร ขอนแก่น');
        $this->join($bike, $narin, 'pending', 'มีจักรยานของตัวเองครับ', hoursAgo: 20);
        $this->join($bike, $fah, 'approved');

        // เต็มแล้ว และนรินทร์ไม่ได้รับอนุมัติ
        $toeic = $this->activity($mind, 'ติวหนังสือ', 'ติว TOEIC กลุ่มเล็ก รับแค่ 4 คน',
            'ทำข้อสอบ Part 5-7 แล้วเฉลยด้วยกัน กลุ่มเล็กจะได้ถามได้เต็มที่', 'ร้านกาแฟหน้ามอ', 4, '13:00', 150, 4);
        foreach ([$phum, $fah, $june, $ton] as $member) {
            $this->join($toeic, $member, 'approved');
        }
        $this->join($toeic, $narin, 'rejected', 'อยากได้คะแนน 700 ครับ');

        // จบแล้ว มาจริง แต่ยังไม่รีวิว: ปุ่ม "ให้คะแนน" ในหน้านัดของฉัน
        $hike = $this->activity($mind, 'ท่องเที่ยว', 'เดินป่าน้ำตกตาดฟ้า วันเดียวจบ',
            'ออกเช้า เดินป่าระยะสั้นไปน้ำตก กลับถึงมอก่อนมืด แชร์ค่ารถคนละ 150 บาท',
            'อุทยานแห่งชาติภูผาม่าน', -3, '07:00', 540, 8, maps: 'อุทยานแห่งชาติภูผาม่าน');
        foreach ([$narin, $phum, $ton, $praew] as $member) {
            $this->join($hike, $member, 'approved', attendance: 'present');
        }
        $this->join($hike, $fah, 'approved', attendance: 'absent');
        $this->review($hike, $phum, 5, 'วิวสวยมาก ผู้จัดดูแลทุกคนดีมาก');
        $this->review($hike, $ton, 5, 'ได้รูปสวยเยอะเลย ขอบคุณครับ');
        $this->review($hike, $praew, 4, 'เหนื่อยแต่คุ้มค่ะ ได้เพื่อนใหม่ด้วย');

        // รีวิวไปแล้ว (แก้ไข/ลบได้) และมีรีวิวคำหยาบที่ถูกรายงานอยู่
        $jam = $this->activity($fah, 'ดนตรี', 'แจมกีตาร์โปร่งใต้ตึกเรียนรวม',
            'เอากีตาร์มาเล่นเพลงที่ชอบด้วยกัน ใครไม่มีเครื่องดนตรีมาร้องได้', 'ลานใต้ตึกเรียนรวม 1', -9, '18:30', 120, 8);
        foreach ([$narin, $phum, $oat, $june] as $member) {
            $this->join($jam, $member, 'approved', attendance: 'present');
        }
        $this->review($jam, $narin, 4, 'บรรยากาศชิลมาก แต่เสียงรถดังไปหน่อย');
        $this->review($jam, $phum, 5, 'ได้เล่นเพลงที่ชอบ สนุกมากครับ');
        $rudeReview = $this->review($jam, $oat, 1, 'จัดห่วยมาก เสียเวลา คนจัดเล่นไม่เป็นแล้วยังมาชวนคนอื่น');

        // ถูกเช็กว่าขาด: รีวิวไม่ได้
        $stats = $this->activity($june, 'ติวหนังสือ', 'ติวสถิติ บทที่ 4 การทดสอบสมมติฐาน',
            'ทำโจทย์ t-test และ chi-square จากชีทอาจารย์', 'ห้องอ่านหนังสือ 24 ชม.', -6, '19:00', 120, 6);
        $this->join($stats, $narin, 'approved', attendance: 'absent');
        $this->join($stats, $praew, 'approved', attendance: 'present');
        $this->review($stats, $praew, 5, 'พี่จูนสอนเก่งมากค่ะ');

        // ผู้จัดยกเลิก
        $volunteer = $this->activity($fah, 'จิตอาสา', 'สอนน้องอ่านหนังสือ โรงเรียนบ้านหนองแวง',
            'ช่วยน้อง ป.2-ป.3 ฝึกอ่าน มีชั่วโมงจิตอาสาให้', 'โรงเรียนบ้านหนองแวง', 6, '09:00', 180, 10);
        $this->join($volunteer, $narin, 'approved');
        $this->join($volunteer, $praew, 'pending');
        $volunteer->status = 'cancelled';
        $volunteer->save();

        // ─────────── กิจกรรมอื่นในหน้ารายการ ───────────

        // กำลังจัดอยู่ตอนนี้ (สถานะ "กำลังจัด")
        $acoustic = $this->activity($ton, 'ดนตรี', 'ซ้อมวงอะคูสติกงานลอยกระทงคณะ',
            'ซ้อมเพลงสำหรับเวทีคณะ ขาดคนเล่นคาฮอง', 'ห้องซ้อมดนตรี ชมรมดนตรีสากล', 0, null, 180, 6, startsAt: $this->now->subHour());
        $this->join($acoustic, $phum, 'approved');
        $this->join($acoustic, $fah, 'approved');

        $this->activity($june, 'ติวหนังสือ', 'อ่านหนังสือเตรียมสอบไฟนอลด้วยกัน',
            'นั่งอ่านเงียบ ๆ ด้วยกันแบบ Pomodoro พักทุก 25 นาที', 'หอสมุดกลาง มข. ชั้น 2', 1, '19:00', 180, 12);
        $karaoke = $this->activity($fah, 'ดนตรี', 'ร้องเพลงคาราโอเกะคลายเครียด',
            'หารค่าห้องกัน ร้องได้ทุกแนว', 'ห้องคาราโอเกะหน้าหอพัก', 2, '19:30', 120, 8);
        $this->join($karaoke, $praew, 'approved');
        $this->activity($ton, 'อื่น ๆ', 'ถ่ายรูปฟิล์มรอบมอ',
            'เดินถ่ายรูปจุดสวย ๆ ในมอ มีกล้องฟิล์มให้ยืม 2 ตัว', 'ลานหน้าหอศิลป์', 8, '16:00', 150, 6);
        $this->activity($mind, 'จิตอาสา', 'เก็บขยะรอบบึงสีฐาน',
            'มีถุงมือและถุงขยะให้ ได้ชั่วโมงจิตอาสา', 'บึงสีฐาน มข.', 9, '08:00', 180, 20, maps: 'บึงสีฐาน มหาวิทยาลัยขอนแก่น');

        // ─────────── รีวิวผู้ใช้ (รีวิวคน: ผู้จัด↔ผู้เข้าร่วม และผู้เข้าร่วม↔ผู้เข้าร่วม) ───────────

        // บาสวันพุธ: ผู้เข้าร่วมรีวิวนรินทร์แล้ว แต่นรินทร์ (ผู้จัด) ยังไม่รีวิวใคร → งานค้าง 4 คน ให้กดสด
        $this->userReview($basketball, $phum, $narin, 5, 'นัดตรงเวลา จัดทีมยุติธรรม');
        $this->userReview($basketball, $ton, $narin, 4);
        $this->userReview($basketball, $fah, $narin, 5, 'ผู้จัดใจดี ดูแลทุกคน');
        $this->userReview($basketball, $phum, $ton, 5, 'ส่งบอลดี เล่นเป็นทีม');
        $this->userReview($basketball, $june, $phum, 4);

        $this->userReview($python, $narin, $praew, 5, 'ตั้งใจเรียน ถามเก่ง');
        $this->userReview($python, $narin, $june, 4, 'มาตรงเวลา ช่วยเพื่อนดีบักด้วย');
        $this->userReview($python, $praew, $narin, 5, 'สอนใจเย็นมากค่ะ');
        $this->userReview($python, $june, $narin, 5);
        $this->userReview($python, $praew, $june, 5, 'พี่จูนช่วยอธิบายเพิ่มให้ เข้าใจขึ้นเยอะ');

        // เดินป่า: คนอื่นรีวิวนรินทร์แล้ว แต่นรินทร์ (ผู้เข้าร่วม) ยังไม่รีวิวใคร → งานค้าง 4 คน ให้กดสด
        $this->userReview($hike, $mind, $phum, 5, 'ช่วยดูแลน้องใหม่ตลอดทาง');
        $this->userReview($hike, $mind, $ton, 5);
        $this->userReview($hike, $mind, $praew, 4, 'เดินช้าหน่อยแต่ไม่บ่นเลย สู้มาก');
        $this->userReview($hike, $mind, $narin, 5, 'มาตรงเวลา ช่วยถือของให้เพื่อน');
        $this->userReview($hike, $phum, $mind, 5, 'ผู้จัดเตรียมตัวดีมาก มีแผนสำรองด้วย');
        $this->userReview($hike, $ton, $mind, 5);
        $this->userReview($hike, $praew, $mind, 5, 'พี่มายด์ใจดีมากค่ะ');
        $this->userReview($hike, $phum, $narin, 5, 'คุยสนุก เดินด้วยกันทั้งวัน');
        $this->userReview($hike, $ton, $praew, 4);

        // แจมกีตาร์: นรินทร์รีวิวครบแล้ว (แก้ไข/ลบได้) และมีรีวิวคำหยาบของโอ๊ตถึงภูมิ
        $this->userReview($jam, $narin, $fah, 4, 'เลือกเพลงดี บรรยากาศชิล');
        $this->userReview($jam, $narin, $phum, 5, 'เล่นกีตาร์เพราะมาก');
        $this->userReview($jam, $narin, $june, 5);
        $this->userReview($jam, $narin, $oat, 3, 'มาสาย แต่ร้องเพลงสนุก');
        $this->userReview($jam, $fah, $narin, 5, 'ร้องเพลงเพราะ ชวนคนอื่นร้องด้วย');
        $this->userReview($jam, $fah, $phum, 5);
        $this->userReview($jam, $fah, $oat, 2, 'มาสายครึ่งชั่วโมง ไม่บอกล่วงหน้า');
        $this->userReview($jam, $phum, $fah, 5, 'จัดสนุก ไว้มาอีกแน่นอน');
        $this->userReview($jam, $june, $fah, 4);
        $rudeUserReview = $this->userReview($jam, $oat, $phum, 1, 'เล่นกีตาร์ห่วยแตก หนวกหูชะมัด อย่ามาให้เห็นหน้าอีก');

        $this->userReview($stats, $praew, $june, 5, 'อธิบายเป็นขั้นตอน เข้าใจง่าย');
        $this->userReview($stats, $june, $praew, 5, 'ทำโจทย์ครบทุกข้อ ตั้งใจมาก');

        // ─────────── รายงาน (รอตรวจ / ดำเนินการแล้ว / ยกรายงาน) ───────────

        $spam = $this->activity($oat, 'อื่น ๆ', 'รับทำการบ้าน รายงาน ทุกวิชา ราคาถูก',
            'ทักไลน์มาคุยราคาได้เลย รับงานด่วน', 'ออนไลน์', 7, '20:00', 120, 50);
        $this->report($phum, 'activity', $spam->id, 'โพสต์ขายบริการทำการบ้าน ผิดวัตถุประสงค์ของเว็บ', hoursAgo: 4);
        $this->report($praew, 'activity', $spam->id, 'เป็นโพสต์โฆษณา ไม่ใช่กิจกรรมค่ะ', hoursAgo: 2);
        $this->report($praew, 'user', $oat->id, 'ไม่มาตามนัด แล้วยังทักแชทมาพูดจาไม่สุภาพ', hoursAgo: 8);
        $this->report($phum, 'review', $rudeReview->id, 'รีวิวใช้คำไม่สุภาพและดูถูกผู้จัด', hoursAgo: 1);
        $this->report($phum, 'user_review', $rudeUserReview->id, 'รีวิวถึงผมใช้คำหยาบและดูถูกกัน', hoursAgo: 2);

        // ซ่อนไปแล้ว: เดโมเลิกซ่อน
        $sheet = $this->activity($oat, 'อื่น ๆ', 'ขายชีทสรุปข้อสอบเก่า ทักไลน์',
            'มีชีททุกวิชา ราคาพิเศษ', 'ออนไลน์', 10, '20:00', 60, 30);
        $hideReport = $this->report($june, 'activity', $sheet->id, 'ขายของ ไม่ใช่กิจกรรม', hoursAgo: 60);
        $sheet->hidden_at = $this->now->subHours(48);
        $sheet->hidden_reason = 'โพสต์ขายของ ผิดวัตถุประสงค์ของเว็บ';
        $sheet->save();
        $this->close($hideReport, $admin, 'actioned', 'โพสต์ขายของ ผิดวัตถุประสงค์ของเว็บ', 'hide_activity', 48);

        // ระงับบัญชีพลอยจากรายงาน
        $ployReport = $this->report($mind, 'user', $ploy->id, 'ไม่มาตามนัด 3 ครั้งติด และไม่ตอบแชท', hoursAgo: 200);
        $this->close($ployReport, $admin, 'actioned', 'ไม่มาตามนัดซ้ำหลายครั้ง ระงับบัญชีชั่วคราว', 'suspend_user', 190);

        // ยกรายงาน: ตรวจแล้วไม่ผิด
        $dismissed = $this->report($oat, 'activity', $basketball->id, 'ไม่ให้ผมเล่นด้วย', hoursAgo: 300);
        $this->close($dismissed, $admin, 'dismissed', 'ตรวจแล้วผู้รายงานขาดนัดเอง ไม่พบการกระทำผิดของผู้จัด', 'dismiss', 290);

        // ─────────── การแจ้งเตือน ───────────

        $this->notify($narin, "แพรว กมลชนก ขอเข้าร่วมกิจกรรม '{$badminton->title}'", route('activities.requests', $badminton, false), 'request', 1);
        $this->notify($narin, "ต้น ศักดิ์ดา ขอเข้าร่วมกิจกรรม '{$badminton->title}'", route('activities.requests', $badminton, false), 'request', 3);
        $this->notify($narin, "จูน พิมพ์ชนก ขอเข้าร่วมกิจกรรม '{$badminton->title}'", route('activities.requests', $badminton, false), 'request', 5);
        $this->notify($narin, "กิจกรรม '{$volunteer->title}' ถูกยกเลิกโดยผู้จัด", route('activities.show', $volunteer, false), 'danger', 10);
        $this->notify($narin, "กิจกรรม '{$run->title}' มีการเปลี่ยนวันเวลาหรือสถานที่ กรุณาตรวจสอบรายละเอียดอีกครั้ง", route('activities.show', $run, false), 'warning', 22);
        $this->notify($narin, "คำขอเข้าร่วมกิจกรรม '{$toeic->title}' ของคุณไม่ได้รับการอนุมัติ", route('activities.show', $toeic, false), 'danger', 48, read: true);
        $this->notify($narin, "คำขอเข้าร่วมกิจกรรม '{$run->title}' ของคุณได้รับการอนุมัติแล้ว!", route('activities.show', $run, false), 'success', 70, read: true);
        $this->notify($narin, "คำขอเข้าร่วมกิจกรรม '{$hike->title}' ของคุณได้รับการอนุมัติแล้ว!", route('activities.show', $hike, false), 'success', 150, read: true);
        $this->notify($narin, "มีรีวิวใหม่ 5 ดาว ในกิจกรรม '{$basketball->title}'", route('activities.show', $basketball, false).'#reviews', 'review', 360, read: true);
        $this->notify($narin, "ภูมิ ทองดี ให้คะแนนคุณ 5 ดาว จากกิจกรรม '{$hike->title}'", route('users.show', ['user' => $narin, 'tab' => 'participant'], false).'#received-reviews', 'review', 30, read: true);
        $this->notify($phum, "โอ๊ต ร. ให้คะแนนคุณ 1 ดาว จากกิจกรรม '{$jam->title}'", route('users.show', ['user' => $phum, 'tab' => 'participant'], false).'#received-reviews', 'review', 150);

        $this->notify($mind, "ต้น ศักดิ์ดา ขอเข้าร่วมกิจกรรม '{$run->title}'", route('activities.requests', $run, false), 'request', 6);
        $this->notify($mind, "Narin K. (นรินทร์) ขอเข้าร่วมกิจกรรม '{$bike->title}'", route('activities.requests', $bike, false), 'request', 20);
        $this->notify($mind, "มีรีวิวใหม่ 5 ดาว ในกิจกรรม '{$hike->title}'", route('activities.show', $hike, false).'#reviews', 'review', 40);
        $this->notify($fah, "มีรีวิวใหม่ 1 ดาว ในกิจกรรม '{$jam->title}'", route('activities.show', $jam, false).'#reviews', 'review', 200);
        $this->notify($oat, "คำขอเข้าร่วมกิจกรรม '{$badminton->title}' ของคุณไม่ได้รับการอนุมัติ", route('activities.show', $badminton, false), 'danger', 30);
        $this->notify($oat, "กิจกรรม '{$sheet->title}' ของคุณถูกซ่อนโดยผู้ดูแลระบบ: โพสต์ขายของ ผิดวัตถุประสงค์ของเว็บ", route('activities.show', $sheet, false), 'danger', 48);
        $this->notify($june, 'ผู้ดูแลระบบดำเนินการกับรายงานของคุณแล้ว: โพสต์ขายของ ผิดวัตถุประสงค์ของเว็บ', null, 'info', 48, read: true);
    }

    // ───────────────────────── Helpers ─────────────────────────

    private function user(string $email, string $name, string $studentId, ?string $bio, int $daysAgo): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'student_id' => $studentId,
            'password' => Hash::make('password'),
            'role' => 'student',
            'status' => 'active',
            'bio' => $bio,
        ]);
        $this->backdate($user, $daysAgo);

        return $user;
    }

    private function backdate(User $user, int $daysAgo): void
    {
        $user->created_at = $this->now->subDays($daysAgo)->setTime(10, 0);
        $user->updated_at = $user->created_at;
        $user->save();
    }

    /** $day = จำนวนวันนับจากวันนี้ (ติดลบ = ผ่านมาแล้ว) $time = เวลาเริ่ม หรือส่ง $startsAt ตรง ๆ */
    private function activity(User $host, string $category, string $title, string $description, string $location,
        int $day, ?string $time, int $minutes, int $capacity, ?string $maps = null, ?CarbonImmutable $startsAt = null): Activity
    {
        $starts = $startsAt ?? $this->now->addDays($day)->setTimeFromTimeString((string) $time);
        $activity = new Activity([
            'category_id' => $this->categories[$category],
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'google_maps_url' => $maps ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($maps) : null,
            'starts_at' => $starts,
            'ends_at' => $starts->addMinutes($minutes),
            'capacity' => $capacity,
        ]);
        $activity->user()->associate($host);
        $activity->status = 'published';
        // โพสต์ล่วงหน้า 5 วัน แต่ไม่เกินตอนนี้
        $posted = $starts->subDays(5)->min($this->now->subHours(30));
        $activity->created_at = $posted;
        $activity->updated_at = $posted;
        $activity->save();

        return $activity;
    }

    /** @param 'present'|'absent'|null $attendance */
    private function join(Activity $activity, User $user, string $status, ?string $message = null,
        ?string $attendance = null, ?int $hoursAgo = null): ActivityParticipant
    {
        $p = new ActivityParticipant(['message' => $message]);
        $p->activity()->associate($activity);
        $p->user()->associate($user);
        $p->status = $status;
        $p->attendance = $attendance;
        $at = $hoursAgo !== null
            ? $this->now->subHours($hoursAgo)
            : CarbonImmutable::parse($activity->created_at)->addHours(6);
        $p->created_at = $at;
        $p->updated_at = $at;
        $p->save();

        return $p;
    }

    private function review(Activity $activity, User $user, int $rating, ?string $comment = null): Review
    {
        $review = new Review(['rating' => $rating, 'comment' => $comment]);
        $review->activity()->associate($activity);
        $review->user()->associate($user);
        $at = CarbonImmutable::parse($activity->ends_at)->addHours(3)->min($this->now);
        $review->created_at = $at;
        $review->updated_at = $at;
        $review->save();

        return $review;
    }

    /** รีวิวคน บทบาทของผู้ถูกรีวิวดูจากว่าเป็นผู้จัดกิจกรรมหรือไม่ (แบบเดียวกับ UserReviewController) */
    private function userReview(Activity $activity, User $reviewer, User $reviewee, int $rating, ?string $comment = null): UserReview
    {
        $review = new UserReview(['rating' => $rating, 'comment' => $comment]);
        $review->activity()->associate($activity);
        $review->reviewer()->associate($reviewer);
        $review->reviewee()->associate($reviewee);
        $review->reviewee_role = $reviewee->id === $activity->user_id ? 'host' : 'participant';
        $at = CarbonImmutable::parse($activity->ends_at)->addHours(4)->min($this->now);
        $review->created_at = $at;
        $review->updated_at = $at;
        $review->save();

        return $review;
    }

    private function report(User $reporter, string $type, int $targetId, string $reason, int $hoursAgo): Report
    {
        $report = new Report(['target_type' => $type, 'target_id' => $targetId, 'reason' => $reason]);
        $report->reporter()->associate($reporter);
        $report->created_at = $this->now->subHours($hoursAgo);
        $report->updated_at = $report->created_at;
        $report->save();

        return $report;
    }

    /** ปิดรายงานพร้อมบันทึกประวัติ (ไม่ส่งแจ้งเตือนจริง เพราะสร้างแจ้งเตือนที่ต้องการไว้แยกแล้ว) */
    private function close(Report $report, User $admin, string $status, string $note, string $action, int $hoursAgo): void
    {
        $at = $this->now->subHours($hoursAgo);
        $report->status = $status;
        $report->admin_note = $note;
        $report->handler()->associate($admin);
        $report->handled_at = $at;
        $report->updated_at = $at;
        $report->save();

        $log = ModerationLog::create([
            'admin_id' => $admin->id,
            'report_id' => $report->id,
            'action' => $action,
            'target_type' => $report->target_type,
            'target_id' => $report->target_id,
            'reason' => $note,
        ]);
        $log->created_at = $at;
        $log->updated_at = $at;
        $log->save();
    }

    private function notify(User $user, string $message, ?string $url, string $kind, int $hoursAgo, bool $read = false): void
    {
        $at = $this->now->subHours($hoursAgo);
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => ActivityNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => $message, 'url' => $url, 'kind' => $kind], JSON_UNESCAPED_UNICODE),
            'read_at' => $read ? $at->addMinutes(30) : null,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }
}
