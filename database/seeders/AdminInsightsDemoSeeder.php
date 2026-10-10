<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * ข้อมูลตัวอย่างสำหรับหน้า "ภาพรวมข้อมูล" (/admin/insights) ไม่อยู่ใน DatabaseSeeder ต้องรันเอง:
 *   php artisan db:seed --class=AdminInsightsDemoSeeder   (รัน UserSeeder และ CategorySeeder ก่อน)
 *
 * ผู้ใช้ demo ใช้อีเมล insights.NN@unimate.ac.th รันซ้ำได้: ลบเฉพาะข้อมูลที่ seeder นี้สร้าง แล้วสร้างใหม่
 * ใช้ mt_srand ค่าคงที่ จึงได้ข้อมูลชุดเดิมทุกครั้ง (เวลาอิงกับวันที่รัน)
 */
class AdminInsightsDemoSeeder extends Seeder
{
    private const EMAIL_PATTERN = 'insights.%@unimate.ac.th';

    /** จำนวนผู้ใช้ demo (ชื่อเล่นวนซ้ำได้ แต่อักษรย่อนามสกุลไม่ซ้ำกัน) */
    private const USER_COUNT = 90;

    private const NICKNAMES = [
        'ฟ้าใส', 'ต้นกล้า', 'มายด์', 'บอส', 'แพรวา', 'ภูมิ', 'ข้าวหอม', 'ปั้น', 'น้ำหวาน', 'กาย',
        'ใบเตย', 'ต้นน้ำ', 'มะปราง', 'ไผ่', 'เฟิร์น', 'ก้อง', 'ปลายฝน', 'ธาม', 'น้ำฝน', 'เจได',
        'แก้ม', 'โอ๊ต', 'ขวัญ', 'ภีม', 'เมย์', 'ณัฐ', 'ส้มโอ', 'เต้', 'อิงฟ้า', 'ซัน',
        'ปุยฝ้าย', 'ไอซ์', 'กิ่งแก้ว', 'ภูผา', 'มุก', 'ตะวัน', 'จูน', 'พีท', 'ดาว', 'วิว',
    ];

    private const INITIALS = ['ก', 'จ', 'ช', 'ด', 'ต', 'ท', 'น', 'บ', 'ป', 'พ', 'ม', 'ร', 'ว', 'ศ', 'ส', 'อ'];

    /** หมวด => [น้ำหนักการสุ่ม, ชื่อกิจกรรม, สถานที่, น้ำหนักคะแนน 1-5] */
    private const CATEGORIES = [
        'กีฬา' => [30, ['แบดมินตันหลังเลิกเรียน', 'บาสเย็นนี้ขาดอีก 3', 'ฟุตซอลรวมคณะ', 'วิ่งรอบอ่างเก็บน้ำ', 'ปิงปองสบาย ๆ', 'วอลเลย์บอลชายหาดจำลอง'],
            ['สนามกีฬากลาง', 'โรงยิม 2', 'สนามบาสหน้าหอ', 'ลู่วิ่งอ่างเก็บน้ำ', 'ศูนย์กีฬาในร่ม'], [2, 3, 10, 35, 50]],
        'ติวหนังสือ' => [25, ['ติวแคลคูลัสก่อนสอบ', 'อ่านฟิสิกส์ด้วยกัน', 'ติวโปรแกรมมิงพื้นฐาน', 'ติวสถิติ บทที่ 4', 'ฝึกพูดอังกฤษ', 'ทบทวนเคมีอินทรีย์'],
            ['ห้องสมุดกลาง ชั้น 3', 'Co-working คณะวิทย์', 'ห้องอ่านหนังสือ 24 ชม.', 'ร้านกาแฟหน้ามอ'], [1, 2, 7, 30, 60]],
        'ท่องเที่ยว' => [10, ['เที่ยวน้ำตกใกล้มอ', 'ปั่นจักรยานชมเมือง', 'เดินตลาดเช้าวันหยุด', 'ขึ้นดอยดูทะเลหมอก'],
            ['จุดนัดหน้าประตู 1', 'ลานจอดรถคณะ', 'ป้ายรถสองแถวหน้ามอ'], [3, 5, 12, 35, 45]],
        'จิตอาสา' => [12, ['เก็บขยะรอบอ่าง', 'สอนน้องอ่านหนังสือ', 'ปลูกต้นไม้ริมถนน', 'ทาสีโรงเรียนชุมชน'],
            ['อ่างเก็บน้ำ', 'โรงเรียนบ้านหนองแวง', 'ลานกิจกรรมกลาง'], [1, 2, 8, 32, 57]],
        'ดนตรี' => [13, ['แจมกีตาร์โปร่ง', 'ซ้อมวงอะคูสติก', 'ร้องเพลงคาราโอเกะ', 'ฝึกอูคูเลเล่มือใหม่'],
            ['ห้องซ้อมดนตรี ชมรม', 'ลานใต้ตึกเรียนรวม', 'ห้องคาราโอเกะหน้าหอ'], [2, 4, 12, 40, 42]],
        'อื่น ๆ' => [10, ['บอร์ดเกมคืนวันศุกร์', 'ถ่ายรูปฟิล์มรอบมอ', 'ทำอาหารหอพัก', 'ชมรมอ่านนิยาย'],
            ['ห้องนั่งเล่นหอ 5', 'ลานหน้าหอศิลป์', 'โรงอาหารกลาง'], [3, 5, 14, 38, 40]],
    ];

    private CarbonImmutable $now;

    public function run(): void
    {
        $categoryIds = Category::query()->whereIn('name', array_keys(self::CATEGORIES))->pluck('id', 'name');
        if ($categoryIds->count() !== count(self::CATEGORIES)) {
            throw new RuntimeException('ไม่พบหมวดหมู่ครบ กรุณารัน CategorySeeder ก่อน');
        }

        mt_srand(20261011);
        $this->now = CarbonImmutable::now()->startOfMinute();

        DB::transaction(function () use ($categoryIds) {
            $this->purge();
            $this->createActivities($this->createUsers(), $categoryIds->all());
        });

        $this->command?->info('AdminInsightsDemoSeeder: '.self::USER_COUNT.' ผู้ใช้ และกิจกรรมตัวอย่างพร้อมแล้ว');
    }

    /** ลบเฉพาะข้อมูลของ seeder นี้ (ผู้ใช้ insights.* และทุกอย่างที่ผูกกับผู้ใช้หรือกิจกรรมของพวกเขา รวมรายงานจากรุ่นก่อนของ seeder) */
    private function purge(): void
    {
        $userIds = User::query()->where('email', 'like', self::EMAIL_PATTERN)->pluck('id');
        if ($userIds->isEmpty()) {
            return;
        }
        $activityIds = Activity::query()->whereIn('user_id', $userIds)->pluck('id');

        Report::query()
            ->whereIn('reporter_id', $userIds)
            ->orWhere(fn ($q) => $q->where('target_type', 'user')->whereIn('target_id', $userIds))
            ->orWhere(fn ($q) => $q->where('target_type', 'activity')->whereIn('target_id', $activityIds))
            ->delete();
        DB::table('reviews')->whereIn('activity_id', $activityIds)->orWhereIn('user_id', $userIds)->delete();
        DB::table('activity_participants')->whereIn('activity_id', $activityIds)->orWhereIn('user_id', $userIds)->delete();
        DB::table('notifications')->where('notifiable_type', User::class)->whereIn('notifiable_id', $userIds)->delete();
        Activity::query()->whereIn('id', $activityIds)->delete();
        User::query()->whereIn('id', $userIds)->delete();
    }

    /** @return list<array{id: int, created: CarbonImmutable, dormantAfter: ?CarbonImmutable, weight: int, join: int}> */
    private function createUsers(): array
    {
        $password = Hash::make('password');
        $users = [];
        for ($i = 0; $i < self::USER_COUNT; $i++) {
            $n = $i + 1;
            $nick = self::NICKNAMES[$i % count(self::NICKNAMES)];
            $initial = self::INITIALS[($i + intdiv($i, count(self::NICKNAMES)) * 5) % count(self::INITIALS)];
            // ผู้ใช้ทยอยสมัครตลอด 12 เดือน คนท้าย ๆ เพิ่งสมัครไม่ถึง 30 วัน
            $created = $this->now->subDays(365 - $i * 4)->setTime(mt_rand(8, 22), mt_rand(0, 59));
            $user = new User([
                'name' => $nick.' '.$initial.'.',
                'email' => sprintf('insights.%02d@unimate.ac.th', $n),
                'student_id' => sprintf('66999%04d-%d', $n, $n % 10),
                'password' => $password, // hash แล้ว cast 'hashed' จะไม่ hash ซ้ำ
                'role' => 'student',
                'status' => 'active',
            ]);
            $user->created_at = $created;
            $user->updated_at = $created;
            $user->save();
            $users[] = [
                'id' => $user->id,
                'created' => $created,
                // ประมาณ 1 ใน 3 ลองใช้ช่วงแรกแล้วเลิกใช้ ทำให้ "ผู้ใช้ที่มีส่วนร่วม" ไม่ใช่เกือบ 100%
                'dormantAfter' => $i % 3 === 1 ? $created->addDays(mt_rand(20, 60)) : null,
                // บางคนเป็นผู้จัดตัวยง
                'weight' => $i % 6 === 0 ? 7 : ($i % 4 === 0 ? 3 : 1),
                // โอกาสส่งคำขอ: ขาประจำ 3, ทั่วไป 2, นาน ๆ ครั้ง 1
                'join' => $i % 5 === 0 ? 3 : ($i % 2 === 0 ? 2 : 1),
            ];
        }

        return $users;
    }

