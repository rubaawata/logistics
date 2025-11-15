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
    <table class="header-table" style="width:100%; margin-bottom: 10px;">
        <tr>
            <td style="width:25%;"><strong>اسم المندوب :</strong> {{ $delivery_name }}</td>
            <td style="width:25%;"><strong>التاريخ :</strong> {{ $report_date  }}</td>
            <td style="width:25%;" rowspan="2" align="center">
                <img src="{{ asset('lading-logo.PNG') }}" class="logo" alt="Logo">
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>اسم الزبون</th>
                <th>منطقة التوصيل</th>
                <th>نوع المنتج</th>
                <th>الحالة</th>
                <th>عدد المنتجات</th>
                <th>المبلغ المستحق</th>
                <th>أجور التوصيل</th>
                <th>المبلغ المستلم من العميل</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $package)
                <tr>
                    <td>{{ $package->Customer->name ?? '' }}</td>
                    <td>{{ $package->area->name ?? '' }}</td>
                    <td>{{ $package->product_type ?? '---' }}</td>
                    <td>{{ getPackageStatus($package->status, $package->delivery_date) ?? '---' }}</td>
                    <td>{{ $package->pieces_count ?? '---' }}</td>
                    <td>{{ number_format($package->package_cost ?? 0) }}</td>
                    <td>{{ number_format($package->delivery_cost ?? 0) }}</td>
                    <td>{{ number_format($package->paid_amount ?? 0) }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="5">المجموع</td>
                <td>{{ number_format($packages->sum('package_cost')) }}</td>
                <td>{{ number_format($packages->sum('delivery_cost')) }}</td>
                <td>{{ number_format($packages->sum('paid_amount')) }}</td>
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

</body>
</html>
