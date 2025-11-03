@extends('layouts.app')

@section('title', 'تسجيل دخول المندوبين')

@section('content')
    <div class="row justify-content-center align-items-center vh-100" dir="rtl">
        <div class="col-md-4">
            <div class="card shadow-lg p-4">
                <h3 class="text-center mb-4">تسجيل دخول المندوب</h3>

                <div id="alert-container"></div>

                <form id="loginForm">
                    @csrf
                    <div class="mb-3">
                        <label for="phone" class="form-label">رقم الهاتف</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                            placeholder="أدخل رقم الهاتف" required dir="rtl">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="أدخل كلمة المرور" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                $('#alert-container').html('');
                $.ajax({
                    url: '{{ route('deliveries.login') }}',
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        phone: $('#phone').val(),
                        password: $('#password').val()
                    },
                    success: function(response) {
                        showAlert('success', response.message || 'تم تسجيل الدخول بنجاح!');

                        if (response.token) {
                            localStorage.setItem('delivery_token', response.token);
                        }

                        window.location.href = '/delivery/dashboard';
                    },
                    error: function(xhr) {
                        let errorMessage = 'فشل تسجيل الدخول. يرجى التحقق من بيانات الاعتماد.';
                     
                        showAlert('danger', errorMessage);
                    }
                });
            });

            function showAlert(type, message) {
                $('#alert-container').html(`
                {{-- إضافة text-right لضمان ظهور النص بشكل صحيح من اليمين لليسار --}}
                <div class="alert alert-${type} alert-dismissible fade show mt-3 text-right" role="alert">
                    ${message}
                    {{-- إضافة aria-label بالإغلاق --}}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
                `);
            }
        });
    </script>
@endpush
