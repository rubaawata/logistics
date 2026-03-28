<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Arial", sans-serif;
            direction: rtl;
            text-align: right;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px 8px;
            vertical-align: middle;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header-table td {
            border: none;
        }

        .logo {
            width: 160px;
            height: auto;
        }

        .total-row td {
            font-weight: bold;
        }

        .summary-table {
            margin-top: 20px;
            width: 50%;
            float: left;
        }

        .summary-table td {
            text-align: center;
            border: 1px solid #444;
        }

        .summary-table .label {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 20px; background: #eee; padding: 10px; border-radius: 5px; display: flex; align-items: center; gap: 10px;">
        <label for="report_date_select"><strong>اختر التاريخ:</strong></label>
        <input type="date" id="report_date_select" value="{{ $report_date }}" 
            onchange="window.location.href='/admin/export-third-party-report/{{ $id }}/' + this.value">
        
        <button onclick="window.print()" style="margin-right: 20px;">طباعة تقرير PDF</button>

        <a id="excel_export_btn" class="btn btn-success" title="تصدير Excel" target="_blank" 
           href="https://tawaridsy.com/admin/third_party_applications/export-third-party-report-excel/{{ $id }}/{{ $report_date }}">
            <i class="fa fa-file-excel-o"></i> تصدير Excel
        </a>
    </div>

    <style>
        /* Hide the date picker when printing the actual PDF */
        @media print {
            .no-print { display: none; }
        }
    </style>
    <table class="header-table" style="width:100%; margin-bottom: 10px;">
        <tr>
            <td style="width:25%;"><strong>اسم التاجر / المسوق :</strong> {{ $third_party_name }}</td>
            <td style="width:25%;"><strong>التاريخ :</strong> {{ \Carbon\Carbon::parse($report_date)->format('Y/m/d') }}</td>
            <td style="width:25%;" rowspan="2" align="center">
                <img src="{{ asset('lading-logo.PNG') }}" class="logo" alt="Logo">
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>اسم الزبون</th>
                <th>رقم الطلب</th>
                <th>منطقة التوصيل</th>
                <th>شركة الشحن</th>
                <!--<th>نوع المنتج</th>-->
                <th>الحالة</th>
                <th>السبب</th>
                <!--<th>عدد المنتجات</th>
                <th>عدد المنتجات الموصلة</th>
                <th>جهة تحمّل تكلفة التوصيل</th>-->
                
                <th>الدفع للتاجر</th>
                <th>سعر الشحنة</th>
                <th>المبلغ المستلم من العميل</th>
                <th>أجور التوصيل</th>
                <th>أجور الشحن</th>
                <th>المبلغ المستحق</th>
                
            </tr>
        </thead>
        <tbody>
            @php
                $total_delivery_cost = 0;
                $total_shipments_cost = 0;
                $total_paid_cost = 0;
                $total = 0;
            @endphp
            @foreach($packages as $package)
                <tr>
                    <td>{{ $package->Customer->name ?? '' }}</td>
                    <td>{{ $package->reference_number ?? '' }}</td>
                    <td>{{ $package->area->name ?? '' }}</td>
                    <td>{{ $package->shipments_company_name ?? '' }}</td>
                    <!--<td>{{ $package->product_type ?? '---' }}</td>-->
                    <td>{{ getPackageStatus($package->status, $package->delivery_date) ?? '---' }}</td>
                    <td>{{ $package->status != 1 ? getReasonMessage($package->failure_reason) : '---' }}</td>
                    <!--<td>{{ $package->pieces_count ?? '---' }}</td>
                    <td>{{ $package->delivered_pieces_count ?? '---' }}</td>
                    <td>{{getDeliveryFeePayer($package->delivery_fee_payer, $package->status, $package->failure_reason) ?? '---'}}</td>-->
                    <td>
                        {{ number_format($package->seller_cost ?? 0) }}
                    </td>
                    <td>
                        {{ number_format($package->package_cost ?? 0) }}
                    </td>
                    <td>
                        {{ number_format($package->paid_amount ?? 0) }}
                        @php
                            $total_paid_amount += $package->paid_amount;
                        @endphp
                    </td>
                    <td>
                        @if($package->status == 1 || $package->status == 3)
                            {{ number_format($package->delivery_cost ?? 0) }}
                            @php
                                $total_delivery_cost += $package->delivery_cost;
                            @endphp
                        @endif
                    </td>
                    <td>
                        {{ number_format($package->cost_of_shipments ?? 0) }}
                        @php
                            $total_shipments_cost += $package->cost_of_shipments;
                        @endphp
                    </td>
                    <td>
                        @if($package->status == 1 || $package->status == 3)
                            @php
                                $seller_cost = $package->status == 1 ? $package->seller_cost : 0;
                                $current_total = $package->paid_amount - ($package->delivery_cost + $seller_cost + $package->cost_of_shipments);
                                $total += $current_total;
                            @endphp
                            {{ number_format($current_total) }}
                        @endif
                    </td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="8">المجموع</td>
                <td>{{ number_format($total_paid_amount) }}</td>
                <td>{{ number_format($total_delivery_cost) }}</td>
                <td>{{ number_format($total_shipments_cost) }}</td>
                <td>{{ number_format($total) }}</td>
                
            </tr>
        </tbody>
    </table>

    {{--@php
        $total_package_cost = $packages->sum('package_cost');
        $total_delivery_cost = $packages->sum('delivery_cost');
        $grand_total = $total_package_cost + $total_delivery_cost;
    @endphp

    <table class="summary-table">
        <tr>
            <td class="label">إجمالي المبالغ المستحقة</td>
            <td>{{ number_format($total_package_cost) }}</td>
        </tr>
        <tr>
            <td class="label">إجمالي أجور التوصيل</td>
            <td>{{ number_format($total_delivery_cost) }}</td>
        </tr>
        <tr>
            <td class="label">المجموع الكلي</td>
            <td><strong>{{ number_format($grand_total) }}</strong></td>
        </tr>
    </table>--}}


    <script>
        // Listen for date changes
        document.getElementById('report_date_select').addEventListener('change', function() {
            const selectedDate = this.value;
            const thirdPartyId = "{{ $id }}";
            
            // Reload the page to show new date data
            window.location.href = '/admin/export-third-party-report/' + thirdPartyId + '/' + selectedDate;
        });
    </script>
</body>
</html>
