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
        .summary-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-item {
            display: inline-block;
            margin: 10px 20px;
            padding: 10px 20px;
            background: white;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            color: #333;
        }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
    </style>
@endpush

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">التقرير المالي للأطراف الثالثة</h3>
            </div>
            <form method="GET" id="filterForm">
                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label">الطرف الثالث</label>
                                <div class="input-group">
                                    <select style='width:100%' class='form-control select2' id="third_party_id" name="third_party_id" required>
                                        <option value="">-- اختر الطرف الثالث --</option>
                                        @foreach($thirdParties as $thirdParty)
                                            <option value="{{$thirdParty->id}}" {{$selectedThirdPartyId == $thirdParty->id ? 'selected' : ''}}>
                                                {{$thirdParty->company_name}} ({{$thirdParty->app_name}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label">من تاريخ الاستلام</label>
                                <div class="input-group">
                                    <input type="date" name="date_from" class="form-control" value="{{$dateFrom}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label">إلى تاريخ الاستلام</label>
                                <div class="input-group">
                                    <input type="date" name="date_to" class="form-control" value="{{$dateTo}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2" style="text-align: end; padding-top: 25px;">
                            <button class="btn btn-sm btn-primary" type="submit">بحث</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if($selectedThirdPartyId && ($dateFrom || $dateTo))
    @if($packages->count() > 0)
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">ملخص التقرير</h3>
                    </div>
                    <div class="box-body">
                        <div class="summary-box">
                            <div class="summary-item">
                                <span class="summary-label">عدد الشحنات</span>
                                <span class="summary-value">{{$summary['packages_count']}}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">المبلغ المستحق للطرف الثالث (seller_cost)</span>
                                <span class="summary-value">{{number_format($summary['total_seller_cost'], 2)}} د.أ</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">تكلفة التوصيل (delivery_cost)</span>
                                <span class="summary-value">{{number_format($summary['total_delivery_cost'], 2)}} د.أ</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">المبلغ الصافي</span>
                                <span class="summary-value {{$summary['net_amount'] >= 0 ? 'positive' : 'negative'}}">
                                    {{number_format($summary['net_amount'], 2)}} د.أ
                                </span>
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <form method="POST" action="{{route('third-party-financial-report.export')}}" style="display: inline;">
                                @csrf
                                <input type="hidden" name="third_party_id" value="{{$selectedThirdPartyId}}">
                                <input type="hidden" name="date_from" value="{{$dateFrom}}">
                                <input type="hidden" name="date_to" value="{{$dateTo}}">
                                <button class="btn btn-sm btn-success" type="submit">
                                    <i class="fa fa-file-excel-o"></i> تصدير إلى Excel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">تفاصيل الشحنات</h3>
                    </div>
                    <div class="box-body">
                        <table id="packages-table" class="table table-hover table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>رقم المرجع</th>
                                    <th>اسم العميل</th>
                                    <th>منطقة التوصيل</th>
                                    <th>تاريخ الاستلام</th>
                                    <th>الحالة</th>
                                    <th>عدد القطع</th>
                                    <th>المبلغ المستحق للطرف الثالث</th>
                                    <th>تكلفة التوصيل</th>
                                    <th>المبلغ الصافي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packages as $package)
                                    @php
                                        $sellerCost = $package->seller_cost ?? 0;
                                        $deliveryCost = $package->delivery_cost ?? 0;
                                        $netAmount = $deliveryCost - $sellerCost;
                                    @endphp
                                    <tr>
                                        <td>{{$package->reference_number ?? '-'}}</td>
                                        <td>{{$package->Customer ? $package->Customer->name : '-'}}</td>
                                        <td>{{$package->Area ? $package->Area->name : '-'}}</td>
                                        <td>{{$package->receipt_date ? \Carbon\Carbon::parse($package->receipt_date)->format('Y-m-d') : '-'}}</td>
                                        <td>{{getPackageStatus($package->status, $package->delivery_date) ?? '-'}}</td>
                                        <td>{{$package->pieces_count ?? 0}}</td>
                                        <td>{{number_format($sellerCost, 2)}} د.أ</td>
                                        <td>{{number_format($deliveryCost, 2)}} د.أ</td>
                                        <td class="{{$netAmount >= 0 ? 'text-success' : 'text-danger'}}">
                                            {{number_format($netAmount, 2)}} د.أ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold; background-color: #f5f5f5;">
                                    <td colspan="6" style="text-align: left;">المجموع الكلي</td>
                                    <td>{{number_format($summary['total_seller_cost'], 2)}} د.أ</td>
                                    <td>{{number_format($summary['total_delivery_cost'], 2)}} د.أ</td>
                                    <td class="{{$summary['net_amount'] >= 0 ? 'text-success' : 'text-danger'}}">
                                        {{number_format($summary['net_amount'], 2)}} د.أ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> لا توجد شحنات في الفترة المحددة.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@else
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <div class="box-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> يرجى اختيار الطرف الثالث وتاريخ الاستلام لعرض التقرير.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('bottom')
    <script src='<?php echo asset("vendor/crudbooster/assets/select2/dist/js/select2.full.min.js")?>'></script>
    <script type="text/javascript">
        $(function() {
            $('.select2').select2();
        })
    </script>
@endpush

