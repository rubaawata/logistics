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
                                <label class="control-label">من تاريخ (الإنشاء أو التوصيل)</label>
                                <div class="input-group">
                                    <input type="date" name="date_from" class="form-control" value="{{$dateFrom}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="control-label">إلى تاريخ (الإنشاء أو التوصيل)</label>
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
                        @if($thirdParty)
                            <div class="alert alert-info">
                                <strong>نسبة الخصم للطرف الثالث:</strong> {{number_format($thirdParty->discount ?? 0, 2)}}%
                            </div>
                        @endif
                        <div class="summary-box">
                            <div class="summary-item">
                                <span class="summary-label">عدد الشحنات</span>
                                <span class="summary-value">{{$summary['packages_count']}}</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">المبلغ المستحق دفعه للتجار (seller_cost)</span>
                                <span class="summary-value">{{number_format($summary['total_seller_cost'], 2)}} </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">المبلغ المستحق من العملاء (package_cost + delivery_cost)</span>
                                <span class="summary-value">{{number_format($summary['total_should_receive'], 2)}} </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">المبلغ المستلم فعلياً من العملاء (paid_amount)</span>
                                <span class="summary-value">{{number_format($summary['total_actually_received'], 2)}} </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">الربح (المستلم فعلياً - المدفوع للتاجر)</span>
                                <span class="summary-value">{{number_format($summary['total_profit'], 2)}} </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">تكلفة التوصيل قبل الخصم</span>
                                <span class="summary-value">{{number_format($summary['total_delivery_cost_before_discount'], 2)}} </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">تكلفة التوصيل بعد الخصم</span>
                                <span class="summary-value">{{number_format($summary['total_delivery_cost_after_discount'], 2)}} </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">مبلغ الخصم على التوصيل</span>
                                <span class="summary-value">{{number_format($summary['discount_amount'], 2)}} </span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">المبلغ الصافي للطرف الثالث (الربح - تكلفة التوصيل بعد الخصم)</span>
                                <span class="summary-value {{$summary['net_amount'] >= 0 ? 'positive' : 'negative'}}">
                                    {{number_format($summary['net_amount'], 2)}} 
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
                                    <th>رقم الشحنة</th>
                                    <th>رقم المرجع</th>
                                    <th>اسم العميل</th>
                                    <th>منطقة التوصيل</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الحالة</th>
                                    <th>عدد القطع</th>
                                    <th>المبلغ المستحق دفعه للتاجر (seller_cost)</th>
                                    <th>المبلغ المستحق من العميل (package_cost + delivery_cost)</th>
                                    <th>المبلغ المستلم فعلياً من العميل (paid_amount)</th>
                                    <th>الربح (المستلم فعلياً - المدفوع للتاجر)</th>
                                    <th>تكلفة التوصيل قبل الخصم</th>
                                    <th>تكلفة التوصيل بعد الخصم</th>
                                    <th>مبلغ الخصم على التوصيل</th>
                                    <th>المبلغ الصافي للطرف الثالث</th>
                                    <th>سبب الفشل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packages as $package)
                                    @php
                                        // For packages with status 5 or 6, show 0 in cost columns
                                        if (in_array($package->status, [5, 6])) {
                                            $shouldReceive = 0;
                                            $actuallyReceived = 0;
                                            $sellerCost = 0;
                                            $profit = 0;
                                            $deliveryCost = 0;
                                            $deliveryCostAfterDiscount = 0;
                                            $discountAmount = 0;
                                            $netAmount = 0;
                                        } else {
                                            // If status is 3 (cancelled) AND package_enter_Hub is 0, delivery company didn't take the order
                                            // So don't take money - set all amounts to 0
                                            $isNotTakenByDelivery = ($package->status == 3 && ($package->package_enter_Hub ?? 0) == 0);
                                            
                                            if ($isNotTakenByDelivery) {
                                                $shouldReceive = 0;
                                                $actuallyReceived = 0;
                                                $sellerCost = 0;
                                                $profit = 0;
                                                $deliveryCost = 0;
                                                $deliveryCostAfterDiscount = 0;
                                                $discountAmount = 0;
                                                $netAmount = 0;
                                            } else {
                                                $packageCost = $package->package_cost ?? 0; // customer_must_pay
                                                $paidAmount = $package->paid_amount ?? 0; // what third party actually received
                                                $sellerCost = $package->seller_cost ?? 0; // seller_must_get (what third party pays)
                                                $deliveryCost = $package->delivery_cost ?? 0;
                                                $deliveryFeePayer = $package->delivery_fee_payer ?? 'customer';
                                                
                                                // What third party should receive: package_cost + delivery_cost (if customer pays delivery)
                                                $shouldReceive = $packageCost;
                                                if ($deliveryFeePayer == 'customer') {
                                                    $shouldReceive += $deliveryCost;
                                                }
                                                
                                                // For cancelled packages (status 3), apply cancellation fee percentage to delivery_cost
                                                // But only if package_enter_Hub is not 0 (delivery company took the order)
                                                // For cancelled packages, do NOT apply discount percentage
                                                $cancellationFeePercentage = $thirdParty ? ($thirdParty->cancellation_fee_percentage ?? 25) : 25; // Default to 25% if not set
                                                if ($package->status == 3 && ($package->package_enter_Hub ?? 0) != 0) {
                                                    $deliveryCost = $deliveryCost * ($cancellationFeePercentage / 100);
                                                    // For cancelled packages, don't apply discount - delivery_cost_after_discount = delivery_cost
                                                    $deliveryCostAfterDiscount = $deliveryCost;
                                                    $discountAmount = 0;
                                                } else {
                                                    // Apply discount to delivery_cost (what third party pays to delivery service)
                                                    $discount = $thirdParty ? ($thirdParty->discount ?? 0) : 0;
                                                    $deliveryCostAfterDiscount = $deliveryCost * (1 - ($discount / 100));
                                                    $discountAmount = $deliveryCost - $deliveryCostAfterDiscount;
                                                }
                                                
                                                // Third party profit = paid_amount - seller_cost (using actual received amount)
                                                $profit = $paidAmount - $sellerCost;
                                                
                                                // Net = profit - delivery_cost_after_discount
                                                $netAmount = $profit - $deliveryCostAfterDiscount;
                                                
                                                // Set actuallyReceived for display
                                                $actuallyReceived = $paidAmount;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{$package->id ?? '-'}}</td>
                                        <td>{{$package->reference_number ?? '-'}}</td>
                                        <td>{{$package->Customer ? $package->Customer->name : '-'}}</td>
                                        <td>{{$package->Area ? $package->Area->name : '-'}}</td>
                                        <td>{{$package->created_at ? \Carbon\Carbon::parse($package->created_at)->format('Y-m-d') : '-'}}</td>
                                        <td>{{getPackageStatus($package->status, $package->delivery_date) ?? '-'}}</td>
                                        <td>{{$package->pieces_count ?? 0}}</td>
                                        <td>{{number_format($sellerCost, 2)}} </td>
                                        <td>{{number_format($shouldReceive, 2)}} </td>
                                        <td>{{number_format($actuallyReceived, 2)}} </td>
                                        <td>{{number_format($profit, 2)}} </td>
                                        <td>{{number_format($deliveryCost, 2)}} </td>
                                        <td>{{number_format($deliveryCostAfterDiscount, 2)}} </td>
                                        <td>{{number_format($discountAmount, 2)}} </td>
                                        <td class="{{$netAmount >= 0 ? 'text-success' : 'text-danger'}}">
                                            {{number_format($netAmount, 2)}} 
                                        </td>
                                        <td>{{$package->failure_reason ? getReasonMessage($package->failure_reason) : '-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold; background-color: #f5f5f5;">
                                    <td></td>
                                    <td colspan="6" style="text-align: left;">المجموع الكلي</td>
                                    <td>{{number_format($summary['total_seller_cost'], 2)}} </td>
                                    <td>{{number_format($summary['total_should_receive'], 2)}} </td>
                                    <td>{{number_format($summary['total_actually_received'], 2)}} </td>
                                    <td>{{number_format($summary['total_profit'], 2)}} </td>
                                    <td>{{number_format($summary['total_delivery_cost_before_discount'], 2)}} </td>
                                    <td>{{number_format($summary['total_delivery_cost_after_discount'], 2)}} </td>
                                    <td>{{number_format($summary['discount_amount'], 2)}} </td>
                                    <td class="{{$summary['net_amount'] >= 0 ? 'text-success' : 'text-danger'}}">
                                        {{number_format($summary['net_amount'], 2)}} 
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
                        <i class="fa fa-info-circle"></i> يرجى اختيار الطرف الثالث وتاريخ الإنشاء لعرض التقرير.
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

