<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Carbon;

class ActivityPolicy
{
    public function update(User $user, Activity $activity): bool
    {
        // เฉพาะเจ้าของโพสต์เท่านั้น: Admin ไม่มีข้อยกเว้นให้แก้โพสต์ของคนอื่น
        return $user->id === $activity->user_id;
    }

    public function cancel(User $user, Activity $activity): bool
    {
        // การยกเลิกใช้เงื่อนไขเดียวกับการแก้ไข เพื่อให้สิทธิ์ทั้งสองทางตรงกัน
        return $this->update($user, $activity);
    }

    public function join(User $user, Activity $activity): bool
    {
        // ไม่ใช่เจ้าของ + กิจกรรม published + ไม่ถูก Admin ซ่อน + ยังไม่หมดเวลา
        return $user->id !== $activity->user_id
            && $activity->status === 'published'
            && $activity->hidden_at === null
            && Carbon::parse($activity->starts_at)->isFuture();
    }

    public function review(User $user, Activity $activity): bool
    {
        // ส่วนที่ 5: เงื่อนไขทั้งหมดอยู่ที่ Activity::reviewBlockReason()
        return $activity->reviewBlockReason($user) === null;
    }

    public function manageRequests(User $user, Activity $activity): bool
    {
        // เฉพาะเจ้าของกิจกรรมเท่านั้นที่ดู/อนุมัติ/ปฏิเสธคำขอได้
        return $user->id === $activity->user_id;
    }
}
