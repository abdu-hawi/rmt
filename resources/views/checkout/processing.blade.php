<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ app()->getLocale() === 'ar' ? 'جاري معالجة الدفع' : 'Processing Payment' }}</title>
    <style>
        :root {
            --accent: #6366f1;
            --accent-light: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: #050816;
            color: #fff;
            font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', 'Tajawal', sans-serif" : "'Inter', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif" }};
            -webkit-font-smoothing: antialiased;
        }

        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: radial-gradient(circle at top, rgba(99, 102, 241, 0.22), transparent 45%), linear-gradient(135deg, #050816 0%, #0f172a 50%, #111827 100%);
        }

        .card {
            width: 100%;
            max-width: 28rem;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 80px rgba(0, 0, 0, 0.35);
            padding: 40px 32px;
            text-align: center;
        }

        .loader-ring {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            border-radius: 50%;
            border: 4px solid rgba(99, 102, 241, 0.20);
            border-top-color: var(--accent);
            animation: spin 1.1s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .icon-box {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(16, 185, 129, 0.10);
            border: 2px solid rgba(16, 185, 129, 0.30);
        }

        h1 { font-size: 1.6rem; font-weight: 800; }
        p.desc { margin-top: 12px; font-size: 0.95rem; line-height: 1.75; color: #cbd5e1; }

        .notice {
            margin-top: 24px;
            border-radius: 16px;
            border: 1px solid rgba(51, 65, 85, 0.40);
            background: rgba(0, 0, 0, 0.20);
            padding: 14px 16px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--accent-light);
        }

        .notice.danger { color: var(--danger); border-color: rgba(239, 68, 68, 0.30); }

        .footer {
            margin-top: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 11px;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div id="loading-state">
                <div class="loader-ring"></div>
                @if (app()->getLocale() === 'ar')
                    <h1>جاري معالجة طلب الدفع</h1>
                    <p class="desc">نقوم حالياً بالتحقق من عملية الدفع الخاصة بك بانتظام. يرجى الانتظار بصبر.</p>
                    <div class="notice">لا تغلق هذه الصفحة حتى يتم إعادة توجيهك.</div>
                @else
                    <h1>Processing your payment</h1>
                    <p class="desc">We are securely checking and completing your payment request. Please wait a moment.</p>
                    <div class="notice">Please do not close this page until you are redirected.</div>
                @endif
            </div>

            <div id="success-state" class="hidden">
                <div class="icon-box">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                </div>
                @if (app()->getLocale() === 'ar')
                    <h1>تم الدفع بنجاح</h1>
                    <p class="desc">جاري تحويلك إلى صفحة تأكيد الطلب...</p>
                @else
                    <h1>Payment successful</h1>
                    <p class="desc">Redirecting you to your order confirmation...</p>
                @endif
            </div>

            <div id="failed-state" class="hidden">
                <div class="icon-box" style="background: rgba(239,68,68,0.10); border-color: rgba(239,68,68,0.30);">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </div>
                @if (app()->getLocale() === 'ar')
                    <h1>فشلت عملية الدفع</h1>
                    <p class="desc">لم نتمكن من تأكيد عملية الدفع. يمكنك إعادة المحاولة من سلة التسوق.</p>
                    <div class="notice danger" id="failed-message"></div>
                @else
                    <h1>Payment failed</h1>
                    <p class="desc">We could not confirm your payment. You can retry from your cart.</p>
                    <div class="notice danger" id="failed-message"></div>
                @endif
            </div>

            <div class="footer">
                <span class="dot"></span>
                <span>{{ app()->getLocale() === 'ar' ? 'عملية آمنة' : 'Secure payment' }}</span>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var orderNumber = @json($order_number);
            var statusUrl = "{{ route('checkout.payment.status', ['order_number' => $order_number]) }}";
            var confirmationUrl = "{{ route('orders.confirmation', $order_number) }}";

            var MAX_ATTEMPTS = 15;
            var INTERVAL_MS = 2000;
            var attempt = 0;
            var finished = false;

            function show(state) {
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('success-state').classList.add('hidden');
                document.getElementById('failed-state').classList.add('hidden');
                document.getElementById(state + '-state').classList.remove('hidden');
            }

            function redirectToConfirmation() {
                window.location.href = confirmationUrl;
            }

            function poll() {
                if (finished) return;

                attempt++;

                // 8. بعد انتهاء المحاولات ولم نجد البيانات في Redis -> استعلام مباشر من البوابة
                var isFallback = attempt > MAX_ATTEMPTS;
                var url = statusUrl + (isFallback ? '?fallback=true' : '');

                fetch(url)
                    .then(function (res) {
                        if (res.status === 444) {
                            show('failed');
                            document.getElementById('failed-message').textContent =
                                "{{ app()->getLocale() === 'ar' ? 'الطلب غير موجود' : 'Order not found' }}";
                            finished = true;
                            return null;
                        }
                        return res.json();
                    })
                    .then(function (data) {
                        if (!data) return;

                        if (data.status === 'completed') {
                            finished = true;
                            show('success');
                            setTimeout(redirectToConfirmation, 1500);
                            return;
                        }

                        if (data.status === 'failed') {
                            finished = true;
                            show('failed');
                            document.getElementById('failed-message').textContent = data.message || '';
                            return;
                        }

                        // حالة pending: نكمل الاستطلاع حتى 15 محاولة
                        if (attempt < MAX_ATTEMPTS + 1) {
                            setTimeout(poll, INTERVAL_MS);
                        } else {
                            finished = true;
                            show('failed');
                            document.getElementById('failed-message').textContent =
                                "{{ app()->getLocale() === 'ar' ? 'انتهت مهلة معالجة الدفع، يرجى المحاولة مرة أخرى' : 'Payment processing timed out, please try again' }}";
                        }
                    })
                    .catch(function () {
                        if (!finished && attempt < MAX_ATTEMPTS + 1) {
                            setTimeout(poll, INTERVAL_MS);
                        } else {
                            finished = true;
                            show('failed');
                            document.getElementById('failed-message').textContent =
                                "{{ app()->getLocale() === 'ar' ? 'حدث خطأ في الاتصال، يرجى المحاولة مرة أخرى' : 'A connection error occurred, please try again' }}";
                        }
                    });
            }

            poll();
        })();
    </script>
</body>
</html>
