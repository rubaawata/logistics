<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyPackageItem;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use crocodicstudio\crudbooster\controllers\CBController;

class AdminThirdPartyPackageItemsController extends CBController
{
    public function cbInit()
    {
        # START CONFIGURATION DO NOT REMOVE THIS LINE
        $this->title_field = "name";
        $this->limit = "20";
        $this->orderby = "sort_order,asc";
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
        $this->table = "third_party_package_items";
        # END CONFIGURATION DO NOT REMOVE THIS LINE

        # START COLUMNS DO NOT REMOVE THIS LINE
        $this->col = [];
        $this->col[] = ["label" => "الاسم", "name" => "name"];
        $this->col[] = ["label" => "الوصف", "name" => "description"];
        $this->col[] = ["label" => "السعر", "name" => "price"];
        $this->col[] = ["label" => "الكمية", "name" => "quantity"];
        $this->col[] = ["label" => "المجموع", "name" => "price", "callback" => function ($row) {
            return number_format($row->price * $row->quantity, 2);
        }];
        # END COLUMNS DO NOT REMOVE THIS LINE

        # START FORM DO NOT REMOVE THIS LINE
        $this->form = [];
        $this->form[] = ['label' => 'الشحنة', 'name' => 'third_party_package_id', 'type' => 'select2', 'validation' => 'required', 'width' => 'col-sm-10', 'datatable' => 'third_party_packages,id'];
        $this->form[] = ['label' => 'اسم القطعة', 'name' => 'name', 'type' => 'text', 'validation' => 'required|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الوصف', 'name' => 'description', 'type' => 'textarea', 'validation' => 'nullable', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'السعر', 'name' => 'price', 'type' => 'number', 'validation' => 'required|numeric|min:0', 'width' => 'col-sm-10', 'step' => '0.01'];
        $this->form[] = ['label' => 'الكمية', 'name' => 'quantity', 'type' => 'number', 'validation' => 'required|integer|min:1', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'ترتيب العرض', 'name' => 'sort_order', 'type' => 'number', 'validation' => 'nullable|integer|min:0', 'width' => 'col-sm-10', 'default' => 0];
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
        // Auto-set package_id if coming from submodule
        if (request('parent_id') && empty($postdata['third_party_package_id'])) {
            $postdata['third_party_package_id'] = request('parent_id');
        }
    }
}

