@extends('layouts.app')

@section('title', 'تسجيل دخول التجار')

@section('content')
    <div class="row justify-content-center align-items-center vh-100 m-0" dir="rtl">
        <div class="col-md-4">
            <div class="w-100 text-center">
                <img height="100px" src="{{ asset('logo-white-background-with-slogan-.png') }}" alt="Seller Login"
                    class="mb-4">
            </div>

            <div class="card shadow-lg p-4">
                <h3 class="text-center mb-4">تسجيل دخول التاجر</h3>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show text-end" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('sellers.login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="phone" class="form-label">رقم الهاتف</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                            class="form-control @error('phone') is-invalid @enderror" placeholder="أدخل رقم الهاتف"
                            required dir="rtl">
                        @error('phone')
                            <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="أدخل كلمة المرور"
                            required>
                        @error('password')
                            <div class="invalid-feedback d-block text-end">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" id="login-btn" class="btn btn-primary w-100">تسجيل الدخول</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('login-btn').addEventListener('click', function() {
            this.disabled = true;
            this.textContent = 'جاري تسجيل الدخول...';
            this.closest('form').submit();
        });
    </script>
@endsection
