<?php
namespace App\Http\Controllers;

use App\Exports\DeliveryReportExport;
use App\Models\Delivery;
use App\Models\Package;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use crocodicstudio\crudbooster\controllers\CBController;
use Illuminate\Support\Facades\Hash;
use Mpdf\Mpdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminDeliveriesController extends CBController
{

    public function cbInit()
    {

        # START CONFIGURATION DO NOT REMOVE THIS LINE
        $this->title_field = "personal_photo";
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
        $this->table = "deliveries";
        # END CONFIGURATION DO NOT REMOVE THIS LINE

        # START COLUMNS DO NOT REMOVE THIS LINE
        $this->col = [];
        $this->col[] = ["label" => "الاسم", "name" => "name"];
        $this->col[] = ["label" => "الموبايل", "name" => "phone_number"];
        $this->col[] = ["label" => "موبايل القريب الأول", "name" => "relative_phone_number_1"];
        $this->col[] = ["label" => "موبايل القريب الثاني", "name" => "relative_phone_number_2"];
        $this->col[] = ["label" => "كلمة السر", "name" => "password", "callback_php" => 'str_repeat("*", 8)'];

        # END COLUMNS DO NOT REMOVE THIS LINE

        # START FORM DO NOT REMOVE THIS LINE
        $this->form = [];
        $this->form[] = ['label' => 'الاسم', 'name' => 'name', 'type' => 'text', 'validation' => 'required|string|min:3|max:70', 'width' => 'col-sm-10', 'placeholder' => 'You can only enter the letter only'];
        $this->form[] = ['label' => 'الموبايل', 'name' => 'phone_number', 'type' => 'number', 'validation' => 'required|numeric', 'width' => 'col-sm-10', 'placeholder' => 'You can only enter the number only'];
        $this->form[] = ['label' => "موبايل القريب الأول", 'name' => 'relative_phone_number_1', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => "موبايل القريب الثاني", 'name' => 'relative_phone_number_2', 'type' => 'text', 'validation' => 'required|min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'الصورة الشخصية', 'name' => 'personal_photo', 'type' => 'filemanager', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'صورة الضمانات', 'name' => 'trust_receipt_photo', 'type' => 'filemanager', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'صورة الهوية', 'name' => 'id_photo', 'type' => 'filemanager', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'شهادة السواقة', 'name' => 'driver_licence_photo', 'type' => 'filemanager', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = ['label' => 'رخصة العربية', 'name' => 'vehicle_licence_photo', 'type' => 'filemanager', 'validation' => 'min:1|max:255', 'width' => 'col-sm-10'];
        $this->form[] = [
            'label' => 'كلمة السر',
            'name' => 'password',
            'type' => 'password',
            'validation' => 'required|string|min:6',
            'width' => 'col-sm-10',
            'placeholder' => 'Enter a secure password'
        ];

        # END FORM DO NOT REMOVE THIS LINE

        # OLD START FORM
        //$this->form = [];
        //$this->form[] = ["label"=>"Phone Number","name"=>"phone_number","type"=>"number","required"=>TRUE,"validation"=>"required|numeric","placeholder"=>"You can only enter the number only"];
        //$this->form[] = ["label"=>"Relative Phone Number 1","name"=>"relative_phone_number_1","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Relative Phone Number 2","name"=>"relative_phone_number_2","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Personal Photo","name"=>"personal_photo","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Trust Receipt Photo","name"=>"trust_receipt_photo","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Photo","name"=>"id_photo","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"photo,id"];
        //$this->form[] = ["label"=>"Driver Licence Photo","name"=>"driver_licence_photo","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Vehicle Licence Photo","name"=>"vehicle_licence_photo","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
        //$this->form[] = ["label"=>"Name","name"=>"name","type"=>"text","required"=>TRUE,"validation"=>"required|string|min:3|max:70","placeholder"=>"You can only enter the letter only"];
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
        $this->addaction[] = [
            'label'=>'تصدير PDF ',
            'url'   => CRUDBooster::mainpath('export-delivery-report/[id]'),
            'icon' => 'fa fa-file-pdf-o',
            'color' => 'success',
            'target' => '_blank',
        ];

        $this->addaction[] = [
            'label'=>'تصدير Excel',
            'url'   => CRUDBooster::mainpath('export-delivery-report-excel/[id]'),
            'icon'  => 'fa fa-file-excel-o',
            'color' => 'success',
            'target' => '_blank',
        ];

        $this->addaction[] = [
            'label'=>'مشاهدة فقط',
            'url'   => 'export-delivery-report/[id]',
            'icon'  => 'fa fa-file-excel-o',
            'color' => 'success',
            'target' => '_blank',
        ];

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
        if (empty($postdata['password'])) {
            unset($postdata['password']);
        }
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

    public function getExportDeliveryReport($id)
    {
        $delivery = Delivery::where('id', '=', $id)->first();

        $package = Package::where('delivery_id', $delivery->id)
        ->where(function ($query) {
            $today = today()->toDateString();

            $query->whereDate('delivery_date', $today)
                ->orWhereDate('delivery_date_1', $today)
                ->orWhereDate('delivery_date_2', $today)
                ->orWhereDate('delivery_date_3', $today);
        })
        ->with(['Customer', 'Seller'])
        ->get();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'dejavusans',
            'format' => [200, 180],
            'directionality' => 'rtl',
        ]);
        $data = [
            'packages' => $package,
            'delivery_name' => $delivery->name,
            'report_date' => now()->format('Y-m-d'),
        ];


        $html = view('pdf.delivery_report', $data)->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function getExportDeliveryReportExcel($id)
    {
        $delivery = Delivery::findOrFail($id);
        
        $filename = 'تقرير_التوصيل_' . $delivery->name . '_' . now()->format('Y-m-d') . '.xlsx';
    
        return Excel::download(
            new DeliveryReportExport($delivery->id, $delivery->name), 
            $filename
        );
    }
}
