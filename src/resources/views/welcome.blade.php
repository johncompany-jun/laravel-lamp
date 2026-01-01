<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/bg-top.jpg') }}');">
            <div class="min-h-screen bg-black/20">
                <div class="relative min-h-screen flex flex-col items-center justify-center">
                    <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                        <main class="text-center">
                            <h1 class="text-2xl md:text-4xl font-bold text-white mb-8 drop-shadow-lg">
                                PW管理システム
                            </h1>

                            @if (Route::has('login'))
                                <div class="flex justify-center">
                                    @auth
                                        <a
                                            href="{{ url('/dashboard') }}"
                                            class="rounded-md px-6 py-3 text-white bg-white/20 backdrop-blur-md border border-white/30 shadow-lg transition hover:bg-white/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                        >
                                            ダッシュボード
                                        </a>
                                    @else
                                        <a
                                            href="{{ route('login') }}"
                                            class="rounded-md px-6 py-3 text-white bg-white/20 backdrop-blur-md border border-white/30 shadow-lg transition hover:bg-white/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                        >
                                            ログイン
                                        </a>
                                    @endauth
                                </div>
                            @endif
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
