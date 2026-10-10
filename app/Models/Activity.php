<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Activity extends Model
{
    // ไม่เปิด user_id/status ให้ mass assignment; Controller เป็นผู้กำหนดสองค่านี้
    // capacity คือจำนวนที่รับเพิ่ม ไม่ใช่ยอดผู้เข้าร่วมจริงและไม่รวมผู้ประกาศ
    protected $fillable = ['category_id', 'title', 'description', 'location', 'google_maps_url', 'starts_at', 'ends_at', 'capacity'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'capacity' => 'integer', 'hidden_at' => 'datetime', 'latitude' => 'float', 'longitude' => 'float'];
    }

    public function googleMapsUrl(): ?string
    {
        if ($this->google_maps_url) {
            return $this->google_maps_url;
        }
        // โพสต์เก่าที่บันทึกหมุดไว้ ยังเปิดจุดนัดพบผ่าน Google Maps ได้
        if ($this->latitude !== null && $this->longitude !== null) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitude.','.$this->longitude;
        }

        return null;
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isEnded(): bool
    {
        // ใช้ Carbon::parse แบบเดียวกับ ActivityPolicy เพราะ PHPStan อ่านชนิดคอลัมน์จาก migration เป็น string
        return Carbon::parse($this->ends_at)->isPast();
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /**
     * ส่วนที่ 5: คืนเหตุผลที่ผู้ใช้รีวิวกิจกรรมนี้ไม่ได้ หรือ null ถ้ารีวิวได้
     * ใช้ร่วมกันทั้ง Controller (ข้อความ error) และหน้าเว็บ (ซ่อน/แสดงฟอร์ม)
     */
    public function reviewBlockReason(User $user): ?string
    {
        if ($user->id === $this->user_id) {
            return 'ผู้จัดไม่สามารถรีวิวกิจกรรมของตัวเองได้';
        }
        if (! $this->isEnded()) {
            return 'รีวิวได้หลังกิจกรรมจบแล้วเท่านั้น';
        }
        $attended = $this->participants()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('attendance', 'present')
            ->exists();
        if (! $attended) {
            return 'เฉพาะผู้ที่ได้รับการเช็กชื่อว่ามาเข้าร่วมจริงเท่านั้นที่รีวิวได้';
        }
        if ($this->reviews()->where('user_id', $user->id)->exists()) {
            return 'คุณรีวิวกิจกรรมนี้ไปแล้ว';
        }

        return null;
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<ActivityParticipant, $this> */
    public function participants(): HasMany
    {
        return $this->hasMany(ActivityParticipant::class);
    }

    /** @return HasMany<ActivityParticipant, $this> */
    public function approvedParticipants(): HasMany
    {
        return $this->hasMany(ActivityParticipant::class)->where('status', 'approved');
    }

    public function approvedCount(): int
    {
        return $this->approvedParticipants()->count();
    }

    public function isFull(): bool
    {
        return $this->approvedCount() >= $this->capacity;
    }

    public function remainingSlots(): int
    {
        return max(0, $this->capacity - $this->approvedCount());
    }
}
