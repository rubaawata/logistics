<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyPackage;
use App\Models\ThirdPartyApplication;
use Illuminate\Support\Facades\DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use crocodicstudio\crudbooster\controllers\CBController;

class AdminThirdPartyPackagesController extends CBController
{
    public function cbInit()
    {
        # START CONFIGURATION DO NOT REMOVE THIS LINE
        $this->title_field = "id";
        $this->limit = "20";
        $this->orderby = "id,desc";
        $this->sortable_table = true;
        $this->global_privilege = false;
        $this->button_table_action = true;
        $this->button_bulk_action = true;
        $this->button_action_style = "button_icon";
        $this->record_seo = false;
        $this->button_add = true;
        $this->button_edit = true;
        $this->button_delete = true;
        $this->button_detail = true;
        $this->pdf_direction = "ltr";
        $this->button_show = true;
        $this->button_filter = true;
        $this->button_import = false;
        $this->button_export = false;
        $this->page_seo = false;
        $this->table = "third_party_packages";
        # END CONFIGURATION DO NOT REMOVE THIS LINE

        # START COLUMNS DO NOT REMOVE THIS LINE
        $this->col = [];
        $this->col[] = ["label" => "التطبيق", "name" => "third_party_application_id", "join" => "third_party_applications,app_name"];
        $this->col[] = ["label" => "اسم التاجر", "name" => "seller_name"];
        $this->col[] = ["label" => "اسم الزبون", "name" => "customer_name"];
        $this->col[] = ["label" => "سعر التاجر", "name" => "seller_price"];
        $this->col[] = ["label" => "سعر الزبون", "name" => "customer_price"];
        $this->col[] = ["label" => "الخصم", "name" => "discount_amount"];
        $this->col[] = ["label" => "تكلفة التوصيل", "name" => "delivery_cost"];
        $this->col[] = ["label" => "عدد القطع", "name" => "pieces_count"];
        $this->col[] = ["label" => "تاريخ التوصيل", "name" => "delivery_date"];
        $this->col[] = ["label" => "الحالة", "name" => "status", "callback" => function ($row) {
            $statuses = config('constants.PACKAGE_STATUS');
            return $statuses[$row->status] ?? 'حالة غير معروفة';
        }];
        # END COLUMNS DO NOT REMOVE THIS LINE

        # START FORM DO NOT REMOVE THIS LINE
        $this->form = [];
        
        // Third Party Application (will be auto-filled from submodule)
        $this->form[] = ['label' => 'التطبيق', 'name' => 'third_party_application_id', 'type' => 'select2', 'validation' => 'required', 'width' => 'col-sm-10', 'datatable' => 'third_party_applications,app_name'];
        
        // Seller Information Section
        $this->form[] = ['label' => 'معلومات التاجر', 'name' => 'seller_section', 'type' => 'text', 'validation' => 'nullable', 'width' => 'col-sm-12', 'readonly' => true, 'value' => '--- معلومات التاجر ---'];
        $this->form[] = ['label' => 'اسم التاجر', 'name' => 'seller_name', 'type' => 'text', 'validation' => 'required|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'اسم الشركة', 'name' => 'seller_company', 'type' => 'text', 'validation' => 'nullable|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'هاتف التاجر', 'name' => 'seller_phone', 'type' => 'text', 'validation' => 'nullable|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'بريد التاجر', 'name' => 'seller_email', 'type' => 'email', 'validation' => 'nullable|email|max:255', 'width' => 'col-sm-10'];
        
        // Customer Information Section
        $this->form[] = ['label' => 'معلومات الزبون', 'name' => 'customer_section', 'type' => 'text', 'validation' => 'nullable', 'width' => 'col-sm-12', 'readonly' => true, 'value' => '--- معلومات الزبون ---'];
        $this->form[] = ['label' => 'اسم الزبون', 'name' => 'customer_name', 'type' => 'text', 'validation' => 'required|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'هاتف الزبون', 'name' => 'customer_phone', 'type' => 'text', 'validation' => 'required|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'بريد الزبون', 'name' => 'customer_email', 'type' => 'email', 'validation' => 'nullable|email|max:255', 'width' => 'col-sm-10'];
        
        // Pricing Section
        $this->form[] = ['label' => 'الأسعار والخصم', 'name' => 'pricing_section', 'type' => 'text', 'validation' => 'nullable', 'width' => 'col-sm-12', 'readonly' => true, 'value' => '--- الأسعار والخصم ---'];
        $this->form[] = ['label' => 'سعر التاجر', 'name' => 'seller_price', 'type' => 'number', 'validation' => 'required|numeric|min:0', 'width' => 'col-sm-10', 'help' => 'السعر الذي يدفعه التاجر'];
        $this->form[] = ['label' => 'سعر الزبون', 'name' => 'customer_price', 'type' => 'number', 'validation' => 'required|numeric|min:0', 'width' => 'col-sm-10', 'help' => 'السعر الذي يدفعه الزبون'];
        $this->form[] = ['label' => 'سعر القطعة الواحدة', 'name' => 'price_per_piece', 'type' => 'number', 'validation' => 'nullable|numeric|min:0', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'مبلغ الخصم', 'name' => 'discount_amount', 'type' => 'number', 'validation' => 'nullable|numeric|min:0', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'نسبة الخصم (%)', 'name' => 'discount_percentage', 'type' => 'number', 'validation' => 'nullable|numeric|min:0|max:100', 'width' => 'col-sm-10'];
        
        // Shipping Information Section
        $this->form[] = ['label' => 'معلومات الشحن', 'name' => 'shipping_section', 'type' => 'text', 'validation' => 'nullable', 'width' => 'col-sm-12', 'readonly' => true, 'value' => '--- معلومات الشحن ---'];
        $this->form[] = ['label' => 'المنطقة', 'name' => 'area_id', 'type' => 'select2', 'validation' => 'nullable', 'width' => 'col-sm-10', 'datatable' => 'areas,name'];
        $this->form[] = ['label' => 'المندوب', 'name' => 'delivery_id', 'type' => 'select2', 'validation' => 'nullable', 'width' => 'col-sm-10', 'datatable' => 'deliveries,name'];
        $this->form[] = ['label' => 'تكلفة التوصيل', 'name' => 'delivery_cost', 'type' => 'number', 'validation' => 'required|numeric|min:0', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'تاريخ التوصيل', 'name' => 'delivery_date', 'type' => 'date', 'validation' => 'required|date', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'تاريخ الاستلام', 'name' => 'receipt_date', 'type' => 'date', 'validation' => 'nullable|date', 'width' => 'col-sm-10'];
        
        // Address Information
        $this->form[] = ['label' => 'رابط العنوان', 'name' => 'location_link', 'type' => 'text', 'validation' => 'required|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'العنوان', 'name' => 'location_text', 'type' => 'textarea', 'validation' => 'required', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'رقم المبنى', 'name' => 'building_number', 'type' => 'text', 'validation' => 'nullable|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الطابق', 'name' => 'floor_number', 'type' => 'text', 'validation' => 'nullable|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الشقة', 'name' => 'apartment_number', 'type' => 'text', 'validation' => 'nullable|max:255', 'width' => 'col-sm-10'];
        
        // Package Details Section
        $this->form[] = ['label' => 'تفاصيل الشحنة', 'name' => 'package_section', 'type' => 'text', 'validation' => 'nullable', 'width' => 'col-sm-12', 'readonly' => true, 'value' => '--- تفاصيل الشحنة ---'];
        $this->form[] = ['label' => 'صورة الشحنة', 'name' => 'image', 'type' => 'upload', 'validation' => 'nullable|image|max:5000', 'width' => 'col-sm-10', 'help' => 'حد أقصى 5MB'];
        $this->form[] = ['label' => 'وصف الشحنة', 'name' => 'description', 'type' => 'textarea', 'validation' => 'nullable', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'عدد القطع', 'name' => 'pieces_count', 'type' => 'number', 'validation' => 'required|integer|min:1', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'فتح الشحنة', 'name' => 'open_package', 'type' => 'switch', 'validation' => 'nullable', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'ملاحظات', 'name' => 'notes', 'type' => 'textarea', 'validation' => 'nullable', 'width' => 'col-sm-10'];
        
        // Status Section
        $this->form[] = ['label' => 'الحالة', 'name' => 'status_section', 'type' => 'text', 'validation' => 'nullable', 'width' => 'col-sm-12', 'readonly' => true, 'value' => '--- الحالة ---'];
        $this->form[] = ['label' => 'الحالة', 'name' => 'status', 'type' => 'select', 'validation' => 'required', 'width' => 'col-sm-10', 'dataenum' => $this->getPackageStatus()];
        $this->form[] = ['label' => 'المبلغ المحصل', 'name' => 'paid_amount', 'type' => 'number', 'validation' => 'nullable|numeric|min:0', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'جهة تحمّل تكلفة التوصيل', 'name' => 'delivery_fee_payer', 'type' => 'select', 'validation' => 'nullable', 'width' => 'col-sm-10', 'dataenum' => $this->getDeliveryFeePayer()];
        # END FORM DO NOT REMOVE THIS LINE

        $this->sub_module = array();
        $this->addaction = array();
        $this->button_selected = array();
        $this->alert = array();
        $this->index_button = array();
        $this->table_row_color = array();
        $this->index_statistic = array();
        $this->script_js = NULL;
        $this->pre_index_html = null;
        $this->post_index_html = null;
        $this->load_js = array();
        $this->style_css = NULL;
        $this->load_css = array();
    }

    public function hook_before_add(&$postdata)
    {
        // Auto-set third_party_application_id if coming from submodule
        if (request('parent_id') && empty($postdata['third_party_application_id'])) {
            $postdata['third_party_application_id'] = request('parent_id');
        }
        
        // Remove section header fields
        unset($postdata['seller_section']);
        unset($postdata['customer_section']);
        unset($postdata['pricing_section']);
        unset($postdata['shipping_section']);
        unset($postdata['package_section']);
        unset($postdata['status_section']);
        
        // Set delivery_date_1 to delivery_date
        if (!empty($postdata['delivery_date'])) {
            $postdata['delivery_date_1'] = $postdata['delivery_date'];
        }
        
        // Calculate discount if not provided
        if (empty($postdata['discount_amount']) && !empty($postdata['seller_price']) && !empty($postdata['customer_price'])) {
            $postdata['discount_amount'] = $postdata['seller_price'] - $postdata['customer_price'];
        }
        
        // Calculate discount percentage if not provided
        if (empty($postdata['discount_percentage']) && !empty($postdata['seller_price']) && $postdata['seller_price'] > 0 && !empty($postdata['discount_amount'])) {
            $postdata['discount_percentage'] = ($postdata['discount_amount'] / $postdata['seller_price']) * 100;
        }
    }

    public function hook_before_edit(&$postdata, $id)
    {
        // Remove section header fields
        unset($postdata['seller_section']);
        unset($postdata['customer_section']);
        unset($postdata['pricing_section']);
        unset($postdata['shipping_section']);
        unset($postdata['package_section']);
        unset($postdata['status_section']);
        
        // Calculate discount if changed
        if (!empty($postdata['seller_price']) && !empty($postdata['customer_price'])) {
            $postdata['discount_amount'] = $postdata['seller_price'] - $postdata['customer_price'];
            if ($postdata['seller_price'] > 0) {
                $postdata['discount_percentage'] = ($postdata['discount_amount'] / $postdata['seller_price']) * 100;
            }
        }
    }

    public function getPackageStatus()
    {
        $status = config('constants.PACKAGE_STATUS');
        $packageStatus = '';
        foreach ($status as $index => $item) {
            $packageStatus .= $index . '|' . $item;
            if ($index < count($status))
                $packageStatus .= ';';
        }
        return $packageStatus;
    }

    public function getDeliveryFeePayer()
    {
        $deliveryFeePayer = config('constants.DELIVERY_FEE_PAYER');
        $deliveryFeePayerStr = '';
        foreach ($deliveryFeePayer as $index => $item) {
            $deliveryFeePayerStr .= $index . '|' . $item;
            if ($index < count($deliveryFeePayer))
                $deliveryFeePayerStr .= ';';
        }
        return $deliveryFeePayerStr;
    }
}

