<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Login') }} - Riof Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-deep-950 min-h-screen flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gradient-to-br from-accent/5 via-transparent to-violet-accent/5 pointer-events-none"></div>

    <div class="glass-card p-8 w-full max-w-md relative">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="text-2xl font-extrabold bg-gradient-to-r from-accent to-accent-light bg-clip-text text-transparent">Riof</a>
            <p class="text-muted-400 text-sm mt-2">{{ __('Sign in to your account') }}</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div class="relative">
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder=" "
                       class="peer input-floating pt-5 pb-2">
                <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('Email') }}</label>
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="relative">
                <input type="password" name="password" required placeholder=" "
                       class="peer input-floating pt-5 pb-2">
                <label class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-muted-400 peer-focus:top-3 peer-focus:text-[11px] peer-focus:text-accent-light peer-[:not(:placeholder-shown)]:top-3 peer-[:not(:placeholder-shown)]:text-[11px] transition-all duration-200">{{ __('Password') }}</label>
                @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" id="remember" class="rounded border-slate-700 bg-carbon-900 text-accent focus:ring-accent/30">
                <label for="remember" class="text-muted-400">{{ __('Remember me') }}</label>
            </div>
            <button class="w-full py-3 bg-accent hover:bg-accent/90 text-white font-semibold rounded-lg transition-all glow-border">{{ __('Login') }}</button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-sm text-muted-400 hover:text-accent-light transition-colors">{{ __('Back to Home') }}</a>
        </div>
    </div>
</body>
</html>
