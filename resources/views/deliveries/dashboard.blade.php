@extends('layouts.app')

@section('content')

    <style>
        .shipment-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .shipment-card .card-header {
            background-color: #f7f7f7;
            border-bottom: 1px solid #e0e0e0;
            padding: 1rem;
        }

        .btn-whatsapp {
            background-color: #25d366;
            color: white;
        }

        .btn-whatsapp:hover {
            background-color: #128c7e;
            color: white;
        }
    </style>

    <div class="header-section py-4 bg-light mb-4 border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-sm-12 col-md-12">
                    <div class="d-flex justify-content-between"> <span class="align-middle">مرحباً, {{ $delivery->name }}</span>
                        <a href="{{ route('deliveries.logout') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div id="alert-container">
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
        </div>


        @if ($shipments->count() > 0)
            <div class="row" id="shipment-list">
                @foreach ($shipments as $shipment)
                    <div class="col-sm-12 col-md-12" id="shipment-card-{{ $shipment->id }}">
                        <div class="shipment-card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-shipping-fast"></i>
                                    الشحنة #{{ $shipment->id }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="customer-info mb-3">
                                    <h6 class="text-primary"><strong> الاسم: </strong>{{ $shipment->customer->name }}</h6>
                                    <p class="mb-1 text-muted">
                                        <i class="fas fa-phone me-1"></i>
                                        <strong> رقم الهاتف: </strong>{{ $shipment->customer->phone_number }}
                                    </p>
                                    <p class="mb-1 text-muted">
                                        عدد القطع : {{ $shipment->pieces_count }}
                                    </p>
                                </div>

                                <div class="address-info mb-3">
                                    <h6>العنوان</h6>
                                    <p class="mb-1 small text-dark">
                                        <strong>المنطقة:</strong> {{ $shipment->area->name }}<br>
                                        <strong>الموقع:</strong> {{ $shipment->location_text }}<br>
                                        <strong>العمارة:</strong> {{ $shipment->building_number }}<br>
                                        <strong>الطابق:</strong> {{ $shipment->floor_number }}<br>
                                        <strong>الشقة:</strong> {{ $shipment->apartment_number }}<br>
                                        @if ($shipment->notes)
                                            <strong>علامات مميزة:</strong> {{ $shipment->notes }}
                                        @endif
                                    </p>
                                </div>

                                <div class="action-buttons pt-2 border-top">
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <a href="tel:{{ $shipment->customer->phone_number }}"
                                                class="btn btn-outline-primary w-100">
                                                <i class="fas fa-phone"></i> اتصال
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <button onclick="redirectToWhatsApp('{{ $shipment->customer->phone_number }}')"
                                                target="_blank" class="btn btn-whatsapp w-100">
                                                <i class="fab fa-whatsapp"></i> واتساب
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                                                data-bs-target="#deliveredModal{{ $shipment->id }}">
                                                <i class="fas fa-check"></i> تم التوصيل
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                                                data-bs-target="#failedModal{{ $shipment->id }}">
                                                <i class="fas fa-times"></i> تعذر التوصيل
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deliveredModal{{ $shipment->id }}" tabindex="-1"
                        data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-end">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">تأكيد التوصيل</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                {{-- Add the shipment ID to the form for JavaScript --}}
                                <form id="deliveredForm{{ $shipment->id }}" data-shipment-id="{{ $shipment->id }}"
                                    data-url="{{ route('deliveries.shipments.delivered', $shipment->id) }}"
                                    class="ajax-form">
                                    @csrf
                                    <div class="modal-body text-dark">
                                        <p>هل أنت متأكد من تسليم الشحنة **#{{ $shipment->id }}** للعميل:
                                            **{{ $shipment->customer->name }}**؟</p>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">
                                                إجمالي المبلغ المطلوب تحصيله:
                                                {{ $shipment->package_cost + $shipment->delivery_cost }}
                                            </label>
                                            <input type="number" step="0.01" name="total_cost" class="form-control"
                                                required placeholder="أدخل المبلغ الإجمالي الذي تم تحصيله">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">
                                                عدد قطع الشحنة:
                                                {{ $shipment->pieces_count }}
                                                الزبون استلم؟
                                            </label>
                                            <input type="number" step="0.01" name="delivered_pieces_count" class="form-control"
                                                required placeholder="أدخل عدد القطع التي استلمها الزبون">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-success">تأكيد التوصيل</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="failedModal{{ $shipment->id }}" tabindex="-1" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content text-end">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">الإبلاغ عن تعذر التوصيل</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="failedForm{{ $shipment->id }}" data-shipment-id="{{ $shipment->id }}"
                                    data-url="{{ route('deliveries.shipments.failed', $shipment->id) }}" method="POST"
                                    class="ajax-form">
                                    @csrf
                                    <div class="modal-body text-dark">
                                        <p>الشحنة #{{ $shipment->id }} - **{{ $shipment->customer->name }}**</p>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-dark">سبب التعذر</label>
                                            <select name="reason" class="form-select" required
                                                onchange="toggleReasonFields(this, {{ $shipment->id }})">
                                                <option value="">اختر السبب</option>
                                                <option value="no_answer">العميل لم يرد على المندوب</option>
                                                <option value="refused">العميل رفض الاستلام</option>
                                                <option value="rescheduled">العميل قام بتأجيل الشحنة</option>
                                                <option value="rto">RTO</option>
                                                <option value="other">سبب آخر</option>
                                            </select>
                                        </div>

                                        <div class="mb-3" id="newDateField{{ $shipment->id }}" style="display: none;">
                                            <label class="form-label">التاريخ الجديد للتوصيل</label>
                                            <input type="date" name="new_date" class="form-control"
                                                min="{{ date('Y-m-d') }}">
                                        </div>

                                        <div class="mb-3" id="customReasonField{{ $shipment->id }}"
                                            style="display: none;">
                                            <label class="form-label">السبب المخصص</label>
                                            <textarea name="custom_reason" class="form-control" rows="3" placeholder="أدخل سبب التعذر بالتفصيل"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">إلغاء</button>
                                        <button type="submit" class="btn btn-danger">تأكيد الإبلاغ</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="no-shipments-message" class="text-center py-5 empty-state" style="display: none;">
                <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                <h3 class="text-secondary fw-bold">لا توجد شحنات متبقية اليوم</h3>
                <p class="text-muted">جميع مهام التوصيل الخاصة بك مكتملة أو لم يتم تخصيص شحنات جديدة بعد.</p>
            </div>
        @else
            <div class="text-center py-5 empty-state" id="no-shipments-message">
                <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                <h3 class="text-secondary fw-bold">لا توجد شحنات مسندة لك اليوم</h3>
                <p class="text-muted">جميع مهام التوصيل الخاصة بك مكتملة أو لم يتم تخصيص شحنات جديدة بعد.</p>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function displayAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show text-end" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                $('#alert-container').append(alertHtml);
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }


            $(document).ready(function() {
                $('.ajax-form').on('submit', function(e) {
                    e.preventDefault();

                    const $form = $(this);
                    const shipmentId = $form.data('shipment-id');
                    const actionUrl = $form.data('url');
                    const $modal = $form.closest('.modal');
                    const $submitButton = $form.find('button[type="submit"]');

                    $submitButton.prop('disabled', true).text('جارٍ الإرسال...');
                    $('#alert-container').empty();

                    $.ajax({
                        url: actionUrl,
                        type: 'POST',
                        data: $form.serialize(),
                        dataType: 'json',
                        success: function(response) {
                            console.log("suc");
                            $modal.modal('hide');
                            displayAlert(response.message || 'تم تحديث حالة الشحنة بنجاح.',
                                'success');
                            window.location.reload();
                        },
                        error: function(xhr) {
                            console.log("344");

                            let errorMessage = 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                errorMessage = Object.values(errors)[0][0];
                            }
                            $modal.modal('hide');
                            displayAlert(errorMessage, 'danger');
                            $submitButton.prop('disabled', false).text('تأكيد الإجراء');
                        },
                        complete: function() {
                            $submitButton.prop('disabled', false).text('تأكيد الإجراء');
                        }
                    });
                });
            });

            function toggleReasonFields(selectElement, shipmentId) {
                const reason = selectElement.value;
                const newDateField = document.getElementById('newDateField' + shipmentId);
                const customReasonField = document.getElementById('customReasonField' + shipmentId);

                $(newDateField).hide().find('input').removeAttr('required');
                $(customReasonField).hide().find('textarea').removeAttr('required');

                if (reason === 'rescheduled') {
                    $(newDateField).show().find('input').attr('required', 'required');
                } else if (reason === 'other') {
                    $(customReasonField).show().find('textarea').attr('required', 'required');
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.modal .form-select').forEach(select => {
                    const form = $(select).closest('form').get(0);
                    if (form) {
                        const shipmentId = form.getAttribute('data-shipment-id');
                        if (shipmentId) {
                            toggleReasonFields(select, shipmentId);
                        }
                    }
                });
            });
        </script>

        <script>
            function redirectToWhatsApp(phoneNumber) {
                phoneNumber = '+963' + phoneNumber;
                const message = 'مرحباً، أنا مندوب التوصيل'; // Optional pre-filled message
                const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
                
                window.open(url, '_blank');
            }
        </script>
    @endpush
@endsection
