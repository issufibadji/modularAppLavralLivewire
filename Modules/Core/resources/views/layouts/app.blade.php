<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>App</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow h-screen p-4">
            <h2 class="font-bold text-lg mb-4">Menu</h2>
            <ul>
                <li><a href="/" class="block py-2">Dashboard</a></li>
            </ul>
        </aside>

        <!-- Conteúdo -->
        <main class="flex-1 p-6">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

</body>
</html>