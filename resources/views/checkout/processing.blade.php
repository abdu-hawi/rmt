<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'جاري معالجة الدفع' : 'Processing Payment' }}</title>
    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spin-loader {
            animation: spin 1.1s linear infinite;
        }
    </style>
</head>
<body class="min-h-screen bg-[#050816] text-white antialiased">
    <div class="min-h-screen flex items-center justify-center px-6 py-10 bg-[radial-gradient(circle_at_top,_rgba(58,130,246,0.22),_transparent_45%),linear-gradient(135deg,_#050816_0%,_#0f172a_50%,_#111827_100%)]">
        <div class="w-full max-w-xl rounded-[28px] border border-white/10 bg-white/10 backdrop-blur-xl shadow-[0_20px_80px_rgba(0,0,0,0.35)] p-8 md:p-10 text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full border border-accent/25 bg-accent/10">
                <div class="spin-loader h-8 w-8 rounded-full border-4 border-accent/20 border-t-accent"></div>
            </div>

            @if (app()->getLocale() === 'ar')
                <h1 class="text-2xl sm:text-3xl font-bold text-white">جاري معالجة طلب الدفع</h1>
                <p class="mt-3 text-sm sm:text-base text-slate-300 leading-7">
                    نقوم حاليا بالتحقق من عملية الدفع الخاصة بك بانتظام. يرجى الانتظار بصبر.
                </p>
                <div class="mt-7 rounded-2xl border border-slate-700/40 bg-black/20 px-4 py-3">
                    <p class="text-sm font-medium text-accent-light">
                        لا تغلق هذه الصفحة حتى يتم إعادة توجيهك.
                    </p>
                </div>
            @else
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Processing your payment</h1>
                <p class="mt-3 text-sm sm:text-base text-slate-300 leading-7">
                    We are securely checking and completing your payment request. Please wait a moment.
                </p>
                <div class="mt-7 rounded-2xl border border-slate-700/40 bg-black/20 px-4 py-3">
                    <p class="text-sm font-medium text-accent-light">
                        Please do not close this page until you are redirected.
                    </p>
                </div>
            @endif

            <div class="mt-8 flex items-center justify-center gap-3 text-[11px] uppercase tracking-[0.3em] text-slate-400">
                <span class="h-2 w-2 rounded-full bg-accent"></span>
                <span>{{ app()->getLocale() === 'ar' ? 'عملية آمنة' : 'Secure payment' }}</span>
            </div>
        </div>
    </div>
</body>
</html>
