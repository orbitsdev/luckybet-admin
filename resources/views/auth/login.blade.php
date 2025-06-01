<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
    
    <style>
        input:focus {
            outline: none !important;
            border-color: #fc0204 !important;
            box-shadow: 0 0 0 2px rgba(252, 2, 4, 0.3) !important;
        }
        input[type="checkbox"]:checked {
            background-color: #fc0204 !important;
            border-color: #fc0204 !important;
        }
    </style>
</head>
<body class="h-full">
    <div class="flex min-h-full">
        <div class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div class="flex flex-col items-center text-center mb-6">
                    <img class="h-32 w-auto" src="{{ asset('assets/logo.png') }}" alt="LuckyBet">
                    <h2 class="mt-8 text-3xl/9 font-bold tracking-tight text-gray-900">LuckyBet Admin Portal</h2>
                    <p class="mt-2 text-base/6 text-gray-500">Sign in to manage your lottery business</p>
                </div>

                <div class="mt-10">
                    <x-validation-errors class="mb-4" />

                    @session('status')
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ $value }}
                        </div>
                    @endsession

                    <div>
                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                            @csrf
                            <div>
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                                <div class="mt-2">
                                    <input id="email" name="email" type="email" autocomplete="email" required :value="old('email')" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 border border-gray-300 placeholder:text-gray-400 focus:border-primary-600 focus:ring-primary-600 focus:ring-2 focus:ring-offset-0 focus:outline-none sm:text-sm/6">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                                <div class="mt-2">
                                    <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 border border-gray-300 placeholder:text-gray-400 focus:border-primary-600 focus:ring-primary-600 focus:ring-2 focus:ring-offset-0 focus:outline-none sm:text-sm/6">
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex gap-3">
                                    <div class="flex h-6 shrink-0 items-center">
                                        <div class="group grid size-4 grid-cols-1">
                                            <input id="remember_me" name="remember" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto">
                                            <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                                <path class="opacity-0 group-has-[:checked]:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path class="opacity-0 group-has-[:indeterminate]:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    </div>
                                    <label for="remember_me" class="block text-sm/6 text-gray-900">Remember me</label>
                                </div>

                                @if (Route::has('password.request'))
                                    <div class="text-sm/6">
                                        <a href="{{ route('password.request') }}" class="font-semibold text-primary-600 hover:text-primary-500">Forgot password?</a>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <button type="submit" class="flex w-full justify-center rounded-md bg-primary-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Sign in</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="mt-10 pt-4 border-t border-gray-200 text-center text-sm text-gray-500 w-full">
                        &copy; {{ date('Y') }} LuckyBet. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
        <div class="relative hidden w-0 flex-1 lg:block">
            <img class="absolute inset-0 h-full w-full object-cover" src="{{ asset('assets/logo_bg.jpg') }}" alt="LuckyBet Background" onerror="this.onerror=null; this.src='{{ asset('assets/logo.png') }}'; this.classList.add('p-16', 'object-contain');">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600/40 to-primary-900/70 mix-blend-multiply"></div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
