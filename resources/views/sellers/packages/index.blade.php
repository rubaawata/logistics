@extends('layouts.app')

@section('title', 'شحناتي')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .packages-card .card-body {
            padding: 0;
        }

        .package-item {
            border: 1px solid #e3e8ee;
            border-radius: .5rem;
            transition: box-shadow .2s ease;
        }

        .package-item:hover {
            box-shadow: 0 .25rem 1rem rgba(0, 0, 0, .08);
        }

        .package-item .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e8ee;
        }

        .package-meta {
            font-size: .85rem;
            color: var(--bs-secondary-color);
        }

        .package-meta i {
            color: var(--bs-secondary-color);
            width: 1.1rem;
            text-align: center;
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
                <div class="card-body p-3">
                    <div class="row g-3">
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
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card package-item h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                        <span class="tracking-code text-white">{{ $package->reference_number ?? '#' . $package->id }}</span>
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ getPackageStatus($package->status, $package->delivery_date) }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="package-meta d-flex flex-column gap-2 pt-1 pb-1">
                                            <div>
                                                <i class="fas fa-user"></i>
                                                <span class="fw-semibold">{{ optional($package->Customer)->name ?? '—' }}</span>
                                                @if (optional($package->Customer)->phone_number)
                                                    <small class="text-muted d-block pe-4">{{ $package->Customer->phone_number }}</small>
                                                @endif
                                            </div>
                                            <div>
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>{{ optional($package->Area)->name ?? '—' }}</span>
                                                @if ($package->location_text)
                                                    <small class="text-muted d-block pe-4">{{ $package->location_text }}</small>
                                                @endif
                                            </div>
                                            <div>
                                                <i class="fas fa-calendar-alt"></i>
                                                <span>{{ optional($package->created_at)->format('Y-m-d H:i') ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="background-color: var(--color-light);" class="card-footer border-top-0 d-flex gap-2 justify-content-end">
                                        <a href="{{ route('sellers.packages.show', $package->id) }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> تفاصيل
                                        </a>
                                        <a href="{{ route('sellers.packages.edit', $package->id) }}"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-pen"></i> تعديل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
