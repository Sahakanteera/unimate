<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * ตัวเลขของหน้า "ภาพรวมข้อมูล" (/admin/insights) และไฟล์ CSV ที่ export ดูนิยามใน docs/admin-insights-plan-th.md
 *
 * - นับกิจกรรมที่ starts_at อยู่ใน [from, now] รวมกิจกรรมที่ถูกซ่อน คำขอ การเช็กชื่อ และรีวิว นับตามกิจกรรมเหล่านี้
 * - query เฉพาะคอลัมน์ที่ใช้แล้วรวมผลใน PHP ไม่ใช้ฟังก์ชันวันที่ของฐานข้อมูล
 */
class AdminInsights
{
    public const RANGES = [
        '30d' => '30 วัน',
        '90d' => '90 วัน',
        'all' => 'ทั้งหมด',
    ];

    public const DEFAULT_RANGE = '90d';

    /** วันจันทร์ถึงอาทิตย์ตาม dayOfWeekIso 1-7 */
    public const DAYS = [1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ', 4 => 'พฤหัสบดี', 5 => 'ศุกร์', 6 => 'เสาร์', 7 => 'อาทิตย์'];

    public const DAYS_SHORT = [1 => 'จ.', 2 => 'อ.', 3 => 'พ.', 4 => 'พฤ.', 5 => 'ศ.', 6 => 'ส.', 7 => 'อา.'];

    public readonly string $range;

    public readonly CarbonImmutable $now;

    public readonly CarbonImmutable $from;

    /** @var Collection<int, Activity> กิจกรรมในช่วง (ทุกสถานะ) */
    private Collection $activities;

    /** @var Collection<int, ActivityParticipant> คำขอของกิจกรรมในช่วง */
    private Collection $participants;

    /** @var Collection<int, Review> รีวิวของกิจกรรมในช่วง */
    private Collection $reviews;

    public function __construct(string $range = self::DEFAULT_RANGE, ?CarbonImmutable $now = null)
    {
        $this->range = self::normalizeRange($range);
        $this->now = $now ?? CarbonImmutable::now();
        $this->from = match ($this->range) {
            '30d' => $this->now->subDays(30)->startOfDay(),
            'all' => $this->firstActivityDate(),
            default => $this->now->subDays(90)->startOfDay(),
        };

        $this->loadData();
    }

    public static function normalizeRange(mixed $range): string
    {
        return is_string($range) && array_key_exists($range, self::RANGES) ? $range : self::DEFAULT_RANGE;
    }

    /** @return array<string, mixed> ข้อมูลที่ view ใช้ตรง ๆ */
    public function toArray(): array
    {
        return [
            'range' => $this->range,
            'ranges' => self::RANGES,
            'periodLabel' => $this->periodLabel(),
            'kpis' => $this->kpis(),
            'trend' => $this->trend(),
            'heatmap' => $this->heatmap($this->activityStartTimes()),
            'categories' => $this->categories(),
        ];
    }

    public function periodLabel(): string
    {
        return $this->from->settings(['locale' => 'th'])->isoFormat('D MMM YYYY').' – '.$this->now->settings(['locale' => 'th'])->isoFormat('D MMM YYYY');
    }

    // ───────────────────────── โหลดข้อมูล ─────────────────────────

    private function firstActivityDate(): CarbonImmutable
    {
        $first = Activity::query()->where('starts_at', '<=', $this->now)->min('starts_at');

        return $first === null
            ? $this->now->startOfMonth()
            : CarbonImmutable::parse((string) $first)->startOfDay();
    }

    private function loadData(): void
    {
        $window = fn ($q) => $q->where('starts_at', '>=', $this->from)->where('starts_at', '<=', $this->now);

        $this->activities = Activity::query()
            ->select(['id', 'user_id', 'category_id', 'capacity', 'status', 'starts_at', 'ends_at'])
            ->where($window)
            ->get();

        $ids = Activity::query()->select('id')->where($window);

        $this->participants = ActivityParticipant::query()
            ->select(['activity_id', 'user_id', 'status', 'attendance'])
            ->whereIn('activity_id', $ids)
            ->get();

        $this->reviews = Review::query()
            ->select(['activity_id', 'rating'])
            ->whereIn('activity_id', $ids)
            ->get();
    }

    /** @return Collection<int, Activity> กิจกรรมที่จัดจริง (ไม่รวมที่ยกเลิก) */
    private function held(): Collection
    {
        return $this->activities->where('status', '!=', 'cancelled')->values();
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @return Collection<int, ActivityParticipant>
     */
    private function participantsOf(Collection $activities): Collection
    {
        $ids = $activities->pluck('id')->flip();

        return $this->participants->filter(fn (ActivityParticipant $p) => $ids->has($p->activity_id))->values();
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @return Collection<int, Review>
     */
    private function reviewsOf(Collection $activities): Collection
    {
        $ids = $activities->pluck('id')->flip();

        return $this->reviews->filter(fn (Review $r) => $ids->has($r->activity_id))->values();
    }

    // ───────────────────────── KPI ─────────────────────────

    /** @return array<string, float|int|null> */
    private function stats(): array
    {
        $held = $this->held();
        $approved = $this->participantsOf($held)->where('status', 'approved');
        $present = $approved->where('attendance', 'present')->count();
        $absent = $approved->where('attendance', 'absent')->count();
        $endedIds = $held->filter(fn (Activity $a) => Carbon::parse($a->ends_at)->lte($this->now))->pluck('id')->flip();
        $unchecked = $approved->filter(fn (ActivityParticipant $p) => $p->attendance === null && $endedIds->has($p->activity_id))->count();
        $capacity = (int) $held->sum('capacity');
        $studentIds = User::query()->where('role', 'student')->pluck('id');

        // ผู้จัดหรือผู้ส่งคำขอของกิจกรรมในช่วง นับเฉพาะนักศึกษาให้ตรงกับตัวหาร "% ของนักศึกษาทั้งหมด"
        $engaged = $this->activities->pluck('user_id')
            ->merge($this->participantsOf($this->activities)->pluck('user_id'))
            ->unique()
            ->intersect($studentIds)
            ->count();

        return [
            'total' => $this->activities->count(),
            'held' => $held->count(),
            'cancelled' => $this->activities->count() - $held->count(),
            'students' => $studentIds->count(),
            'engaged' => $engaged,
            'approved' => $approved->count(),
            'capacity' => $capacity,
            'fillRate' => $capacity > 0 ? $approved->count() / $capacity * 100 : null,
            'present' => $present,
            'absent' => $absent,
            'unchecked' => $unchecked,
            'attendanceRate' => ($present + $absent) > 0 ? $present / ($present + $absent) * 100 : null,
        ];
    }

    /** @return array<string, mixed> */
    private function kpis(): array
    {
        $s = $this->stats();
        $cancelPct = $s['total'] > 0 ? round((int) $s['cancelled'] / (int) $s['total'] * 100) : 0;

        return [
            'stats' => $s,
            'cards' => [
                [
                    'label' => 'กิจกรรมที่จัด',
                    'value' => number_format((int) $s['held']),
                    'sub' => $s['total'] > 0 ? 'ยกเลิก '.$s['cancelled'].' ('.$cancelPct.'%)' : 'ยังไม่มีกิจกรรมในช่วงนี้',
                ],
                [
                    'label' => 'ผู้ใช้ที่มีส่วนร่วม',
                    'value' => number_format((int) $s['engaged']),
                    'sub' => $s['students'] > 0 ? 'คิดเป็น '.round((int) $s['engaged'] / (int) $s['students'] * 100).'% ของนักศึกษาทั้งหมด' : 'ยังไม่มีนักศึกษาในระบบ',
                ],
                [
                    'label' => 'อัตราเติมที่นั่ง',
                    'value' => $s['fillRate'] === null ? '–' : self::pct($s['fillRate']),
                    'sub' => $s['fillRate'] === null ? 'ยังไม่มีที่นั่งในช่วงนี้' : 'อนุมัติ '.number_format((int) $s['approved']).' จาก '.number_format((int) $s['capacity']).' ที่นั่ง',
                ],
                [
                    'label' => 'อัตรามาตามนัด',
                    'value' => $s['attendanceRate'] === null ? '–' : self::pct($s['attendanceRate']),
                    'sub' => $s['attendanceRate'] === null
                        ? 'ยังไม่มีการเช็กชื่อ'
                        : 'เช็กชื่อแล้ว '.number_format((int) $s['present'] + (int) $s['absent']).' · ยังไม่เช็กชื่อ '.number_format((int) $s['unchecked']),
                ],
            ],
        ];
    }

    public static function pct(float|int $value): string
    {
        return round($value).'%';
    }

    // ───────────────────────── แนวโน้ม ─────────────────────────

    /** @return array<string, mixed> */
    private function trend(): array
    {
        $unit = match ($this->range) {
            '30d' => 'day',
            '90d' => 'week',
            default => 'month',
        };
        $bucketStart = fn (CarbonImmutable $d): CarbonImmutable => match ($unit) {
            'day' => $d->startOfDay(),
            'week' => $d->startOfWeek(CarbonImmutable::MONDAY),
            default => $d->startOfMonth(),
        };

        // สร้างทุกช่วงย่อยตั้งแต่ from ถึง now (ช่วงที่ไม่มีข้อมูลเป็น 0)
        $buckets = [];
        for ($cursor = $bucketStart($this->from); $cursor->lte($this->now); $cursor = match ($unit) {
            'day' => $cursor->addDay(),
            'week' => $cursor->addWeek(),
            default => $cursor->addMonthNoOverflow(),
        }) {
            $th = $cursor->settings(['locale' => 'th']);
            $buckets[$cursor->format('Y-m-d')] = [
                'date' => $cursor->format('Y-m-d'),
                'label' => $unit === 'month' ? $th->isoFormat('MMM YY') : $th->isoFormat('D MMM'),
                'title' => match ($unit) {
                    'day' => $th->isoFormat('dd D MMM YYYY'),
                    'week' => 'สัปดาห์ที่เริ่ม '.$th->isoFormat('D MMM YYYY'),
                    default => $th->isoFormat('MMMM YYYY'),
                },
                'value' => 0,
            ];
        }

        foreach ($this->held() as $a) {
            $key = $bucketStart(CarbonImmutable::parse($a->starts_at))->format('Y-m-d');
            if (isset($buckets[$key])) {
                $buckets[$key]['value']++;
            }
        }

        $bars = array_values($buckets);
        $values = array_column($bars, 'value');
        $max = $values === [] ? 0 : max($values);
        $total = array_sum($values);
        $unitLabel = ['day' => 'วัน', 'week' => 'สัปดาห์', 'month' => 'เดือน'][$unit];

        $insight = null;
        if ($total > 0) {
            $peak = $bars[(int) array_search($max, $values, true)];
            $insight = 'สูงสุด: '.$peak['title'].' · '.$max.' กิจกรรม · เฉลี่ย '
                .rtrim(rtrim(number_format($total / count($bars), 1), '0'), '.').' กิจกรรมต่อ'.$unitLabel;
        }

        return [
            'unitLabel' => ['day' => 'รายวัน', 'week' => 'รายสัปดาห์ (เริ่มวันจันทร์)', 'month' => 'รายเดือน'][$unit],
            'bars' => $bars,
            'max' => $max,
            'total' => $total,
            'insight' => $insight,
        ];
    }

    // ───────────────────────── Heatmap ─────────────────────────

    /** @return list<CarbonImmutable> */
    private function activityStartTimes(): array
    {
        return array_values($this->held()->map(fn (Activity $a) => CarbonImmutable::parse($a->starts_at))->all());
    }

    /**
     * ตาราง 7 วัน × 12 ช่วง (ช่วงละ 2 ชั่วโมง) พร้อมระดับสี 0-5
     *
     * @param  list<CarbonImmutable>  $times
     * @return array<string, mixed>
     */
    public function heatmap(array $times, string $noun = 'กิจกรรม'): array
    {
        $grid = [];
        foreach (self::DAYS as $day => $_) {
            $grid[$day] = array_fill(0, 12, 0);
        }
        foreach ($times as $t) {
            $grid[$t->dayOfWeekIso][intdiv($t->hour, 2)]++;
        }

        $max = 0;
        $peak = null;
        foreach ($grid as $day => $slots) {
            foreach ($slots as $slot => $v) {
                if ($v > $max) {
                    $max = $v;
                    $peak = [$day, $slot];
                }
            }
        }

        $rows = [];
        foreach ($grid as $day => $slots) {
            $cells = [];
            foreach ($slots as $slot => $v) {
                $cells[] = [
                    'value' => $v,
                    // ceil(v × 5 / max) ด้วยจำนวนเต็ม กันทศนิยมคลาดจนระดับไม่ตรงกับ legend
                    'level' => $v > 0 ? min(5, max(1, intdiv($v * 5 + $max - 1, $max))) : 0,
                    'title' => 'วัน'.self::DAYS[$day].' '.self::slotLabel($slot),
                ];
            }
            $rows[] = ['day' => $day, 'label' => self::DAYS_SHORT[$day], 'name' => self::DAYS[$day], 'cells' => $cells, 'total' => array_sum($slots)];
        }

        // ช่วงค่าของแต่ละระดับ: ระดับ L ครอบคลุม v ที่ ceil(v × 5 / max) = L
        $legend = [];
        for ($level = 1; $level <= 5; $level++) {
            $lo = (int) floor(($level - 1) * $max / 5) + 1;
            $hi = (int) floor($level * $max / 5);
            if ($max > 0 && $lo <= $hi) {
                $legend[] = ['level' => $level, 'label' => $lo === $hi ? (string) $lo : $lo.'–'.$hi];
            }
        }

        $total = count($times);
        $insight = null;
        $summary = 'ไม่มีข้อมูลในช่วงนี้';
        if ($peak !== null) {
            $dayTotals = array_map('array_sum', $grid);
            $busyDay = (int) array_search(max($dayTotals), $dayTotals, true);
            $slotTotals = [];
            for ($s = 0; $s < 12; $s++) {
                $slotTotals[$s] = array_sum(array_column($grid, $s));
            }
            $busySlot = (int) array_search(max($slotTotals), $slotTotals, true);

            $peakText = 'วัน'.self::DAYS[$peak[0]].' '.self::slotLabel($peak[1]).' · '.$max.' '.$noun;
            $insight = 'คึกคักที่สุด: '.$peakText
                .' · วันที่มากสุด: '.self::DAYS[$busyDay].' ('.$dayTotals[$busyDay].')'
                .' · ช่วงเวลาที่มากสุด: '.self::slotLabel($busySlot).' ('.$slotTotals[$busySlot].')';
            $summary = 'ตารางความถี่'.$noun.'ตามวันและเวลา รวม '.$total.' '.$noun.' คึกคักที่สุด '.$peakText;
        }

        return [
            'noun' => $noun,
            'rows' => $rows,
            'hours' => array_map(fn ($s) => sprintf('%02d', $s * 2), range(0, 11)),
            'max' => $max,
            'total' => $total,
            'legend' => $legend,
            'insight' => $insight,
            'summary' => $summary,
        ];
    }

    public static function slotLabel(int $slot): string
    {
        return sprintf('%02d:00–%02d:00', $slot * 2, $slot * 2 + 2);
    }

    // ───────────────────────── หมวดหมู่ ─────────────────────────

    /** @return array<string, mixed> */
    private function categories(): array
    {
        $held = $this->held();
        $participants = $this->participantsOf($held)->groupBy('activity_id');
        $reviews = $this->reviewsOf($held)->groupBy('activity_id');

        $rows = Category::query()->orderBy('name')->get(['id', 'name'])->map(function (Category $c) use ($held, $participants, $reviews) {
            $acts = $held->where('category_id', $c->id);
            $approved = $acts->flatMap(fn (Activity $a) => $participants->get($a->id, collect()))->where('status', 'approved');
            $present = $approved->where('attendance', 'present')->count();
            $absent = $approved->where('attendance', 'absent')->count();
            $capacity = (int) $acts->sum('capacity');
            $ratings = $acts->flatMap(fn (Activity $a) => $reviews->get($a->id, collect()))->pluck('rating');

            return [
                'name' => $c->name,
                'activities' => $acts->count(),
                'fillRate' => $capacity > 0 ? $approved->count() / $capacity * 100 : null,
                'attendanceRate' => ($present + $absent) > 0 ? $present / ($present + $absent) * 100 : null,
                'rating' => $ratings->isEmpty() ? null : round((float) $ratings->avg(), 1),
                'reviews' => $ratings->count(),
            ];
        })->sort(fn ($a, $b) => [$b['activities'], $a['name']] <=> [$a['activities'], $b['name']])->values()->all();

        $max = $rows === [] ? 0 : max(array_column($rows, 'activities'));
        $insight = null;
        if ($max > 0) {
            $insight = 'จัดมากที่สุด: '.$rows[0]['name'].' · '.$rows[0]['activities'].' กิจกรรม';
            $rated = array_filter($rows, fn ($r) => $r['fillRate'] !== null && $r['activities'] >= 3);
            if ($rated !== []) {
                usort($rated, fn ($a, $b) => $b['fillRate'] <=> $a['fillRate']);
                $insight .= ' · เติมที่นั่งดีที่สุด: '.$rated[0]['name'].' ('.self::pct($rated[0]['fillRate']).')';
            }
        }

        return ['rows' => $rows, 'max' => $max, 'insight' => $insight];
    }

    // ───────────────────────── Export CSV ─────────────────────────

    /**
     * แถวของไฟล์ CSV: ข้อมูลชุดเดียวกับหน้าเว็บ แบ่งเป็นตอน ๆ คั่นด้วยบรรทัดว่าง
     * ตัวเลขเป็นค่าดิบ (เปอร์เซ็นต์ทศนิยม 1 ตำแหน่งโดยไม่มีเครื่องหมาย %) เพื่อให้ Excel คำนวณต่อได้
     *
     * @return list<array<int, mixed>>
     */
    public function exportRows(): array
    {
        $data = $this->toArray();
        $s = $data['kpis']['stats'];
        $round = fn ($v) => $v === null ? null : round((float) $v, 1);

        $rows = [
            ['รายงานสถิติ UniMate'],
            ['ช่วงเวลา', $data['periodLabel']],
            ['ตั้งแต่', $this->from->format('Y-m-d H:i'), 'ถึง', $this->now->format('Y-m-d H:i')],
            ['หมายเหตุ', 'นับกิจกรรมตามวันเริ่มกิจกรรม (รวมกิจกรรมที่ถูกซ่อน) เวลาประเทศไทย'],
            [],
            ['สรุปตัวเลข'],
            ['ตัวชี้วัด', 'ค่า', 'หน่วย'],
            ['กิจกรรมที่จัด', $s['held'], 'กิจกรรม'],
            ['กิจกรรมที่ยกเลิก', $s['cancelled'], 'กิจกรรม'],
            ['ผู้ใช้ที่มีส่วนร่วม', $s['engaged'], 'คน'],
            ['นักศึกษาทั้งหมด', $s['students'], 'คน'],
            ['อัตราเติมที่นั่ง', $round($s['fillRate']), '%'],
            ['ผู้ได้รับอนุมัติ', $s['approved'], 'คน'],
            ['ที่นั่งทั้งหมด', $s['capacity'], 'ที่นั่ง'],
            ['อัตรามาตามนัด', $round($s['attendanceRate']), '%'],
            ['มาร่วมจริง', $s['present'], 'คน'],
            ['ขาด', $s['absent'], 'คน'],
            ['ยังไม่เช็กชื่อ', $s['unchecked'], 'คน'],
            [],
            ['แยกตามหมวดหมู่'],
            ['หมวดหมู่', 'กิจกรรม', 'เติมที่นั่ง (%)', 'มาตามนัด (%)', 'คะแนนเฉลี่ย', 'จำนวนรีวิว'],
        ];
        foreach ($data['categories']['rows'] as $c) {
            $rows[] = [$c['name'], $c['activities'], $round($c['fillRate']), $round($c['attendanceRate']), $c['rating'], $c['reviews']];
        }

        $rows[] = [];
        $rows[] = ['จำนวนกิจกรรมที่จัด '.$data['trend']['unitLabel']];
        $rows[] = ['วันที่เริ่มช่วง', 'ช่วง', 'กิจกรรม'];
        foreach ($data['trend']['bars'] as $bar) {
            $rows[] = [$bar['date'], $bar['title'], $bar['value']];
        }

        $rows[] = [];
        $rows[] = ['จำนวนกิจกรรมตามวันและเวลาเริ่ม'];
        $rows[] = array_merge(['วัน'], array_map(fn ($s) => self::slotLabel($s), range(0, 11)), ['รวม']);
        foreach ($data['heatmap']['rows'] as $row) {
            $rows[] = array_merge([$row['name']], array_column($row['cells'], 'value'), [$row['total']]);
        }

        return $rows;
    }

    /** ค่าในช่อง CSV: ตัวเลขคงเดิม null เป็นช่องว่าง และข้อความที่ขึ้นต้นด้วย = + - @ ใส่ ' นำหน้า กัน Excel ตีความเป็นสูตร */
    public static function csvCell(mixed $value): string|int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $text = is_scalar($value) ? (string) $value : '';

        return preg_match('/^[=+\-@\t\r]/', $text) === 1 ? "'".$text : $text;
    }
}
