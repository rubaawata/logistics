@extends('layouts.app')

@section('title', 'شحناتي')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .packages-card .card-body {
            padding: 0;
        }

        .packages-table {
            margin-bottom: 0;
            color: #1f2d3d;
        }

        .packages-table thead th {
            background-color: var(--color-primary);
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            border: none;
        }

        .packages-table tbody td {
            vertical-align: middle;
            color: #1f2d3d;
        }

        .packages-table tbody tr:hover {
            background-color: #eef2f6;
        }

        .tracking-code {
            font-family: monospace;
            font-weight: 700;
            color: var(--color-primary);
        }
    </style>
@endpush

@section('content')
    <div class="header-section py-4 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="align-middle">مرحباً, {{ $seller->seller_name }}</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('sellers.packages.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> إضافة شحنة
                    </a>
                    <a href="{{ route('sellers.logout') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5" dir="rtl">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show text-end" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show text-end" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($packages->count() > 0 || request('search'))
            <form method="GET" action="{{ route('sellers.packages.index') }}" class="mb-3 d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="بحث" dir="rtl">
                <button type="submit" class="btn btn-primary text-nowrap">
                    <i class="fas fa-search"></i> بحث
                </button>
                @if (request('search'))
                    <a href="{{ route('sellers.packages.index') }}" class="btn btn-outline-secondary text-nowrap">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>

            <div class="card packages-card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-box"></i> شحناتي</h6>
                    <span class="badge bg-light text-dark">{{ $packages->total() }}</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover packages-table text-end">
                        <thead>
                            <tr>
                                <th>رقم التتبع</th>
                                <th>المستلم</th>
                                <th>الوجهة</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>تفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($packages as $package)
                                @php
                                    $statusColors = [
                                        1 => 'success',
                                        2 => 'secondary',
                                        3 => 'danger',
                                        4 => 'info',
                                        5 => 'warning',
                                        6 => 'primary',
                                    ];
                                    $statusColor = $statusColors[$package->status] ?? 'secondary';
                                @endphp
                                <tr>
                                    <td><span class="tracking-code">{{ $package->reference_number ?? '#' . $package->id }}</span></td>
                                    <td>
                                        {{ optional($package->Customer)->name ?? '—' }}
                                        @if (optional($package->Customer)->phone_number)
                                            <br><small class="text-muted">{{ $package->Customer->phone_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($package->Area)->name ?? '—' }}
                                        @if ($package->location_text)
                                            <br><small class="text-muted">{{ $package->location_text }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ getPackageStatus($package->status, $package->delivery_date) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($package->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('sellers.packages.show', $package->id) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('sellers.packages.edit', $package->id) }}"
                                                class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $packages->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5 empty-state">
                <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                @if (request('search'))
                    <h3 class="text-secondary fw-bold">لا توجد نتائج مطابقة</h3>
                    <p class="text-muted">لم يتم العثور على أي شحنة تطابق «{{ request('search') }}».</p>
                    <a href="{{ route('sellers.packages.index') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-times"></i> إلغاء البحث
                    </a>
                @else
                    <h3 class="text-secondary fw-bold">لا توجد شحنات بعد</h3>
                    <p class="text-muted">لم تقم بإضافة أي شحنة حتى الآن. ابدأ بإضافة شحنتك الأولى.</p>
                    <a href="{{ route('sellers.packages.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> إضافة شحنة
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection
