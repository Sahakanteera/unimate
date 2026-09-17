<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    // ไม่เปิด user_id/status ให้ mass assignment; Controller เป็นผู้กำหนดสองค่านี้
    // capacity คือจำนวนที่รับเพิ่ม ไม่ใช่ยอดผู้เข้าร่วมจริงและไม่รวมผู้ประกาศ
    protected $fillable = ['category_id', 'title', 'description', 'location', 'starts_at', 'ends_at', 'capacity'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'capacity' => 'integer'];
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
}
