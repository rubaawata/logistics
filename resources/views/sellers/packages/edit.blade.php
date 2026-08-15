@extends('layouts.app')

@section('title', 'تعديل شحنة')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .seller-form .form-label {
            color: #1f2d3d;
            font-weight: 600;
        }

        .seller-form .form-text {
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
    <div class="header-section py-4 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="align-middle">تعديل الشحنة</span>
                <a href="{{ route('sellers.packages.index') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-right"></i> رجوع للقائمة
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5" dir="rtl">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show text-end" role="alert">
                        <strong>يرجى تصحيح الأخطاء التالية:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card shadow-sm seller-form">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-box"></i> بيانات المستلم</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('sellers.packages.update', $package->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="recipient_name" class="form-label">اسم المستلم <span class="text-danger">*</span></label>
                                    <input type="text" id="recipient_name" name="recipient_name"
                                        value="{{ old('recipient_name', optional($package->Customer)->name) }}"
                                        class="form-control @error('recipient_name') is-invalid @enderror" required>
                                    @error('recipient_name')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="recipient_phone" class="form-label">رقم هاتف المستلم <span class="text-danger">*</span></label>
                                    <input type="text" id="recipient_phone" name="recipient_phone"
                                        value="{{ old('recipient_phone', optional($package->Customer)->phone_number) }}"
                                        class="form-control @error('recipient_phone') is-invalid @enderror" required dir="rtl">
                                    @error('recipient_phone')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="address" class="form-label">العنوان التفصيلي <span class="text-danger">*</span></label>
                                    <input type="text" id="address" name="address"
                                        value="{{ old('address', $package->location_text) }}"
                                        class="form-control @error('address') is-invalid @enderror" required>
                                    @error('address')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="location_link" class="form-label">رابط الموقع (اختياري)</label>
                                    <input type="text" id="location_link" name="location_link"
                                        value="{{ old('location_link', $package->location_link) }}"
                                        class="form-control @error('location_link') is-invalid @enderror"
                                        placeholder="https://maps.google.com/...">
                                    @error('location_link')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-start">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> حفظ التعديلات
                                </button>
                                <a href="{{ route('sellers.packages.show', $package->id) }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
