<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>The Writers Room Live</title>
        <script src="https://cdn.tailwindcss.com"></script>
        @livewireStyles
    </head>
    <body class="antialiased bg-gray-900 overflow-hidden">
        <livewire:chatroom.chat-layout />
        @livewireScripts
    </body>
</html>
