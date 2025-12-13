<?php
if (!function_exists('getPackageStatus')) {
    function getPackageStatus($status, $delivery_date = null)
    {
        $status = (int) $status;
        $statuses = config('constants.PACKAGE_STATUS');
        $statusName = $statuses[$status] ?? 'غير معروف';
        $today = today()->toDateString();
        if ($delivery_date && $status === 5 && $delivery_date != $today) {
            return 'مؤجلة';
        }
        return $statusName;
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
            case 'client_wrong_data':
                return 'معلومات العميل غير صحيحة';
            case 'other':
                return 'سبب آخر';
            case 'too_many_attempts':
                return 'تم إلغاء الشحنة بعد محاولات تسليم متعددة';
            default:
                return 'غير معروف';
        }
    }
}
