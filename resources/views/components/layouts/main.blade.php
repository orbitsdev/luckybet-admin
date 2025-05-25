<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
       <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js',])

        <!-- Styles -->
        @livewireStyles


        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('adminDashboard', () => ({
                    sidebarOpen: window.innerWidth >= 1024,
                    profileDropdownOpen: false,
                    usersOpen: true,
                    reportsOpen: true,
                    init() {
                        this.$nextTick(() => {
                            window.addEventListener('resize', () => {
                                if (window.innerWidth >= 1024) {
                                    this.sidebarOpen = true;
                                } else if (window.innerWidth < 1024 && this.sidebarOpen) {
                                    this.sidebarOpen = false;
                                }
                            });

                            window.addEventListener('keydown', e => {
                                if (e.key === 'Escape' && this.sidebarOpen && window.innerWidth < 1024) {
                                    this.sidebarOpen = false;
                                }
                            });

                            document.addEventListener('click', e => {
                                if (window.innerWidth < 1024 &&
                                    this.sidebarOpen &&
                                    !e.target.closest('#sidebar') &&
                                    !e.target.closest('button[aria-controls="sidebar"]')) {
                                    this.sidebarOpen = false;
                                }
                            }, { capture: true });
                        });
                    }
                }));
            });
        </script>
    </head>
    <body class="font-sans antialiased bg-white">

        {{ $slot }}


    </body>
</html>
