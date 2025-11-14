@extends('crudbooster::admin_template')

@push('head')
    <link rel='stylesheet' href='<?php echo asset("vendor/crudbooster/assets/select2/dist/css/select2.min.css")?>'/>
    <style type="text/css">
        .select2-container--default .select2-selection--single {
            border-radius: 0px !important
        }

        .select2-container .select2-selection--single {
            height: 35px
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3c8dbc !important;
            border-color: #367fa9 !important;
            color: #fff !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
        }
    </style>
@endpush

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="box">
            <form method="GET" id="monthForm">
                <div class="box-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group form-datepicker header-group-0 " id="form-group-delivery_date" style="">
                                <label class="control-label col-sm-4">
                                    الأسبوع الذي ينتهي في يوم الخميس
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group" style='width:100%'>
                                        <select style='width:100%' class='form-control select2' id="week" name="week">
                                            @foreach ($thursdays as $item)
                                                <option {{$selectedThursday == $item ? 'selected' : ''}} value="{{$item}}">{{$item}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6" style="text-align: end">
                            <button class="btn btn-sm btn-success btn-edit" type="submit">بحث</button>
                        </div>
                    </div>
                </div> 
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="box">
            <div class="box-body">
                <table id="packages-table"  class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>اسم المندوب</th>
                            <th>عدد الشحنات الكلي</th>
                            <th>عدد الشحنات الموصلة</th>
                            <th>عدد الشحنات الغير الموصلة</th>
                            <th>مبلغ التوصيل الكلي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveries as $item)
                            <tr>
                                <td>{{$item['name']}}</td>
                                <td>{{$item['total_packages']}}</td>
                                <td>{{$item['total_delivered_packages']}}</td>
                                <td>{{$item['total_none_delivered_packages']}}</td>
                                <td>{{$item['total_amount']}}</td>
                                <!--<td>
                                    <a class="btn btn-xs btn-success btn-edit" href="admin/reports/packages-count-report/{{$item['id']}}/{{$selected_month}}" target="_blank">التفاصيل</a>
                                </td>-->
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('bottom')

    <script src='<?php echo asset("vendor/crudbooster/assets/select2/dist/js/select2.full.min.js")?>'></script>
    <script type="text/javascript">
        $(function() {
            $('.select2').select2();
        })
    </script>
@endpush
