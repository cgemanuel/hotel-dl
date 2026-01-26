<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Hotel Don Luis SCR</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'hdl-maroon': '#1a4d2e',
                        'hdl-cream': '#F5F1E6',
                        'hdl-gold': '#f4d03f',
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
</head>

<body class="antialiased font-sans">

<div class="grid lg:grid-cols-2 h-screen">

    <!-- COLUMNA IZQUIERDA -->
    <div class="relative hidden lg:block">
        <!-- Imagen de fondo -->
        <img
            src="/images/hotel-exterior.jpg"
            alt="Hotel Don Luis"
            class="absolute inset-0 w-full h-full object-cover"
        >

        <!-- Overlay verde -->
        <div class="absolute inset-0 bg-hdl-maroon bg-opacity-80"></div>

        <!-- Contenido -->
        <div class="relative z-10 h-full flex flex-col justify-center px-14 text-white">
            <img src="/images/logo-hdl.jpg" class="w-20 mb-6" alt="Logo">

            <h2 class="text-4xl font-bold text-hdl-gold mb-4">
                Sistema de Gestión Hotelera
            </h2>

            <p class="text-base leading-relaxed max-w-md text-gray-100">
                Plataforma interna para el control de reservas, habitaciones,
                clientes y pagos del Hotel Don Luis.
            </p>
        </div>
    </div>

    <!-- COLUMNA DERECHA -->
    <div class="bg-white flex flex-col">

        <!-- CONTENIDO PRINCIPAL -->
        <main class="flex-grow flex flex-col justify-center items-center text-center px-8">
            <img src="/images/logo-hdl.jpg" alt="Logo Hotel Don Luis" class="w-32 mb-6">

            @auth
                <!-- Usuario autenticado -->
                <h1 class="text-3xl font-semibold text-hdl-maroon mb-2">
                    Bienvenido de nuevo, {{ Auth::user()->name }}
                </h1>

                <p class="text-gray-600 max-w-sm mb-8">
                    Tu sesión sigue activa. Accede al panel de control.
                </p>

                <div class="flex gap-4">
                    <a href="{{ route('dashboard') }}"
                       class="px-6 py-3 rounded-md bg-hdl-maroon text-white font-semibold hover:bg-opacity-90 transition">
                        Dashboard
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="px-6 py-3 rounded-md border border-hdl-gold text-hdl-maroon font-semibold hover:bg-hdl-cream transition">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @else
                <!-- Usuario no autenticado -->
                <h1 class="text-3xl font-semibold text-hdl-maroon mb-2">
                    Bienvenido
                </h1>

                <p class="text-gray-600 max-w-sm mb-8">
                    Acceso exclusivo para personal autorizado del Hotel Don Luis.
                </p>

                <div class="flex gap-4">
                    <a href="{{ route('login') }}"
                       class="px-6 py-3 rounded-md bg-hdl-maroon text-white font-semibold hover:bg-opacity-90 transition">
                        Iniciar sesión
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-6 py-3 rounded-md border border-hdl-gold text-hdl-maroon font-semibold hover:bg-hdl-cream transition">
                            Registrarse
                        </a>
                    @endif
                </div>
            @endauth
        </main>

        <!-- FOOTER -->
        <footer class="text-center text-sm text-gray-500 pb-6">
            <p>C. 39 191-A, entre 38 y 40, Centro, 97760 Valladolid, Yuc.</p>
            <p class="font-semibold">+52 985 856 5617</p>
        </footer>
    </div>

</div>

</body>
</html>
