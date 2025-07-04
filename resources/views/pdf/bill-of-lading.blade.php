<!DOCTYPE html>
<html dir="rtl">

<head>
    <meta charset="UTF-8">
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #777777;
            text-align: right;
            padding: 8px;
        }
    </style>
</head>

<body>
    <div>
        <table>
            <tr>
                <td><strong> التاريخ </strong></td>
                <td colspan="4">{{$package->delivery_date}}</td>
                <td colspan="2" rowspan="5"><img  src="{{asset('lading-logo.PNG')}}"></td>
            </tr>
            <tr>
                <td><strong> المرسل </strong></td>
                <td colspan="4">{{ $package->Seller->seller_name }}</td>
            </tr>
            <tr>
                <td><strong> العميل </strong></td>
                <td colspan="4">{{ $package->Customer->name }}</td>
            </tr>
            <tr>
                <td><strong> رقم التلفون </strong></td>
                <td colspan="4">{{ $package->Customer->phone_number }}</td>
            </tr>
            <tr>
                <td><strong> العنوان </strong></td>
                <td colspan="4">{{ $package->location_text }}</td>
            </tr>
            <tr>
                <td><strong> المدينة </strong></td>
                <td colspan="4">دمشق</td>
                <td colspan="2"><strong> وصف الشحنة </strong></td>
            </tr>
            <tr>
                <td><strong> المنطقة </strong></td>
                <td colspan="4">{{ $package->area->name }}</td>
                <td colspan="2" rowspan="3">{{ $package->description }}</td>
            </tr>
            <tr>
                <td><strong> رقم المبنى </strong></td>
                <td colspan="4">{{ $package->building_number }}</td>
            </tr>
            <tr>
                <td><strong> الطابق + الشقة </strong></td>
                <td colspan="4">{{ $package->floor_number . ' - ' . $package->apartment_number }}</td>
            </tr>
            <tr>
                <td><strong> فتح الشحنة </strong></td>
                <td>نعم</td>
                <td>{{$package->open_package == 1 ? 'X' : ''}}</td>
                <td>لا</td>
                <td>{{$package->open_package == 0 ? 'X' : ''}}</td>
                <td><strong> عدد القطع </strong></td>
                <td>{{$package->pieces_count}}</td>
            </tr>
            <tr>
                <td rowspan="2"><strong> ملاحظات </strong></td>
                <td colspan="4" rowspan="2">{{$package->notes}}</td>
                <td rowspan="2"><strong> السعر الاجمالي </strong></td>
                <td>{{$package->delivery_cost + $package->package_cost}}</td>
            </tr>
            <tr>
                <td>ل.س</td>
            </tr>
        </table>
    </div>
</body>

</html>