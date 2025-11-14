<?php
if (!function_exists('getPackageStatus')) {
    function getPackageStatus($status)
    {
        $status = (int) $status;
        $statuses = config('constants.PACKAGE_STATUS');
        return $statuses[$status] ?? 'غير معروف';
    }
}

if (!function_exists('getReasonMessage')) {
    function getReasonMessage($reason)
    {
        switch ($reason) {
            case 'no_answer':
                return 'العميل لم يرد على المندوب';
            case 'refused':
                return 'العميل رفض الاستلام';
            case 'rescheduled':
                return 'العميل قام بتأجيل الشحنة';
            case 'rto':
                return 'RTO';
            case 'other':
                return 'سبب آخر';
            default:
                return 'غير معروف';
        }
    }
}
