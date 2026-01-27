@extends('crudbooster::admin_template')
@section('content')

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">مفاتيح API - {{ $app->app_name }}</h3>
    </div>
    <div class="box-body">
        @if(session('message'))
        <div class="alert alert-{{ session('message_type') ?? 'success' }}">
            {{ session('message') }}
        </div>
        @endif
        
        <div class="alert alert-warning">
            <strong>تحذير:</strong> احفظ هذه المفاتيح في مكان آمن. لن تتمكن من رؤية مفتاح API السري مرة أخرى بعد إغلاق هذه الصفحة.
        </div>
        
        <div class="form-group">
            <label>مفتاح API (API Key):</label>
            <div class="input-group">
                <input type="text" class="form-control" id="api_key" value="{{ $app->api_key }}" readonly style="font-family: monospace;">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button" onclick="copyToClipboard('api_key')">
                        <i class="fa fa-copy"></i> نسخ
                    </button>
                </span>
            </div>
        </div>
        
        <div class="form-group">
            <label>مفتاح API السري (API Secret):</label>
            <div class="input-group">
                <input type="text" class="form-control" id="api_secret" value="{{ $app->api_secret }}" readonly style="font-family: monospace;">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button" onclick="copyToClipboard('api_secret')">
                        <i class="fa fa-copy"></i> نسخ
                    </button>
                </span>
            </div>
        </div>
        
        <div class="form-group">
            <label>مثال على الاستخدام:</label>
            <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>curl -X POST {{ url('/api/v1/third-party/orders') }} \
  -H "X-API-Key: {{ $app->api_key }}" \
  -H "Content-Type: application/json" \
  -d '{
    "seller_id": 1,
    "customer_name": "John Doe",
    "customer_phone": "1234567890",
    "area_id": 1,
    "package_cost": 100,
    "delivery_cost": 20,
    "delivery_date": "2026-01-30",
    "location_link": "https://maps.google.com/...",
    "location_text": "123 Main Street"
  }'</code></pre>
        </div>
    </div>
    <div class="box-footer">
        <a href="{{ $mainpath }}" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> رجوع
        </a>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    
    // Show toast notification if available, otherwise alert
    if (typeof toastr !== 'undefined') {
        toastr.success('تم النسخ بنجاح!');
    } else {
        alert("تم النسخ!");
    }
}
</script>

@endsection