    /**
     * @param  list<array{id: int, created: CarbonImmutable, dormantAfter: ?CarbonImmutable, weight: int, join: int}>  $users
     * @param  array<string, int>  $categoryIds
     */
    private function createActivities(array $users, array $categoryIds): void
    {
        $participantRows = [];
        $reviewRows = [];

        // อดีต 9 เดือน (ถี่ขึ้นเรื่อย ๆ) 180 อัน และอนาคต 3 สัปดาห์ 15 อัน
        $plans = [];
        for ($i = 0; $i < 180; $i++) {
            $plans[] = 270 * (1 - sqrt(mt_rand() / mt_getrandmax()));
        }
        for ($i = 0; $i < 15; $i++) {
            $plans[] = -mt_rand(1, 21);
        }

        foreach ($plans as $daysAgo) {
            $category = $this->pick(array_map(fn ($c) => $c[0], self::CATEGORIES));
            [, $titles, $places, $ratingWeights] = self::CATEGORIES[$category];
            $starts = $this->startTime($category, $this->now->subDays((int) floor($daysAgo))->startOfDay(), $daysAgo < 0);
            $ends = $starts->addMinutes([90, 120, 150, 180][mt_rand(0, 3)]);
            $posted = $starts->subDays(mt_rand(1, 10))->setTime(mt_rand(8, 23), mt_rand(0, 59));
            if ($posted->gt($this->now)) {
                $posted = $this->now->subMinutes(mt_rand(30, 600));
            }

            // ผู้ใช้ที่สมัครแล้วและยังไม่เลิกใช้ ณ เวลาที่โพสต์
            $eligible = array_values(array_filter($users, fn ($u) => $u['created']->lt($posted)
                && ($u['dormantAfter'] === null || $u['dormantAfter']->gt($posted))));
            if (count($eligible) < 3) {
                continue;
            }
            $host = $this->pickUser($eligible);

            $activity = new Activity([
                'category_id' => $categoryIds[$category],
                'title' => $titles[mt_rand(0, count($titles) - 1)],
                'description' => 'กิจกรรมตัวอย่างสำหรับหน้าภาพรวมข้อมูล (สร้างโดย AdminInsightsDemoSeeder)',
                'location' => $places[mt_rand(0, count($places) - 1)],
                'starts_at' => $starts,
                'ends_at' => $ends,
                'capacity' => mt_rand(4, 20),
            ]);
            $activity->user()->associate($host['id']);
            $activity->status = mt_rand(1, 100) <= 6 ? 'cancelled' : 'published';
            $activity->created_at = $posted;
            $activity->updated_at = $posted;
            $activity->save();

            // คำขอเข้าร่วม: สุ่มแบบถ่วงน้ำหนัก (Efraimidis–Spirakis) ขาประจำจึงถูกเลือกบ่อยกว่า
            $candidates = array_values(array_filter($eligible, fn ($u) => $u['id'] !== $host['id']));
            $keys = [];
            foreach ($candidates as $k => $u) {
                $keys[$k] = (mt_rand(1, mt_getrandmax()) / mt_getrandmax()) ** (1 / $u['join']);
            }
            arsort($keys);
            $candidates = array_map(fn ($k) => $candidates[$k], array_keys($keys));
            $isPast = $starts->lte($this->now);
            $isEnded = $ends->lte($this->now);
            $wanted = (int) round($activity->capacity * (mt_rand(40, 140) / 100));
            if ($activity->status === 'cancelled') {
                $wanted = mt_rand(0, 3);
            }
            $approved = 0;
            $latest = $starts->lt($this->now) ? $starts : $this->now;
            foreach (array_slice($candidates, 0, $wanted) as $candidate) {
                $requested = $this->requestTime($posted, $latest);
                $roll = mt_rand(1, 100);
                if ($activity->status === 'cancelled') {
                    $status = $roll <= 50 ? 'cancelled' : 'pending';
                } elseif ($isPast) {
                    $status = $approved < $activity->capacity && $roll <= 82 ? 'approved' : ($roll <= 91 ? 'rejected' : 'cancelled');
                } else {
                    $status = match (true) {
                        $roll <= 45 => 'pending',
                        $roll <= 85 && $approved < $activity->capacity => 'approved',
                        $roll <= 92 => 'rejected',
                        default => 'cancelled',
                    };
                }
                if ($status === 'approved') {
                    $approved++;
                }

                $attendance = null;
                if ($status === 'approved' && $isEnded) {
                    $a = mt_rand(1, 100);
                    $attendance = $a <= 82 ? 'present' : ($a <= 92 ? 'absent' : null);
                }
                $participantRows[] = [
                    'activity_id' => $activity->id,
                    'user_id' => $candidate['id'],
                    'status' => $status,
                    'message' => null,
                    'attendance' => $attendance,
                    'created_at' => $requested->format('Y-m-d H:i:s'),
                    'updated_at' => $requested->format('Y-m-d H:i:s'),
                ];

                // รีวิวประมาณ 45% ของผู้ที่มาจริง
                if ($attendance === 'present' && mt_rand(1, 100) <= 45) {
                    $reviewed = $ends->addMinutes(mt_rand(30, 60 * 48));
                    if ($reviewed->gt($this->now)) {
                        $reviewed = $this->now;
                    }
                    $reviewRows[] = [
                        'activity_id' => $activity->id,
                        'user_id' => $candidate['id'],
                        'rating' => $this->pick([1 => $ratingWeights[0], 2 => $ratingWeights[1], 3 => $ratingWeights[2], 4 => $ratingWeights[3], 5 => $ratingWeights[4]]),
                        'comment' => null,
                        'created_at' => $reviewed->format('Y-m-d H:i:s'),
                        'updated_at' => $reviewed->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        foreach (array_chunk($participantRows, 200) as $chunk) {
            DB::table('activity_participants')->insert($chunk);
        }
        foreach (array_chunk($reviewRows, 200) as $chunk) {
            DB::table('reviews')->insert($chunk);
        }
    }

    /** เวลาเริ่มที่สมจริงตามหมวด: กีฬาเย็นวันธรรมดาหรือเช้าวันหยุด ติวหัวค่ำ เที่ยว/จิตอาสาเช้าวันหยุด ดนตรีหัวค่ำ */
    private function startTime(string $category, CarbonImmutable $day, bool $future): CarbonImmutable
    {
        $weekend = match ($category) {
            'ท่องเที่ยว', 'จิตอาสา' => true,
            'กีฬา' => mt_rand(1, 100) <= 30,
            'ติวหนังสือ' => mt_rand(1, 100) <= 20,
            default => mt_rand(1, 100) <= 35,
        };
        // เลื่อนไปวันที่ตรงประเภท (วันหยุดหรือวันธรรมดา) ที่ใกล้ที่สุด
        $direction = $future ? 1 : -1;
        for ($i = 0; $i < 7 && $day->isWeekend() !== $weekend; $i++) {
            $day = $day->addDays($direction);
        }

        [$hour, $minute] = match ($category) {
            'กีฬา' => $weekend ? [mt_rand(6, 8), [0, 30][mt_rand(0, 1)]] : [mt_rand(16, 18), [0, 30][mt_rand(0, 1)]],
            'ติวหนังสือ' => $weekend ? [mt_rand(13, 15), 0] : [mt_rand(18, 20), [0, 30][mt_rand(0, 1)]],
            'ท่องเที่ยว' => [mt_rand(6, 8), [0, 30][mt_rand(0, 1)]],
            'จิตอาสา' => [mt_rand(8, 9), 0],
            'ดนตรี' => [mt_rand(18, 20), [0, 30][mt_rand(0, 1)]],
            default => [mt_rand(10, 20), [0, 30][mt_rand(0, 1)]],
        };
        $starts = $day->setTime($hour, $minute);

        if (! $future && $starts->gt($this->now)) {
            $starts = $starts->subWeek();
        }
        if ($future && $starts->lte($this->now)) {
            $starts = $starts->addWeek();
        }

        return $starts;
    }

    /** เวลาส่งคำขอ: สุ่มระหว่างเวลาโพสต์ถึงเวลาเริ่ม (ไม่เกินตอนนี้) */
    private function requestTime(CarbonImmutable $from, CarbonImmutable $until): CarbonImmutable
    {
        return $from->addMinutes(mt_rand(0, max(1, (int) $from->diffInMinutes($until, true))));
    }

    /**
     * สุ่มคีย์ตามน้ำหนัก
     *
     * @template TKey of array-key
     *
     * @param  array<TKey, int>  $weights
     * @return TKey
     */
    private function pick(array $weights): int|string
    {
        $roll = mt_rand(1, (int) array_sum($weights));
        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    /**
     * @param  list<array{id: int, created: CarbonImmutable, dormantAfter: ?CarbonImmutable, weight: int, join: int}>  $users
     * @return array{id: int, created: CarbonImmutable, dormantAfter: ?CarbonImmutable, weight: int, join: int}
     */
    private function pickUser(array $users): array
    {
        return $users[$this->pick(array_map(fn ($u) => $u['weight'], $users))];
    }
}
