<?php
if (!function_exists('getPackageStatus')) {
    function getPackageStatus($status)
    {
        $status = (int) $status;
        $statuses = config('constants.PACKAGE_STATUS');
        return $statuses[$status] ?? 'غير معروف';
    }
}
