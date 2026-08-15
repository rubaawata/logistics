@extends('layouts.app')

@section('title', 'تفاصيل الشحنة')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .detail-table {
            margin-bottom: 0;
            color: #1f2d3d;
        }

        .detail-table th {
            width: 35%;
            background-color: #eef2f6;
            color: #1f2d3d;
            font-weight: 600;
            white-space: nowrap;
        }

        .detail-table td {
            color: #1f2d3d;
        }

        .tracking-code {
            font-family: monospace;
            font-weight: 700;
            color: var(--color-primary);
        }
    </style>
@endpush

@section('content')
    @php
        $statusColors = [1 => 'success', 2 => 'secondary', 3 => 'danger', 4 => 'info', 5 => 'warning', 6 => 'primary'];
        $statusColor = $statusColors[$package->status] ?? 'secondary';
        $feePayers = config('constants.DELIVERY_FEE_PAYER');
    @endphp

    <div class="header-section py-4 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="align-middle">تفاصيل الشحنة</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('sellers.packages.edit', $package->id) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-pen"></i> تعديل
                    </a>
                    <a href="{{ route('sellers.packages.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-right"></i> رجوع للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5" dir="rtl">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-box"></i>
                            <span class="tracking-code text-white">{{ $package->reference_number ?? '#' . $package->id }}</span>
                        </h6>
                        <span class="badge bg-{{ $statusColor }}">
                            {{ getPackageStatus($package->status, $package->delivery_date) }}
                        </span>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered detail-table text-end">
                            <tbody>
                                <tr>
                                    <th>المستلم</th>
                                    <td>{{ optional($package->Customer)->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>رقم هاتف المستلم</th>
                                    <td>{{ optional($package->Customer)->phone_number ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>المنطقة</th>
                                    <td>{{ optional($package->Area)->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>العنوان</th>
                                    <td>{{ $package->location_text ?? '—' }}</td>
                                </tr>
                                @if ($package->location_link)
                                    <tr>
                                        <th>رابط الموقع</th>
                                        <td><a href="{{ $package->location_link }}" target="_blank" rel="noopener">فتح الموقع</a></td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>سعر الشحنة</th>
                                    <td>{{ number_format($package->package_cost) }}</td>
                                </tr>
                                <tr>
                                    <th>تكلفة التوصيل</th>
                                    <td>{{ number_format($package->delivery_cost) }}</td>
                                </tr>
                                <tr>
                                    <th>عدد القطع</th>
                                    <td>{{ $package->pieces_count ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>جهة تحمّل تكلفة التوصيل</th>
                                    <td>{{ $feePayers[$package->delivery_fee_payer] ?? '—' }}</td>
                                </tr>
                                @if ($package->description)
                                    <tr>
                                        <th>وصف الشحنة</th>
                                        <td>{{ $package->description }}</td>
                                    </tr>
                                @endif
                                @if ($package->notes)
                                    <tr>
                                        <th>ملاحظات</th>
                                        <td>{{ $package->notes }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>تاريخ الإنشاء</th>
                                    <td>{{ optional($package->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
