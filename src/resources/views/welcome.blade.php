<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }} | {{ __('welcome.title_suffix') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|noto-sans-tc:400,500,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --brand-ink: #111827;
                --brand-soft: #f5f7ff;
                --brand-line: #dbe3ff;
                --brand-primary: #2251ff;
                --brand-accent: #ff7a18;
            }

            body {
                font-family: 'Noto Sans TC', sans-serif;
                color: var(--brand-ink);
                background:
                    radial-gradient(1200px 500px at 10% -5%, #e6ecff 0%, transparent 60%),
                    radial-gradient(700px 420px at 95% 0%, #ffe6d1 0%, transparent 65%),
                    #f8faff;
            }

            .headline-font {
                font-family: 'Space Grotesk', 'Noto Sans TC', sans-serif;
            }
        </style>
    </head>
    <body class="min-h-screen">
        <div class="mx-auto max-w-6xl px-6 py-8 lg:px-10 lg:py-10">
            <header class="flex items-center justify-between rounded-2xl border border-[var(--brand-line)] bg-white/85 px-5 py-4 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-[var(--brand-primary)]/10 p-2 text-[var(--brand-primary)]">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-full w-full" aria-hidden="true">
                            <path d="M4 12h16M12 4v16" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <p class="headline-font text-lg font-bold tracking-tight">{{ config('app.name', 'Laravel') }}</p>
                </div>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-2">
                        <form method="POST" action="{{ route('locale.switch', ['locale' => 'zh_TW']) }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold transition duration-200 hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)]">
                                {{ __('welcome.locale_zh_tw') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('locale.switch', ['locale' => 'en']) }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold transition duration-200 hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)]">
                                {{ __('welcome.locale_en') }}
                            </button>
                        </form>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium transition duration-200 hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)]">{{ __('welcome.nav_dashboard') }}</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium transition duration-200 hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)]">{{ __('welcome.nav_login') }}</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-xl bg-[var(--brand-primary)] px-4 py-2 text-sm font-semibold text-white transition duration-200 hover:bg-[#163fd6]">{{ __('welcome.nav_register') }}</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main class="mt-8 grid gap-6 lg:mt-10 lg:grid-cols-12">
                <section class="lg:col-span-7 rounded-3xl border border-[var(--brand-line)] bg-white p-8 shadow-[0_10px_45px_rgba(34,81,255,0.08)] lg:p-10">
                    <p class="inline-flex items-center rounded-full bg-[var(--brand-primary)]/10 px-3 py-1 text-xs font-semibold tracking-wide text-[var(--brand-primary)]">{{ __('welcome.badge') }}</p>
                    <h1 class="headline-font mt-4 text-3xl font-bold leading-tight tracking-tight lg:text-5xl">{{ __('welcome.hero_title_prefix') }}
                        <span class="text-[var(--brand-primary)]">{{ __('welcome.hero_title_highlight') }}</span>
                    </h1>
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-600 lg:text-lg">{{ __('welcome.hero_description') }}</p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--brand-primary)] px-5 py-3 text-sm font-semibold text-white transition duration-200 hover:translate-y-[-1px] hover:bg-[#163fd6]">{{ __('welcome.cta_start') }}</a>
                        <a href="{{ url('/dashboard') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition duration-200 hover:border-[var(--brand-primary)] hover:text-[var(--brand-primary)]">{{ __('welcome.cta_dashboard') }}</a>
                    </div>
                </section>

                <section class="lg:col-span-5 rounded-3xl border border-[var(--brand-line)] bg-gradient-to-b from-white to-[var(--brand-soft)] p-7">
                    <h2 class="headline-font text-xl font-bold tracking-tight">{{ __('welcome.section_title') }}</h2>
                    <ul class="mt-5 space-y-4">
                        <li class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-sm font-semibold">{{ __('welcome.feature_1_title') }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ __('welcome.feature_1_desc') }}</p>
                        </li>
                        <li class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-sm font-semibold">{{ __('welcome.feature_2_title') }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ __('welcome.feature_2_desc') }}</p>
                        </li>
                        <li class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-sm font-semibold">{{ __('welcome.feature_3_title') }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ __('welcome.feature_3_desc') }}</p>
                        </li>
                    </ul>
                    <div class="mt-5 rounded-2xl bg-[var(--brand-accent)] px-4 py-3 text-sm font-semibold text-white">{{ __('welcome.next_step') }}</div>
                </section>
            </main>
        </div>
    </body>
</html>
