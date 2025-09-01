<?php

namespace App\Services;

use App\Models\AdminLog;

class AdminLogService
{
    public function record($userId, $action, $details = null, $ip = null)
    {
        return AdminLog::create([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip ?? request()->ip(),
        ]);
    }


    public static function getActionByCode(int $code): ?string
    {
        $actions = [
            1 => 'LOGIN',
            2 => 'LOGOUT',
            3 => 'USER_STATUS_CHANGE',
            4 => 'COURSE_ENROLLMENT',
            5 => 'COURSE_UPDATE',
            6 => 'SYSTEM_ANALYTICS_REFRESH',
            7 => 'JOB_POSTING',
            8 => 'COURSE_ADDED'
        ];

        return $actions[$code] ?? null; // null if code is invalid
    }

}
