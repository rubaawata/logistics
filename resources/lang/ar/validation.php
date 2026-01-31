<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | الرسائل الافتراضية الخاصة بالتحقق من الصحة. بعض القواعد لها أكثر من نسخة
    | مثل قواعد الحجم. يمكنك تعديل هذه الرسائل كما تشاء.
    |
    */

    'accepted'             => 'يجب قبول :attribute.',
    'active_url'           => ':attribute ليس رابط صالح.',
    'after'                => 'يجب أن يكون تاريخ :attribute بعد :date.',
    'after_or_equal'       => 'يجب أن يكون تاريخ :attribute بعد أو يساوي :date.',
    'alpha'                => 'قد يحتوي :attribute على حروف فقط.',
    'alpha_dash'           => 'قد يحتوي :attribute على حروف، أرقام، شرطات وشرطات سفلية فقط.',
    'alpha_num'            => 'قد يحتوي :attribute على حروف وأرقام فقط.',
    'array'                => 'يجب أن يكون :attribute مصفوفة.',
    'before'               => 'يجب أن يكون تاريخ :attribute قبل :date.',
    'before_or_equal'      => 'يجب أن يكون تاريخ :attribute قبل أو يساوي :date.',
    'between'              => [
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
        'file'    => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string'  => 'يجب أن يكون طول :attribute بين :min و :max حرفًا.',
        'array'   => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max.',
    ],
    'boolean'              => 'يجب أن يكون حقل :attribute صحيح أو خطأ.',
    'confirmed'            => 'تأكيد :attribute غير مطابق.',
    'date'                 => ':attribute ليس تاريخًا صالحًا.',
    'date_equals'          => 'يجب أن يكون :attribute تاريخًا مساويًا لـ :date.',
    'date_format'          => ':attribute لا يتوافق مع الصيغة :format.',
    'different'            => 'يجب أن يكون :attribute و :other مختلفان.',
    'digits'               => 'يجب أن يكون :attribute :digits أرقام.',
    'digits_between'       => 'يجب أن يكون :attribute بين :min و :max أرقام.',
    'dimensions'           => ':attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct'             => 'حقل :attribute يحتوي على قيمة مكررة.',
    'email'                => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالح.',
    'ends_with'            => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'exists'               => ':attribute المحدد غير صالح.',
    'file'                 => 'يجب أن يكون :attribute ملف.',
    'filled'               => 'حقل :attribute يجب أن يحتوي على قيمة.',
    'gt'                   => [
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت.',
        'string'  => 'يجب أن يكون طول :attribute أكبر من :value حرفًا.',
        'array'   => 'يجب أن يحتوي :attribute على أكثر من :value عنصرًا.',
    ],
    'gte'                  => [
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string'  => 'يجب أن يكون طول :attribute أكبر من أو يساوي :value حرفًا.',
        'array'   => 'يجب أن يحتوي :attribute على :value عناصر أو أكثر.',
    ],
    'image'                => 'يجب أن يكون :attribute صورة.',
    'in'                   => ':attribute المحدد غير صالح.',
    'in_array'             => 'حقل :attribute غير موجود في :other.',
    'integer'              => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'ip'                   => 'يجب أن يكون :attribute عنوان IP صالح.',
    'ipv4'                 => 'يجب أن يكون :attribute عنوان IPv4 صالح.',
    'ipv6'                 => 'يجب أن يكون :attribute عنوان IPv6 صالح.',
    'json'                 => 'يجب أن يكون :attribute نص JSON صالح.',
    'lt'                   => [
        'numeric' => 'يجب أن يكون :attribute أصغر من :value.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أصغر من :value كيلوبايت.',
        'string'  => 'يجب أن يكون طول :attribute أصغر من :value حرفًا.',
        'array'   => 'يجب أن يحتوي :attribute على أقل من :value عنصرًا.',
    ],
    'lte'                  => [
        'numeric' => 'يجب أن يكون :attribute أصغر من أو يساوي :value.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أصغر من أو يساوي :value كيلوبايت.',
        'string'  => 'يجب أن يكون طول :attribute أصغر من أو يساوي :value حرفًا.',
        'array'   => 'يجب ألا يحتوي :attribute على أكثر من :value عنصرًا.',
    ],
    'max'                  => [
        'numeric' => 'قد لا يكون :attribute أكبر من :max.',
        'file'    => 'قد لا يكون حجم الملف :attribute أكبر من :max كيلوبايت.',
        'string'  => 'قد لا يكون طول :attribute أكبر من :max حرفًا.',
        'array'   => 'قد لا يحتوي :attribute على أكثر من :max عنصرًا.',
    ],
    'mimes'                => 'يجب أن يكون :attribute ملف من نوع: :values.',
    'mimetypes'            => 'يجب أن يكون :attribute ملف من نوع: :values.',
    'min'                  => [
        'numeric' => 'يجب أن يكون :attribute على الأقل :min.',
        'file'    => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت.',
        'string'  => 'يجب أن يكون طول :attribute على الأقل :min حرفًا.',
        'array'   => 'يجب أن يحتوي :attribute على الأقل :min عنصرًا.',
    ],
    'not_in'               => ':attribute المحدد غير صالح.',
    'not_regex'            => 'تنسيق :attribute غير صالح.',
    'numeric'              => 'يجب أن يكون :attribute رقمًا.',
    'password'             => 'كلمة المرور غير صحيحة.',
    'present'              => 'حقل :attribute يجب أن يكون موجودًا.',
    'regex'                => 'تنسيق :attribute غير صالح.',
    'required'             => 'حقل :attribute مطلوب.',
    'required_if'          => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_unless'      => 'حقل :attribute مطلوب ما لم يكن :other ضمن :values.',
    'required_with'        => 'حقل :attribute مطلوب عندما يكون :values موجود.',
    'required_with_all'    => 'حقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_without'     => 'حقل :attribute مطلوب عندما لا يكون :values موجود.',
    'required_without_all' => 'حقل :attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same'                 => 'يجب أن يتطابق :attribute و :other.',
    'size'                 => [
        'numeric' => 'يجب أن يكون :attribute :size.',
        'file'    => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت.',
        'string'  => 'يجب أن يكون طول :attribute :size حرفًا.',
        'array'   => 'يجب أن يحتوي :attribute على :size عنصرًا.',
    ],
    'starts_with'          => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values.',
    'string'               => 'يجب أن يكون :attribute نصًا.',
    'timezone'             => 'يجب أن تكون :attribute منطقة زمنية صالحة.',
    'unique'               => 'تم أخذ :attribute بالفعل.',
    'uploaded'             => 'فشل رفع :attribute.',
    'url'                  => 'تنسيق :attribute غير صالح.',
    'uuid'                 => 'يجب أن يكون :attribute UUID صالح.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'رسالة مخصصة',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];
