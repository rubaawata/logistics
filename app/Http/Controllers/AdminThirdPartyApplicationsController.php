<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyApplication;
use Illuminate\Support\Facades\DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use crocodicstudio\crudbooster\controllers\CBController;

class AdminThirdPartyApplicationsController extends CBController
{
    public function cbInit()
    {
        # START CONFIGURATION DO NOT REMOVE THIS LINE
        $this->title_field = "app_name";
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
        $this->table = "third_party_applications";
        # END CONFIGURATION DO NOT REMOVE THIS LINE

        # START COLUMNS DO NOT REMOVE THIS LINE
        $this->col = [];
        $this->col[] = ["label" => "اسم التطبيق", "name" => "app_name"];
        $this->col[] = ["label" => "اسم الشركة", "name" => "company_name"];
        $this->col[] = ["label" => "البريد الإلكتروني", "name" => "contact_email"];
        $this->col[] = ["label" => "رقم الهاتف", "name" => "contact_phone"];
        $this->col[] = ["label" => "مفتاح API", "name" => "api_key", "callback" => function ($row) {
            return substr($row->api_key, 0, 20) . '...';
        }];
        $this->col[] = ["label" => "الحالة", "name" => "is_active", "callback" => function ($row) {
            return $row->is_active ? '<span class="label label-success">نشط</span>' : '<span class="label label-danger">غير نشط</span>';
        }];
        $this->col[] = ["label" => "الخصم (%)", "name" => "discount"];
        $this->col[] = ["label" => "عدد الطلبات", "name" => "request_count"];
        $this->col[] = ["label" => "آخر استخدام", "name" => "last_used_at"];
        # END COLUMNS DO NOT REMOVE THIS LINE

        # START FORM DO NOT REMOVE THIS LINE
        $this->form = [];
        $this->form[] = ['label' => 'اسم التطبيق', 'name' => 'app_name', 'type' => 'text', 'validation' => 'required|min:1|max:255|unique:third_party_applications,app_name', 'width' => 'col-sm-10', 'placeholder' => 'مثال: Lakta App'];
        $this->form[] = ['label' => 'اسم الشركة', 'name' => 'company_name', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'البريد الإلكتروني', 'name' => 'contact_email', 'type' => 'email', 'validation' => 'required|email|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'رقم الهاتف', 'name' => 'contact_phone', 'type' => 'text', 'validation' => 'nullable|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الوصف', 'name' => 'description', 'type' => 'textarea', 'validation' => 'nullable', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'رابط Webhook', 'name' => 'webhook_url', 'type' => 'text', 'validation' => 'nullable|url|max:512', 'width' => 'col-sm-10', 'placeholder' => 'https://example.com/webhook'];
        $this->form[] = ['label' => 'العناوين المسموحة (IP)', 'name' => 'allowed_ips', 'type' => 'text', 'validation' => 'nullable', 'width' => 'col-sm-10', 'placeholder' => 'مفصولة بفواصل: 192.168.1.1,10.0.0.1', 'help' => 'اتركه فارغاً للسماح بجميع العناوين'];
        $this->form[] = ['label' => 'الخصم (%)', 'name' => 'discount', 'type' => 'number', 'validation' => 'nullable|numeric|min:0|max:100', 'width' => 'col-sm-10', 'placeholder' => '0-100', 'help' => 'نسبة الخصم من 0 إلى 100', 'default' => 0];
        $this->form[] = ['label' => 'نسبة تحمل الإلغاء (%)', 'name' => 'cancellation_fee_percentage', 'type' => 'number', 'validation' => 'nullable|numeric|min:0|max:100', 'width' => 'col-sm-10', 'placeholder' => '0-100', 'help' => 'نسبة من 0 إلى 100', 'default' => 0];
        $this->form[] = ['label' => 'نشط', 'name' => 'is_active', 'type' => 'switch', 'validation' => 'required', 'width' => 'col-sm-10'];
        # END FORM DO NOT REMOVE THIS LINE

        # OLD START FORM
        # OLD END FORM

        $this->sub_module = array();
        $this->sub_module[] = ['label' => 'الشحنات', 'path' =>'packages', 'foreign_key' => 'third_party_application_id', 'button_color' => 'primary', 'button_icon' => 'fa fa-cube', 'parent_columns' => 'app_name'];

        $this->addaction = array();
        $this->addaction[] = [
            'label' => 'عرض مفاتيح API',
            'url' => CRUDBooster::mainpath('view-api-credentials/[id]'),
            'icon' => 'fa fa-eye',
            'color' => 'info',
        ];
        $this->addaction[] = [
            'label' => 'إعادة توليد مفتاح API',
            'url' => CRUDBooster::mainpath('regenerate-api-key/[id]'),
            'icon' => 'fa fa-key',
            'color' => 'warning',
        ];

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
        // Generate API key and secret when creating new application
        $postdata['api_key'] = ThirdPartyApplication::generateApiKey();
        $postdata['api_secret'] = ThirdPartyApplication::generateApiSecret();
        
        // Convert allowed_ips string to array
        if (!empty($postdata['allowed_ips'])) {
            $ips = array_map('trim', explode(',', $postdata['allowed_ips']));
            $postdata['allowed_ips'] = json_encode(array_filter($ips));
        } else {
            $postdata['allowed_ips'] = null;
        }
    }

    public function hook_before_edit(&$postdata, $id)
    {
        // Convert allowed_ips string to array
        if (!empty($postdata['allowed_ips'])) {
            $ips = array_map('trim', explode(',', $postdata['allowed_ips']));
            $postdata['allowed_ips'] = json_encode(array_filter($ips));
        } else {
            $postdata['allowed_ips'] = null;
        }
    }

    public function hook_before_get_edit($id, &$row)
    {
        // Convert allowed_ips array to comma-separated string for editing
        if (!empty($row->allowed_ips)) {
            $row->allowed_ips = is_array($row->allowed_ips) ? implode(', ', $row->allowed_ips) : $row->allowed_ips;
        }
    }

    public function hook_after_add($id)
    {
        // Log the creation
        $app = ThirdPartyApplication::findOrFail($id);
        CRUDBooster::insertLog('Third Party Application Created: ' . $app->app_name);
    }

    public function hook_query_index(&$query)
    {
        // You can add custom query logic here if needed
    }

    public function hook_row_index($column_index, &$column_value)
    {
        // You can customize row display here if needed
    }

    public function getViewApiCredentials($id)
    {
        $this->cbLoader();
        
        $app = ThirdPartyApplication::findOrFail($id);
        
        $data = [];
        $data['page_title'] = 'مفاتيح API - ' . $app->app_name;
        $data['app'] = $app;
        $data['mainpath'] = CRUDBooster::mainpath();
        
        return view('api_credentials', $data);
    }

    public function getRegenerateApiKey($id)
    {
        $app = ThirdPartyApplication::findOrFail($id);
        $oldApiKey = $app->api_key;
        $app->api_key = ThirdPartyApplication::generateApiKey();
        $app->api_secret = ThirdPartyApplication::generateApiSecret();
        $app->save();

        CRUDBooster::insertLog('API Key Regenerated for: ' . $app->app_name);
        
        // Show new credentials
        session()->flash('api_credentials', [
            'api_key' => $app->api_key,
            'api_secret' => $app->api_secret,
            'app_name' => $app->app_name,
            'regenerated' => true,
        ]);

        CRUDBooster::redirect(CRUDBooster::mainpath('view-api-credentials/' . $id), 'تم إعادة توليد مفتاح API بنجاح. يرجى حفظ المفاتيح الجديدة.', 'success');
    }
}

