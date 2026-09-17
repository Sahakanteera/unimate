<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

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
}
