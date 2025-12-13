<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\helpers\CB;
use Mpdf\Mpdf;


use App\Models\Package;

class AdminPackagesController extends CBController
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
        $this->table = "packages";
        # END CONFIGURATION DO NOT REMOVE THIS LINE

        # START COLUMNS DO NOT REMOVE THIS LINE
        $this->col = [];
        $this->col[] = ["label" => "البائع", "name" => "seller_id", "join" => "sellers,seller_name"];
        $this->col[] = ["label" => "الزيون", "name" => "customer_id", "join" => "customers,name"];
        $this->col[] = ["label" => "المنطقة", "name" => "area_id", "join" => "areas,name"];
        $this->col[] = ["label" => "المندوب", "name" => "delivery_id", "join" => "deliveries,name"];
        $this->col[] = ["label" => "تكلفة التوصيل", "name" => "delivery_cost"];
        $this->col[] = ["label" => "سعر الشحنة", "name" => "package_cost"];
        $this->col[] = ["label" => "تاريخ التوصيل", "name" => "delivery_date"];
        $this->col[] = ["label" => "تاريخ التوصيل", "name" => "delivery_date"];

        $this->col[] = [
            "label" => "سبب الفشل (مخفي)",
            "name" => "failure_reason",
            "visible" => false
        ];
        $this->col[] = [
            "label" => "تاريخ إعادة الجدولة (مخفي)",
            "name" => "reschedule_date",
            "visible" => false
        ];
        $this->col[] = [
            "label" => "السبب المخصص (مخفي)",
            "name" => "custom_reason",
            "visible" => false
        ];

        $this->col[] = [
            "label" => "الحالة",
            "name" => "status",
            "callback" => function ($row) {
                $statuses = config('constants.PACKAGE_STATUS');

                $status_name = $statuses[$row->status] ?? 'حالة غير معروفة';
                $failure_details = '';

                if ($row->status == 3 && (!empty($row->failure_reason) || !empty($row->custom_reason) || !empty($row->reschedule_date))) {

                    if (!empty($row->failure_reason)) {
                        $failure_details .= ' (السبب: ' .  getReasonMessage($row->failure_reason) . ')';
                    }

                    if (!empty($row->custom_reason)) {
                        $failure_details .= ' | (ملاحظات المندوب: ' . $row->custom_reason . ')';
                    }

                    if (!empty($row->reschedule_date)) {
                        $reschedule_date = date('Y-m-d', strtotime($row->reschedule_date));
                        $failure_details .= ' | (تمت إعادة الجدولة: ' . $reschedule_date . ')';
                    }
                }
                return $status_name . "<br>" . $failure_details;
            }
        ];

        $this->col[] = ["label" => "عدد محاولات التوصيل", "name" => "number_of_attempts"];
        $this->col[] = ["label" => "المبلغ المقبوض", "name" => "paid_amount"];
        $this->col[] = ["label" => "عدد القطع المسلمة", "name" => "delivered_pieces_count"];
        $this->col[] = ["label" => "جهة تحمّل تكلفة التوصيل", "name" => "delivery_fee_payer", "callback" => function ($row) {
            return getDeliveryFeePayer($row->delivery_fee_payer, $row->status, $row->failure_reason);
        }];
        # END COLUMNS DO NOT REMOVE THIS LINE

        # START FORM DO NOT REMOVE THIS LINE
        $this->form = [];
        $this->form[] = ['label' => 'البائع', 'name' => 'seller_id', 'type' => 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'datatable' => 'sellers,seller_name'];
        $this->form[] = ['label' => 'الزبون', 'name' => 'customer_id', 'type' => 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'datatable' => 'customers,name'];
        $this->form[] = ['label' => 'المنطقة', 'name' => 'area_id', 'type' => 'select2', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'datatable' => 'areas,name'];
        $this->form[] = ['label' => 'المندوب', 'name' => 'delivery_id', 'type' => 'select2', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10', 'datatable' => 'deliveries,name'];
        $this->form[] = ['label' => "تكلفة التوصيل", 'name' => 'delivery_cost', 'type' => 'number', 'validation' => 'required|integer|min:0', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'سعر الشحنة', 'name' => 'package_cost', 'type' => 'number', 'validation' => 'required|integer|min:0', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الحالة', 'name' => 'status', 'type' => 'select', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10', 'dataenum' => $this->getPackageStatus()];
        //$this->form[] = ['label' => 'عدد محاولات التوصيل', 'name' => 'number_of_attempts', 'type' => 'number', 'validation' => 'required|integer|min:0', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'تاريخ التوصيل', 'name' => 'delivery_date', 'type' => 'date', 'validation' => 'required|date', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'تاريخ الاستلام', 'name' => 'receipt_date', 'type' => 'date', 'validation' => 'required|date', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'رابط العنوان', 'name' => 'location_link', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'العنوان', 'name' => 'location_text', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10'];

        $this->form[] = ['label' => 'رقم المبنى', 'name' => 'building_number', 'type' => 'text', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الطابق', 'name' => 'floor_number', 'type' => 'text', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الشقة', 'name' => 'apartment_number', 'type' => 'text', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'فتح الشحنة', 'name' => 'open_package', 'type' => 'switch', 'validation' => 'required', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'وصف الشحنة', 'name' => 'description', 'type' => 'text', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'عدد القطع', 'name' => 'pieces_count', 'type' => 'number', 'validation' => 'required', 'width' => 'col-sm-10'];

        $this->form[] = ['label' => 'ملاحظات', 'name' => 'notes', 'type' => 'text', 'validation' => 'min:1', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'المبلغ المحصل من الشحنة', 'name' => 'paid_amount', 'type' => 'number', 'validation' => 'integer|min:0', 'width' => 'col-sm-10'];
        //$this->form[] = ['label' => 'جهة تحمّل تكلفة التوصيل', 'name' => 'delivery_fee_payer', 'type' => 'select', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10', 'dataenum' => $this->getDeliveryFeePayer()];
        # END FORM DO NOT REMOVE THIS LINE

        # OLD START FORM
        //$this->form = [];
        //$this->form[] = ["label"=>"Seller Id","name"=>"seller_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"seller,id"];
        //$this->form[] = ["label"=>"Customer Id","name"=>"customer_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"customer,id"];
        //$this->form[] = ["label"=>"Area Id","name"=>"area_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"area,id"];
        //$this->form[] = ["label"=>"Delivery Cost","name"=>"delivery_cost","type"=>"number","required"=>TRUE,"validation"=>"required|integer|min:0"];
        //$this->form[] = ["label"=>"Status","name"=>"status","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Parent Id","name"=>"parent_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"parent,id"];
        //$this->form[] = ["label"=>"Number Of Attempts","name"=>"number_of_attempts","type"=>"number","required"=>TRUE,"validation"=>"required|integer|min:0"];
        //$this->form[] = ["label"=>"Delivery Date","name"=>"delivery_date","type"=>"date","required"=>TRUE,"validation"=>"required|date"];
        //$this->form[] = ["label"=>"Receipt Date","name"=>"receipt_date","type"=>"date","required"=>TRUE,"validation"=>"required|date"];
        //$this->form[] = ["label"=>"Location Link","name"=>"location_link","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Location Map","name"=>"location_map","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        # OLD END FORM

        /*
	        | ----------------------------------------------------------------------
	        | Sub Module
	        | ----------------------------------------------------------------------
			| @label          = Label of action
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class
			| @parent_columns = Sparate with comma, e.g : name,created_at
	        |
	        */
        $this->sub_module = array();
        $this->sub_module[] = ['path' => 'packages/bill-of-lading/[id]', 'button_color' => 'success', 'button_icon' => 'fa fa-print', 'target' => '_blank'];

        /*
	        | ----------------------------------------------------------------------
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, succecss, info)
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        |
	        */
        $this->addaction = array();


        /*
	        | ----------------------------------------------------------------------
	        | Add More Button Selected
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @icon 	   = Icon from fontawesome
	        | @name 	   = Name of button
	        | Then about the action, you should code at actionButtonSelected method
	        |
	        */
        $this->button_selected = array();


        /*
	        | ----------------------------------------------------------------------
	        | Add alert message to this module at overheader
	        | ----------------------------------------------------------------------
	        | @message = Text of message
	        | @type    = warning,success,danger,info
	        |
	        */
        $this->alert        = array();



        /*
	        | ----------------------------------------------------------------------
	        | Add more button to header button
	        | ----------------------------------------------------------------------
	        | @label = Name of button
	        | @url   = URL Target
	        | @icon  = Icon from Awesome.
	        |
	        */
        $this->index_button = array();



        /*
	        | ----------------------------------------------------------------------
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.
	        |
	        */
        $this->table_row_color = array();


        /*
	        | ----------------------------------------------------------------------
	        | You may use this bellow array to add statistic at dashboard
	        | ----------------------------------------------------------------------
	        | @label, @count, @icon, @color
	        |
	        */
        $this->index_statistic = array();



        /*
	        | ----------------------------------------------------------------------
	        | Add javascript at body
	        | ----------------------------------------------------------------------
	        | javascript code in the variable
	        | $this->script_js = "function() { ... }";
	        |
	        */
        $this->script_js = NULL;


        /*
	        | ----------------------------------------------------------------------
	        | Include HTML Code before index table
	        | ----------------------------------------------------------------------
	        | html code to display it before index table
	        | $this->pre_index_html = "<p>test</p>";
	        |
	        */
        $this->pre_index_html = null;



        /*
	        | ----------------------------------------------------------------------
	        | Include HTML Code after index table
	        | ----------------------------------------------------------------------
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
        $this->post_index_html = null;



        /*
	        | ----------------------------------------------------------------------
	        | Include Javascript File
	        | ----------------------------------------------------------------------
	        | URL of your javascript each array
	        | $this->load_js[] = asset("myfile.js");
	        |
	        */
        $this->load_js = array();



        /*
	        | ----------------------------------------------------------------------
	        | Add css style at body
	        | ----------------------------------------------------------------------
	        | css code in the variable
	        | $this->style_css = ".style{....}";
	        |
	        */
        $this->style_css = NULL;



        /*
	        | ----------------------------------------------------------------------
	        | Include css File
	        | ----------------------------------------------------------------------
	        | URL of your css each array
	        | $this->load_css[] = asset("myfile.css");
	        |
	        */
        $this->load_css = array();
    }


    /*
	    | ----------------------------------------------------------------------
	    | Hook for button selected
	    | ----------------------------------------------------------------------
	    | @id_selected = the id selected
	    | @button_name = the name of button
	    |
	    */
    public function actionButtonSelected($id_selected, $button_name)
    {
        //Your code here

    }


    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate query of index result
	    | ----------------------------------------------------------------------
	    | @query = current sql query
	    |
	    */
    public function hook_query_index(&$query)
    {
        //Your code here

    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate row of index table html
	    | ----------------------------------------------------------------------
	    |
	    */
    public function hook_row_index($column_index, &$column_value)
    {
        //Your code here
    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before add data is execute
	    | ----------------------------------------------------------------------
	    | @arr
	    |
	    */
    public function hook_before_add(&$postdata)
    {
        //Your code here
        $postdata['delivery_date_1'] = $postdata['delivery_date'];
    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after add public static function called
	    | ----------------------------------------------------------------------
	    | @id = last insert id
	    |
	    */
    public function hook_after_add($id)
    {
        //Your code here

    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before update data is execute
	    | ----------------------------------------------------------------------
	    | @postdata = input post data
	    | @id       = current id
	    |
	    */
    public function hook_before_edit(&$postdata, $id)
    {
        //Your code here

    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before update page is open
	    | ----------------------------------------------------------------------
	    | @row = model object
	    | @id  = current id
	    |
	    */
    public function hook_before_get_edit($id, &$row)
    {
        //Your code here

    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
    public function hook_after_edit($id)
    {
        //Your code here

    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
    public function hook_before_delete($id)
    {
        //Your code here

    }

    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
    public function hook_after_delete($id)
    {
        //Your code here

    }



    //By the way, you can still create your own method in here... :)

    public function getBillOfLading($id)
    {

        $package = Package::with(['Seller', 'Customer', 'Area'])->findOrFail($id);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
            'format' => [200, 180],
            'directionality' => 'rtl',
        ]);

        $html = view('pdf.bill-of-lading', ['package' => $package])->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function getIndex()
    {
        $this->cbLoader();

        $module = CRUDBooster::getCurrentModule();
        if (!CRUDBooster::isView() && $this->global_privilege == false) {
            CRUDBooster::insertLog(cbLang('log_try_view', ['module' => $module->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
        }

        if (request('parent_table')) {
            $parentTablePK = CB::pk(g('parent_table'));
            $data['parent_table'] = DB::table(request('parent_table'))->where($parentTablePK, request('parent_id'))->first();
            if (request("parent_translation_table")) {
                $data['parent_table'] = CB::getRowWithTranslations(request('parent_table'), request("parent_translation_table"), request('parent_id'));
            }
            if (request('foreign_key')) {
                $data['parent_field'] = request('foreign_key');
            } else {
                $data['parent_field'] = CB::getTableForeignKey(g('parent_table'), $this->table);
            }

            if ($data['parent_field']) {
                foreach ($this->columns_table as $i => $col) {
                    if ($col['name'] == $data['parent_field']) {
                        unset($this->columns_table[$i]);
                    }
                }
            }
        }
        $data['table'] = $this->table;
        $data['table_pk'] = CB::pk($this->table);
        $data['page_title'] = $module->name;
        $data['page_description'] = cbLang('default_module_description');
        $data['date_candidate'] = $this->date_candidate;
        $data['limit'] = $limit = (request('limit')) ? request('limit') : $this->limit;

        $tablePK = $data['table_pk'];
        $table_columns = CB::getTableColumns($this->table);
        $translationColumns = CB::getTableColumns($this->translation_table);
        $result = DB::table($this->table)->select(DB::raw($this->table . "." . $this->primary_key));

        if (request('parent_id')) {
            $table_parent = $this->table;
            $table_parent = CRUDBooster::parseSqlTable($table_parent)['table'];
            $result->where($table_parent . '.' . request('foreign_key'), request('parent_id'));
        }

        $this->hook_query_index($result);

        if (in_array('deleted_at', $table_columns)) {
            $result->where($this->table . '.deleted_at', null);
        }

        $alias = [];
        $join_alias_count = 0;
        $join_table_temp = [];
        $table = $this->table;
        $columns_table = $this->columns_table;
        $translationTableJoined = false;
        foreach ($columns_table as $index => $coltab) {
            $table = $this->table;

            $join = @$coltab['join'];
            $join_where = @$coltab['join_where'];
            $join_id = @$coltab['join_id'];
            $field = @$coltab['name'];
            if ($coltab["translation"]) {
                $field = $this->translation_table . "." . $field;
                if (!$translationTableJoined) {
                    $join_table_temp[] = $this->translation_table;
                    $result->leftJoin($this->translation_table, function ($join) {
                        $join->on($this->table . '.id', '=', $this->translation_table . '.' . $this->translation_main_column);
                    })
                        ->where($this->translation_table . '.locale', "=", $this->websiteLanguages[0]->code);
                }
                $translationTableJoined = true;
            }
            $join_table_temp[] = $table;

            if (!$field) {
                continue;
            }

            if (strpos($field, ' as ') !== false) {
                $field = substr($field, strpos($field, ' as ') + 4);
                $field_with = (array_key_exists('join', $coltab)) ? str_replace(",", ".", $coltab['join']) : $field;
                $result->addselect(DB::raw($coltab['name']));
                $columns_table[$index]['type_data'] = 'varchar';
                $columns_table[$index]['field'] = $field;
                $columns_table[$index]['field_raw'] = $field;
                $columns_table[$index]['field_with'] = $field_with;
                $columns_table[$index]['is_subquery'] = true;
                continue;
            }

            if (strpos($field, '.') !== false) {
                $result->addselect($field);
            } else {
                $result->addselect($table . '.' . $field);
            }

            $field_array = explode('.', $field);

            if (isset($field_array[1])) {
                $field = $field_array[1];
                $table = $field_array[0];
            } else {
                $table = $this->table;
            }

            if ($join) {
                $join_exp = explode(',', $join);

                $join_table = $join_exp[0];
                $joinTablePK = CB::pk($join_table);
                $join_column = $join_exp[1];
                $join_alias = str_replace(".", "_", $join_table);

                if (in_array($join_table, $join_table_temp)) {
                    $join_alias_count += 1;
                    $join_alias = $join_table . $join_alias_count;
                }

                if (@$coltab['join_translation_table']) {
                    $join_table = @$coltab['join_translation_table'];
                    $joinTablePK = CRUDBooster::getTranslationTableMainColumn($join_table);
                    $join_where .= "$join_alias.locale = '" . $this->websiteLanguages[0]->code . "'";
                }
                $join_table_temp[] = $join_table;
                $result->leftjoin($join_table . ' as ' . $join_alias, $join_alias . (($join_id) ? '.' . $join_id : '.' . $joinTablePK), '=', DB::raw($table . '.' . $field . (($join_where) ? ' AND ' . $join_where . ' ' : '')));
                $result->addselect($join_alias . '.' . $join_column . ' as ' . $join_alias . '_' . $join_column);

                $join_table_columns = CRUDBooster::getTableColumns($join_table);
                if ($join_table_columns) {
                    foreach ($join_table_columns as $jtc) {
                        $result->addselect($join_alias . '.' . $jtc . ' as ' . $join_alias . '_' . $jtc);
                    }
                }

                $alias[] = $join_alias;
                $columns_table[$index]['type_data'] = CRUDBooster::getFieldType($join_table, $join_column);
                $columns_table[$index]['field'] = $join_alias . '_' . $join_column;
                $columns_table[$index]['field_with'] = $join_alias . '.' . $join_column;
                $columns_table[$index]['field_raw'] = $join_column;
                $columns_table[$index]['table'] = $table;

                @$join_table1 = $join_exp[2];
                @$joinTable1PK = CB::pk($join_table1);
                @$join_column1 = $join_exp[3];
                @$join_alias1 = $join_table1;
                if ($join_table1 && $join_column1) {

                    if (in_array($join_table1, $join_table_temp)) {
                        $join_alias_count += 1;
                        $join_alias1 = $join_table1 . $join_alias_count;
                    }

                    $join_table_temp[] = $join_table1;

                    $result->leftjoin($join_table1 . ' as ' . $join_alias1, $join_alias1 . '.' . $joinTable1PK, '=', $join_alias . '.' . $join_column);
                    $result->addselect($join_alias1 . '.' . $join_column1 . ' as ' . $join_column1 . '_' . $join_alias1);
                    $alias[] = $join_alias1;
                    $columns_table[$index]['type_data'] = CRUDBooster::getFieldType($join_table1, $join_column1);
                    $columns_table[$index]['field'] = $join_column1 . '_' . $join_alias1;
                    $columns_table[$index]['field_with'] = $join_alias1 . '.' . $join_column1;
                    $columns_table[$index]['field_raw'] = $join_column1;
                }
            } else {

                if (isset($field_array[1])) {
                    $result->addselect($table . '.' . $field . ' as ' . $table . '_' . $field);
                    $columns_table[$index]['type_data'] = CRUDBooster::getFieldType($table, $field);
                    $columns_table[$index]['field'] = $table . '_' . $field;
                    $columns_table[$index]['field_raw'] = $table . '.' . $field;
                } else {
                    $result->addselect($table . '.' . $field);
                    $columns_table[$index]['type_data'] = CRUDBooster::getFieldType($table, $field);
                    $columns_table[$index]['field'] = $field;
                    $columns_table[$index]['field_raw'] = $field;
                }

                $columns_table[$index]['field_with'] = $table . '.' . $field;
            }
        }
        if (request('q')) {
            $result->where(function ($w) use ($columns_table) {
                foreach ($columns_table as $col) {
                    if (!$col['field_with']) {
                        continue;
                    }
                    if ($col['is_subquery']) {
                        continue;
                    }
                    $w->orwhere($col['field_with'], "like", "%" . request("q") . "%");
                }
            });
        }

        if (request('date')) {
            $date = request('date');

            $result->where('delivery_date', $date);
        }

        if (request('where')) {
            foreach (request('where') as $k => $v) {
                $result->where($table . '.' . $k, $v);
            }
        }

        $filter_is_orderby = false;
        if (request('filter_column')) {

            $filter_column = request('filter_column');
            $result->where(function ($w) use ($filter_column) {
                foreach ($filter_column as $key => $fc) {

                    $value = @$fc['value'];
                    $type = @$fc['type'];

                    if ($type == 'empty') {
                        $w->whereNull($key)->orWhere($key, '');
                        continue;
                    }

                    if ($value == '' || $type == '') {
                        continue;
                    }

                    if ($type == 'between') {
                        continue;
                    }

                    switch ($type) {
                        default:
                            if ($key && $type && $value) {
                                $w->where($key, $type, $value);
                            }
                            break;
                        case 'like':
                        case 'not like':
                            $value = '%' . $value . '%';
                            if ($key && $type && $value) {
                                $w->where($key, $type, $value);
                            }
                            break;
                        case 'in':
                        case 'not in':
                            if ($value) {
                                $value = explode(',', $value);
                                if ($key && $value) {
                                    $w->whereIn($key, $value);
                                }
                            }
                            break;
                    }
                }
            });

            foreach ($filter_column as $key => $fc) {
                $value = @$fc['value'];
                $type = @$fc['type'];
                $sorting = @$fc['sorting'];

                if ($sorting != '') {
                    if ($key) {
                        $result->orderby($key, $sorting);
                        $filter_is_orderby = true;
                    }
                }

                if ($type == 'between') {
                    if ($key && $value) {
                        $result->whereBetween($key, $value);
                    }
                } else {
                    continue;
                }
            }
        }

        if ($filter_is_orderby == true) {
            $data['result'] = $result->paginate($limit);
        } else {
            if ($this->orderby) {
                if (is_array($this->orderby)) {
                    foreach ($this->orderby as $k => $v) {
                        if (strpos($k, '.') !== false) {
                            $orderby_table = explode(".", $k)[0];
                            $k = explode(".", $k)[1];
                        } else {
                            $orderby_table = $this->table;
                        }
                        $result->orderby($orderby_table . '.' . $k, $v);
                    }
                } else {
                    $this->orderby = explode(";", $this->orderby);
                    foreach ($this->orderby as $o) {
                        $o = explode(",", $o);
                        $k = $o[0];
                        $v = $o[1];
                        if (strpos($k, '.') !== false) {
                            $orderby_table = explode(".", $k)[0];
                        } else {
                            $orderby_table = $this->table;
                        }
                        $result->orderby($orderby_table . '.' . $k, $v);
                    }
                }
                $data['result'] = $result->paginate($limit);
            } else {
                $data['result'] = $result->orderby($this->table . '.' . $this->primary_key, 'desc')->paginate($limit);
            }
        }

        $data['columns'] = $columns_table;

        if ($this->index_return) {
            return $data;
        }

        //LISTING INDEX HTML
        $addaction = $this->data['addaction'];

        if ($this->record_seo) {
            $addaction[] = ['label' => cbLang('action_set_seo'), 'url' => CRUDBooster::adminPath('seo') . '?page=' . CRUDBooster::getCurrentModule()->path . '&page_id=[id]', 'icon' => 'fa fa-globe', 'color' => 'success'];
        }

        if ($this->sub_module) {
            foreach ($this->sub_module as $s) {
                $table_parent = CRUDBooster::parseSqlTable($this->table)['table'];
                $addaction[] = [
                    'label' => $s['label'],
                    'icon' => $s['button_icon'],
                    'url' => CRUDBooster::adminPath($s['path']) . '?return_url=' . urlencode(Request::fullUrl()) . '&parent_table=' . $table_parent . '&parent_columns=' . $s['parent_columns'] . '&parent_columns_alias=' . $s['parent_columns_alias'] . '&parent_id=[' . (!isset($s['custom_parent_id']) ? "id" : $s['custom_parent_id']) . ']&foreign_key=' . $s['foreign_key'] . '&label=' . urlencode($s['label']) . '&parent_translation_table=' . $s['parent_translation_table'],
                    'color' => $s['button_color'],
                    'showIf' => $s['showIf'],
                    'target' => isset($s['target']) ?: '_self',
                ];
            }
        }

        $mainpath = CRUDBooster::mainpath();
        $orig_mainpath = $this->data['mainpath'];
        $title_field = $this->title_field;
        $html_contents = [];
        $page = (request('page')) ? request('page') : 1;
        $number = ($page - 1) * $limit + 1;
        foreach ($data['result'] as $row) {
            $html_content = [];

            if ($this->button_bulk_action) {

                $html_content[] = "<input type='checkbox' class='checkbox tbl-checkbox' name='checkbox[]' value='" . $row->{$tablePK} . "'/>";
            }

            if ($this->show_numbering) {
                $html_content[] = $number . '. ';
                $number++;
            }

            foreach ($columns_table as $col) {
                if ($col['visible'] === false) {
                    continue;
                }

                $value = @$row->{$col['field']};
                $title = @$row->{$this->title_field};
                $label = $col['label'];

                if (@$col['str_limit']) {
                    $value = trim(strip_tags($value));
                    $value = substr($value, 0, $col['str_limit']);
                }

                if (isset($col['image'])) {
                    if ($value == '') {
                        $value = "<a  data-lightbox='roadtrip' rel='group_{{$table}}' title='$label: $title' href='" . (CRUDBooster::getSetting('default_img') ? asset(CRUDBooster::getSetting('default_img')) : asset('vendor/crudbooster/avatar.jpg')) . "'><img width='40px' height='40px' src='" . (CRUDBooster::getSetting('default_img') ? asset(CRUDBooster::getSetting('default_img')) : asset('vendor/crudbooster/avatar.jpg')) . "'/></a>";
                    } else {
                        $matched_upload_word = '';
                        if (preg_match('/\w+/', config('crudbooster.filemanager_upload_dir'), $matches)) {
                            $matched_upload_word = $matches[0];
                        }
                        if (!empty($matched_upload_word)) {
                            $new_upload_word = config('crudbooster.filemanager_thumbs_base_path');
                            $new_value = preg_replace('/\b' . $matched_upload_word . '\b/', $new_upload_word, $value, 1);
                            $img_value = preg_replace('/\b' . $matched_upload_word . '\b/', config('crudbooster.filemanager_upload_dir'), $value, 1);
                        }
                        $pic = (strpos($new_value, 'http://') !== false) ? $new_value : asset($new_value);
                        $value = "<a data-lightbox='roadtrip'  rel='group_{{$table}}' title='$label: $title' href='" . $img_value . "'><img width='40px' height='40px' src='" . $pic . "'/></a>";
                    }
                }

                if (@$col['download']) {
                    $url = (strpos($value, 'http://') !== false) ? $value : asset($value) . '?download=1';
                    if ($value) {
                        $value = "<a class='btn btn-xs btn-primary' href='$url' target='_blank' title='Download File'><i class='fa fa-download'></i> Download</a>";
                    } else {
                        $value = " - ";
                    }
                }

                if (@$col['switch']) {
                    $checked = '';
                    if ($value == 1) {
                        $checked = 'checked';
                    }

                    $value = "<input row_id='{$row->id}' id='{$col["name"]}_{$row->id}' class='cms_switch_input' name='{$col["name"]}' type='checkbox' value='{$value}' {$checked} style='display:none;'/>
								<label class='cms_switch_label' for='{$col["name"]}_{$row->id}'>Toggle</label>";
                }

                if ($col['nl2br']) {
                    $value = nl2br($value);
                }

                if ($col['callback_php']) {
                    foreach ($row as $k => $v) {
                        $col['callback_php'] = str_replace("[" . $k . "]", $v, $col['callback_php']);
                    }
                    @eval("\$value = " . $col['callback_php'] . ";");
                }

                //New method for callback
                if (isset($col['callback'])) {
                    $value = call_user_func($col['callback'], $row);
                }

                $datavalue = @unserialize($value);
                if ($datavalue !== false) {
                    if ($datavalue) {
                        $prevalue = [];
                        foreach ($datavalue as $d) {
                            if ($d['label']) {
                                $prevalue[] = $d['label'];
                            }
                        }
                        if ($prevalue && count($prevalue)) {
                            $value = implode(", ", $prevalue);
                        }
                    }
                }

                $html_content[] = $value;
            } //end foreach columns_table

            if ($this->button_table_action) :

                $button_action_style = $this->button_action_style;
                $html_content[] = "<div class='button_action' style='text-align:right'>" . view('crudbooster::components.action', compact('addaction', 'row', 'button_action_style', 'parent_field'))->render() . "</div>";

            endif; //button_table_action

            foreach ($html_content as $i => $v) {
                $this->hook_row_index($i, $v);
                $html_content[$i] = $v;
            }

            $html_contents[] = $html_content;
        } //end foreach data[result]

        $html_contents = ['html' => $html_contents, 'data' => $data['result']];

        $data['html_contents'] = $html_contents;

        $manualView = null;
        if (view()->exists(CRUDBooster::getCurrentModule()->path . '.index')) {
            $manualView = view(CRUDBooster::getCurrentModule()->path . '.index', $data);
        }

        $view = $manualView ?: view("crudbooster::default.index", $data);
        return $view;
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
