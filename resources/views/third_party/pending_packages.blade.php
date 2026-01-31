<?php   
    $status = config('constants.PACKAGE_STATUS');
    
?> 
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
            <form method="GET" id="dateForm">
                <div class="box-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group form-datepicker header-group-0 " id="form-group-area_id" style="">
                                <label class="control-label col-sm-4">
                                    رقم الشحنة
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group" style="width: 100%">
                                        <input type="text" title="ID"  class="form-control notfocus" name="package_id" id="package_id" value="{{$selected_package_id}}">
                                    </div>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group form-datepicker header-group-0 " id="form-group-area_id" style="">
                                <label class="control-label col-sm-4">
                                    النظام المتعاقد
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group" style='width:100%'>
                                        <select style='width:100%' class='form-control select2' id="third_party_application_id" name="third_party_application_id">
                                            <option value="null">الكل</option>
                                            @foreach ($third_party_applications as $item)
                                                <option {{$selected_third_party_application_id == $item->id ? 'selected' : ''}} value="{{$item->id}}">{{$item->app_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
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
                            <th>رقم الشحنة</th>
                            <th>اسم البائع</th>
                            <th>رقم هاتف البائع</th>
                            <th>عنوان البائع</th>
                            <th>موقع البائع</th>
                            <th>المبلغ الذي يجب أن يستلمه البائع</th>
                            <th>كلفة التوصيل</th>
                            <th>سعر الشحنة</th>
                            <th>المنطقة</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $item)
                            <tr>
                                <td>{{$item['id']}}</td>
                                <td>{{$item['seller']['seller_name']}}</td>
                                <td>{{$item['seller']['phone_number']}}</td>
                                <td>{{$item['seller']['location_text_1']}}</td>
                                <td>{{$item['seller']['location_link_1']}}</td>
                                <td>{{$item['seller_cost']}}</td>
                                <td>{{$item['delivery_cost']}}</td>
                                <td>{{$item['package_cost']}}</td>
                                <td>{{$item['area']['name']}}</td>
                                <td>
                                    <button class="btn btn-xs btn-success btn-edit" onclick="confirmPackageReceivedModal({{$item['id']}})">تأكيد استلام الشحنة</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="confirmPackageReceivedModal" tabindex="-1" aria-labelledby="confirmPackageReceivedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmPackageReceivedModalLabel">تأكيد استلام الشحنة</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" name="package_id" id="package_id">
                <div class="mb-3">
                    <label for="delivery_id" class="form-label">اختر تاريخ توصيل الشحنة</label>
                    <div class="input-group">
                        <span class="input-group-addon open-datetimepicker"><a><i class="fa fa-calendar "></i></a></span>
                        <input type="text" title="Delivery Date" readonly="" required="" class="form-control notfocus input_date" name="delivery_date" id="delivery_date" value="{{now()->format('d/m/Y')}}">
                    </div>
                </div>
                <div id="responseMessageStatus" class="text-success small"></div>
                <div id="responseErrorStatus" class="text-danger small"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="confirmPackageReceived()">تأكيد</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeModal('confirmPackageReceivedModal')">الغاء</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('bottom')
    <script src='<?php echo asset("vendor/crudbooster/assets/select2/dist/js/select2.full.min.js")?>'></script>
    <script>
        $(document).ready(function () {

            $('#packages-table').DataTable({
                searching: false,
                lengthChange: false,
                pageLength: 30,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                }
            });

            $('.open-datetimepicker').click(function () {
                $(this).next('.input_date').datepicker('show');
            });

            $('.open-datetimepicker1').click(function () {
                $(this).next('.input_date').datepicker('show');
            });

            /*$('#date').on('change', function () {
                $('#dateForm').submit();
            });*/
        });
        
        function closeModal(modal_id) {
            $('#' + modal_id).modal('hide');
        }

        function showSpinner() {
            $('.main-overlay').css('display', 'block');
            $('.spinner-loader').css('display', 'block');
        }

        function hideSpinner() {
            $('.main-overlay').css('display', 'none');
            $('.spinner-loader').css('display', 'none');
        }
    </script>

    <script>

        function confirmPackageReceivedModal(package_id) {
            $('#package_id').val(package_id);
            $('#responseMessageStatus').text('');
            $('#responseErrorStatus').text('');
            $('#confirmPackageReceivedModal').modal('show');
        }

        function confirmPackageReceived() {
            showSpinner();
            package_id = document.getElementById('package_id').value;
            delivery_date = document.getElementById('delivery_date').value;
            $.ajax({
                url: '/admin/confirm-package-received',
                type: 'POST',
                data: {
                    package_id: package_id,
                    delivery_date: delivery_date,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    document.getElementById('responseMessageStatus').innerHTML = "تم تأكيد الشحنة بنجاح";
                    hideSpinner();
                    setTimeout(function() {
                        location.reload(); // reloads the current page
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    document.getElementById('responseErrorStatus').innerHTML = "حدثت مشكلة ما";
                    hideSpinner();
                }
            });
        }
    </script>
@endpush
