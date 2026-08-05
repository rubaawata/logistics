@extends('layouts.app')

@section('title', 'إضافة شحنة')

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
                <span class="align-middle">إضافة شحنة جديدة</span>
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
                        <h6 class="mb-0"><i class="fas fa-box"></i> بيانات الشحنة</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('sellers.packages.store') }}">
                            @csrf

                            <h6 class="text-secondary fw-bold mb-3">بيانات المستلم</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="recipient_name" class="form-label">اسم المستلم <span class="text-danger">*</span></label>
                                    <input type="text" id="recipient_name" name="recipient_name"
                                        value="{{ old('recipient_name') }}"
                                        class="form-control @error('recipient_name') is-invalid @enderror" required>
                                    @error('recipient_name')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="recipient_phone" class="form-label">رقم هاتف المستلم <span class="text-danger">*</span></label>
                                    <input type="text" id="recipient_phone" name="recipient_phone"
                                        value="{{ old('recipient_phone') }}"
                                        class="form-control @error('recipient_phone') is-invalid @enderror" required dir="rtl">
                                    @error('recipient_phone')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <h6 class="text-secondary fw-bold mb-3">الوجهة</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="area_id" class="form-label">المنطقة <span class="text-danger">*</span></label>
                                    <select id="area_id" name="area_id"
                                        class="form-select @error('area_id') is-invalid @enderror" required>
                                        <option value="">اختر المنطقة</option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" data-cost="{{ $area->delivery_cost }}"
                                                {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('area_id')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="address" class="form-label">العنوان التفصيلي <span class="text-danger">*</span></label>
                                    <input type="text" id="address" name="address" value="{{ old('address') }}"
                                        class="form-control @error('address') is-invalid @enderror" required>
                                    @error('address')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="location_link" class="form-label">رابط الموقع (اختياري)</label>
                                    <input type="text" id="location_link" name="location_link"
                                        value="{{ old('location_link') }}"
                                        class="form-control @error('location_link') is-invalid @enderror"
                                        placeholder="https://maps.google.com/...">
                                    @error('location_link')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <h6 class="text-secondary fw-bold mb-3">تفاصيل الشحنة</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="package_cost" class="form-label">سعر الشحنة <span class="text-danger">*</span></label>
                                    <input type="number" min="0" id="package_cost" name="package_cost"
                                        value="{{ old('package_cost') }}"
                                        class="form-control @error('package_cost') is-invalid @enderror" required>
                                    @error('package_cost')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="delivery_cost" class="form-label">تكلفة التوصيل</label>
                                    <input type="number" id="delivery_cost" name="delivery_cost_display"
                                        value="{{ old('delivery_cost') }}" class="form-control bg-light" readonly
                                        tabindex="-1" placeholder="—">
                                    <small class="form-text">تُحدد تلقائياً حسب المنطقة المختارة.</small>
                                </div>
                                <div class="col-md-4">
                                    <label for="pieces_count" class="form-label">عدد القطع</label>
                                    <input type="number" min="1" id="pieces_count" name="pieces_count"
                                        value="{{ old('pieces_count', 1) }}"
                                        class="form-control @error('pieces_count') is-invalid @enderror">
                                    @error('pieces_count')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="delivery_fee_payer" class="form-label">جهة تحمّل تكلفة التوصيل <span class="text-danger">*</span></label>
                                    <select id="delivery_fee_payer" name="delivery_fee_payer"
                                        class="form-select @error('delivery_fee_payer') is-invalid @enderror" required>
                                        <option value="customer" {{ old('delivery_fee_payer', 'customer') == 'customer' ? 'selected' : '' }}>الزبون</option>
                                        <option value="seller" {{ old('delivery_fee_payer') == 'seller' ? 'selected' : '' }}>البائع</option>
                                    </select>
                                    @error('delivery_fee_payer')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">وصف الشحنة <span class="text-danger">*</span></label>
                                    <textarea id="description" name="description" rows="2" required
                                        class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="notes" class="form-label">ملاحظات (اختياري)</label>
                                    <textarea id="notes" name="notes" rows="2"
                                        class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-start">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check"></i> حفظ الشحنة
                                </button>
                                <a href="{{ route('sellers.packages.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var areaSelect = document.getElementById('area_id');
                var costField = document.getElementById('delivery_cost');

                function syncDeliveryCost() {
                    var option = areaSelect.options[areaSelect.selectedIndex];
                    var cost = option ? option.getAttribute('data-cost') : '';
                    costField.value = (cost === null || cost === '') ? '' : cost;
                }

                if (areaSelect && costField) {
                    areaSelect.addEventListener('change', syncDeliveryCost);
                    // Reflect any pre-selected area (e.g. when the form re-renders after a validation error).
                    syncDeliveryCost();
                }
            })();
        </script>
    @endpush
@endsection
