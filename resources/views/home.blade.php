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
                            <div class="form-group form-datepicker header-group-0 " id="form-group-delivery_date" style="">
                                <label class="control-label col-sm-4">
                                     التاريخ
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <span class="input-group-addon open-datetimepicker"><a><i class="fa fa-calendar "></i></a></span>
                                        <input type="text" title="Date" readonly="" required="" class="form-control notfocus input_date" name="date" id="date" value="{{$selected_date}}">
                                    </div>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group form-datepicker header-group-0 " id="form-group-area_id" style="">
                                <label class="control-label col-sm-4">
                                    المنطقة
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group" style='width:100%'>
                                        <select style='width:100%' class='form-control select2' id="area_id" name="area_id">
                                            <option value="null">الكل</option>
                                            @foreach ($areas as $item)
                                                <option {{$selected_area == $item->id ? 'selected' : ''}} value="{{$item->id}}">{{$item->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                    المندوب
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group" style='width:100%'>
                                        <select style='width:100%' class='form-control select2' id="delivery_id" name="delivery_id">
                                            <option value="null">الكل</option>
                                            @foreach ($deliveries as $item)
                                                <option {{$selected_delivery == $item->id ? 'selected' : ''}} value="{{$item->id}}">{{$item->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group form-datepicker header-group-0 " id="form-group-area_id" style="">
                                <label class="control-label col-sm-4">
                                    البائع
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group" style='width:100%'>
                                        <select style='width:100%' class='form-control select2' id="seller_id" name="seller_id">
                                            <option value="null">الكل</option>
                                            @foreach ($sellers as $item)
                                                <option {{$selected_seller == $item->id ? 'selected' : ''}} value="{{$item->id}}">{{$item->seller_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="help-block"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group form-datepicker header-group-0 " id="form-group-area_id" style="">
                                <label class="control-label col-sm-4">
                                    الزبون
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group" style='width:100%'>
                                        <select style='width:100%' class='form-control select2' id="customer_id" name="customer_id">
                                            <option value="null">الكل</option>
                                            @foreach ($customers as $item)
                                                <option {{$selected_customer == $item->id ? 'selected' : ''}} value="{{$item->id}}">{{$item->name}}</option>
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
                            <th>رقم هاتف الزبون</th>
                            <th>المندوب</th>
                            <th>المنطقة</th>
                            <th>العنوان</th>
                            <th>الحالة</th>
                            <th>كلفة الشحن</th>
                            <th>سعر الشحنة</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $item)
                            <tr>
                                <td>{{$item['id']}}</td>
                                <td>{{$item['seller']['seller_name']}}</td>
                                <td>{{$item['customer']['phone_number']}}</td>
                                <td>{{$item['delivery']['name']}}</td>
                                <td>{{$item['area']['name']}}</td>
                                <td>{{$item['location_text']}}</td>
                                <td>{{$item['status']}}</td>
                                <td>{{$item['delivery_cost']}}</td>
                                <td>{{$item['package_cost']}}</td>
                                <td>
                                    <button class="btn btn-xs btn-success btn-edit" onclick="updatePackageStatusModal({{$item['id']}}, '{{$item['status']}}')">تعديل الحالة</button>
                                    <button class="btn btn-xs btn-warning btn-edit" onclick="updatePackageDeliveryInfoModal({{$item['id']}}, '{{$item['location_text']}}', '{{$item['location_link']}}', '{{$item['delivery_date']}}')">تعديل الشحنة</button>
                                    <button class="btn btn-xs btn-primary btn-edit" onclick="updatePackageDeliveryModal({{$item['id']}}, '{{$item['delivery_id']}}')">تعديل المندوب</button>
                                    <a class="btn btn-xs btn-success btn-edit" href="admin/packages/bill-of-lading/{{$item['id']}}" target="_blank">البوليصة</a>
                                    <button class="btn btn-xs btn-primary btn-edit" onclick="redirectToWhatsApp('{{$item['customer']['phone_number']}}')">واتساب</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="box">
            <div class="box-body">
                <table id="delivery-table"  class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>اسم المندوب</th>
                            <th>رقم الهاتف</th>
                            <th>عدد الشحنات الموصلة</th>
                            <th>عدد الشحنات الغير الموصلة</th>
                            <th>المبلغ الكلي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delivery_workers as $item)
                            <tr>
                                <td>{{$item['name']}}</td>
                                <td>{{$item['phone_number']}}</td>
                                <td>{{$item['delivered_package_count']}}</td>
                                <td>{{$item['undelivered_package_count']}}</td>
                                <td>{{$item['total_amount']}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">تعديل حالة الشحنة</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" name="package_id_status" id="package_id_status">
                <div class="mb-3">
                    <label for="new_status" class="form-label">حالة جديدة</label>
                    <select class="form-control" id="new_status" data-value="" required="" name="new_status">
                        <option value="">** Please select a Status</option>
                        <option value="Delivered">موصلة</option>
                        <option value="Pending">بالانتظار</option>
                        <option value="RTO">RTO</option>
                        <option value="Changed">معدلة</option>
                        <option value="Canceled">ملغاة</option>        
                    </select>
                    
                </div>
                <div id="responseMessageStatus" class="text-success small"></div>
                <div id="responseErrorStatus" class="text-danger small"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="updatePackageStatus()">تعديل</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeModal('statusModal')">الغاء</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="deliveryModal" tabindex="-1" aria-labelledby="deliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryModalLabel">تعديل المندوب</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" name="package_id_delivery" id="package_id_delivery">
                <div class="mb-3">
                    <label for="delivery_id" class="form-label">اختر مندوب جديد</label>
                    <select class="form-control" id="delivery_id" data-value="" required="" name="delivery_id">
                        <option value="">** رجاءً اختر مندوب</option>
                        @foreach ($deliveries as $item)
                            <option value="{{$item['id']}}">{{$item['name']}}</option>
                        @endforeach      
                    </select>
                    
                </div>
                <div id="responseMessageDelivery" class="text-success small"></div>
                <div id="responseErrorDelivery" class="text-danger small"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="updatePackageDelivery()">تعديل</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeModal('deliveryModal')">إلغاء</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deliveryInfoModal" tabindex="-1" aria-labelledby="deliveryInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryInfoModalLabel">تعديل معلومات الشحنة</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" name="package_id_delivery_info" id="package_id_delivery_info">
                <div class="mb-3">
                    <label for="delivery_id" class="form-label">اختر تاريخ الشحنة</label>
                    <div class="input-group">
                        <span class="input-group-addon open-datetimepicker"><a><i class="fa fa-calendar "></i></a></span>
                        <input type="text" title="Delivery Date" readonly="" required="" class="form-control notfocus input_date" name="delivery_date" id="delivery_date" value="{{$selected_date}}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="location_text" class="form-label">عنوان الشحنة</label>
                    <input type="text" title="Location Text" required="" maxlength="255" class="form-control" name="location_text" id="location_text" value="" spellcheck="false" data-ms-editor="true">
                </div>
                <div class="mb-3">
                    <label for="location_link" class="form-label">رابط عنوان الشحنة</label>
                    <input type="text" title="Location Link" required="" maxlength="255" class="form-control" name="location_link" id="location_link" value="" spellcheck="false" data-ms-editor="true">
                </div>
                <div id="responseMessageDeliveryInfo" class="text-success small"></div>
                <div id="responseErrorDeliveryInfo" class="text-danger small"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" onclick="updatePackageDeliveryInfo()">تعديل</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeModal('deliveryInfoModal')">إلغاء</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('bottom')
    <script src='<?php echo asset("vendor/crudbooster/assets/select2/dist/js/select2.full.min.js")?>'></script>
    <script>
        $(document).ready(function () {
            $('#delivery-table').DataTable({
                searching: false,
                lengthChange: false,
                pageLength: 30,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                }
            });

            $('#packages-table').DataTable({
                searching: false,
                lengthChange: false,
                pageLength: 30,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                }
            });

            $('.input_date').datepicker({
                dateFormat: 'yy-mm-dd'
            });

            $('.open-datetimepicker').click(function () {
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

        function updatePackageStatusModal(package_id, old_status) {
            $('#package_id_status').val(package_id);
            $('#responseMessageStatus').text('');
            $('#responseErrorStatus').text('');
            $('#new_status').val(old_status);
            $('#statusModal').modal('show');
        }

        function updatePackageStatus() {
            showSpinner();
            package_id = document.getElementById('package_id_status').value;
            new_status = document.getElementById('new_status').value;
            if(new_status == '') {
                document.getElementById('responseErrorStatus').innerHTML = "Please Select Status";
                return;
            }
            $.ajax({
                url: '/admin/update-package-status',
                type: 'POST',
                data: {
                    package_id: package_id,
                    new_status: new_status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    document.getElementById('responseMessageStatus').innerHTML = "The status has been updated successfully";
                    hideSpinner();
                    setTimeout(function() {
                        location.reload(); // reloads the current page
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    document.getElementById('responseErrorStatus').innerHTML = "Something wrong, please try again";
                    hideSpinner();
                }
            });
        }
    </script>

    <script>
        function updatePackageDeliveryModal(package_id, old_delivery) {
            $('#package_id_delivery').val(package_id);
            $('#responseMessageDelivery').text('');
            $('#responseErrorDelivery').text('');
            $('#delivery_id').val(old_delivery);
            $('#deliveryModal').modal('show');
        }

        function updatePackageDelivery() {
            showSpinner();
            package_id = document.getElementById('package_id_delivery').value;
            delivery_id = document.getElementById('delivery_id').value;
            $.ajax({
                url: '/admin/update-package-delivery',
                type: 'POST',
                data: {
                    package_id: package_id,
                    delivery_id: delivery_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    document.getElementById('responseMessageDelivery').innerHTML = "The delivery has been updated successfully";
                    hideSpinner();
                    setTimeout(function() {
                        location.reload(); // reloads the current page
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    document.getElementById('responseErrorDelivery').innerHTML = "Something wrong, please try again";
                    hideSpinner();
                }
            });
        }
    </script>

    <script>
        function updatePackageDeliveryInfoModal(package_id, location_text, location_link, delivery_date) {
            $('#package_id_delivery_info').val(package_id);
            $('#responseMessageDeliveryInfo').text('');
            $('#responseErrorDeliveryInfo').text('');
            $('#location_text').val(location_text);
            $('#location_link').val(location_link);
            $('#deliveryInfoModal').modal('show');
        }

        function updatePackageDeliveryInfo() {
            showSpinner();
            package_id = document.getElementById('package_id_delivery_info').value;
            location_text = document.getElementById('location_text').value;
            location_link = document.getElementById('location_link').value;
            delivery_date = document.getElementById('delivery_date').value;
            $.ajax({
                url: '/admin/update-package-delivery-info',
                type: 'POST',
                data: {
                    package_id: package_id,
                    location_text: location_text,
                    location_link: location_link,
                    delivery_date: delivery_date,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    document.getElementById('responseMessageDeliveryInfo').innerHTML = "The data has been updated successfully";
                    hideSpinner();
                    setTimeout(function() {
                        location.reload(); // reloads the current page
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    document.getElementById('responseErrorDeliveryInfo').innerHTML = "Something wrong, please try again";
                    hideSpinner();
                }
            });
        }
    </script>

    <script>
        function redirectToWhatsApp(phoneNumber) {
            phoneNumber = '+963' + phoneNumber;
            const message = 'مرحبا! سيصل مندوب التوصيل قريبا'; // Optional pre-filled message
            const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            
            window.open(url, '_blank');
        }
    </script>

    <script type="text/javascript">
        $(function() {
            $('.select2').select2();
        })
    </script>
@endpush
