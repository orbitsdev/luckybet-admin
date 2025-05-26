<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            :root {
                --primary-color: #FC0204;
                --primary-hover: #e00203;
                --secondary-color: #ff3848;
                --text-dark: #4a4a4a;
                --text-medium: #6e6e6e;
                --text-light: #8a8a8a;
                --bg-light: #f8f9fa;
                --border-color: #e5e7eb;
            }

            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @vite(['resources/css/app.css'])

  
        @livewireStyles

  
    </head>
    <body class="font-sans  bg-white antialiased">

        {{ $slot }}

        @livewire('notifications')
        @stack('modals')
        @livewireScripts
        @filamentScripts
        @vite('resources/js/app.js')
      
    </body>
</html>
