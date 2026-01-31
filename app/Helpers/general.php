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

if (!function_exists('getPackageStatusEn')) {
    function getPackageStatusEN($status, $delivery_date = null)
    {
        $status = (int) $status;
        $statuses = config('constants.PACKAGE_STATUS_EN');
        $statusName = $statuses[$status] ?? 'غير معروف';
        $today = today()->toDateString();
        if ($delivery_date && $status === 5 && $delivery_date != $today) {
            return 'Delayed';
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
            case 'client_refuse_to_accept_order':
                return 'الشحنة تحتوي على مشكلة والعميل رفض الاستلام';
            case 'other':
                return 'سبب آخر';
            case 'too_many_attempts':
                return 'تم إلغاء الشحنة بعد محاولات تسليم متعددة';
            case 'cancelled_by_third_party':
                return 'تم إلغاء الشحنة بواسطة الطرف الثالث';
            default:
                return 'غير معروف';
        }
    }
}

if (!function_exists('getReasonMessageEN')) {
    function getReasonMessageEN($reason)
    {
        switch ($reason) {
            case 'no_answer':
                return 'The customer did not answer the delivery agent';
            case 'refused':
                return 'The customer refused to accept the package';
            case 'rescheduled':
                return 'The customer rescheduled the delivery';
            case 'rto':
                return 'Returned to origin (RTO)';
            case 'client_wrong_data':
                return 'Customer information is incorrect';
            case 'client_refuse_to_accept_order':
                return 'The package has an issue and the customer refused to accept it';
            case 'other':
                return 'Other reason';
            case 'too_many_attempts':
                return 'The package was cancelled after multiple delivery attempts';
            case 'cancelled_by_third_party':
                return 'The package was cancelled by the third party';
            default:
                return 'Unknown';
        }
    }
}


if (!function_exists('getDeliveryFeePayer')) {
    function getDeliveryFeePayer($delivery_fee_payer, $status, $reason)
    {
        /*if($status == 1 || ($status == 3 && $reason == 'client_refuse_to_accept_order')) { // show fee payer if status is delivered or cancelled and reason is Client_refuse_to_accept_order
            return config('constants.DELIVERY_FEE_PAYER')[$delivery_fee_payer] ?? '---';
        }
        return '---';*/
        return config('constants.DELIVERY_FEE_PAYER')[$delivery_fee_payer] ?? '---';
    }
}
