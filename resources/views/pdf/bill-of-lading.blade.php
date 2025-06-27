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
    <div style="margin-bottom: 20px">
        <table>
            <tr>
                <td style="width: 50%;border:none">
                    <svg width="70px" height="70px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3 9h6V3H3zm1-5h4v4H4zm1 1h2v2H5zm10 4h6V3h-6zm1-5h4v4h-4zm1 1h2v2h-2zM3 21h6v-6H3zm1-5h4v4H4zm1 1h2v2H5zm15 2h1v2h-2v-3h1zm0-3h1v1h-1zm0-1v1h-1v-1zm-10 2h1v4h-1v-4zm-4-7v2H4v-1H3v-1h3zm4-3h1v1h-1zm3-3v2h-1V3h2v1zm-3 0h1v1h-1zm10 8h1v2h-2v-1h1zm-1-2v1h-2v2h-2v-1h1v-2h3zm-7 4h-1v-1h-1v-1h2v2zm6 2h1v1h-1zm2-5v1h-1v-1zm-9 3v1h-1v-1zm6 5h1v2h-2v-2zm-3 0h1v1h-1v1h-2v-1h1v-1zm0-1v-1h2v1zm0-5h1v3h-1v1h-1v1h-1v-2h-1v-1h3v-1h-1v-1zm-9 0v1H4v-1zm12 4h-1v-1h1zm1-2h-2v-1h2zM8 10h1v1H8v1h1v2H8v-1H7v1H6v-2h1v-2zm3 0V8h3v3h-2v-1h1V9h-1v1zm0-4h1v1h-1zm-1 4h1v1h-1zm3-3V6h1v1z"/><path fill="none" d="M0 0h24v24H0z"/></svg>
                </td>
                <td style="border:none;text-align:left;font-size:25px"><strong>Tawarid</strong></td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 5px">
        <table>
            <tr>
                <td style="width: 25%">رقم الشحنة</td>
                <td>{{ $package->id }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 5px">
        <table>
            <tr>
                <td style="width: 25%">من</td>
                <td>{{ $package->Seller->seller_name }}</td>
            </tr>
            <tr>
                <td>إلى</td>
                <td>{{ $package->Customer->name }}</td>
            </tr>
            <tr>
                <td>تليفون</td>
                <td>{{ $package->Customer->phone_number }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 5px">
        <table>
            <tr>
                <td style="width: 25%">المدينة</td>
                <td>دمشق</td>
            </tr>
            <tr>
                <td style="width: 25%">المنطقة</td>
                <td>{{ $package->area->name }}</td>
            </tr>
            <tr>
                <td>العنوان</td>
                <td>{{ $package->location_text }}</td>
            </tr>
            <tr>
                <td>السعر</td>
                <td>{{ $package->delivery_cost + $package->package_cost }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 5px">
        <table>
            <tr>
                <td>ملاحظات</td>
            </tr>
            <tr>
                <td style="height: 70px">{{ $package->notes }}</td>
            </tr>
        </table>
    </div>
</body>

</html>