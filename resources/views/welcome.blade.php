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
                    'hdl-maroon': '#1a4d2e', // 🟢 Verde oscuro
                    'hdl-cream': '#F5F1E6',
                    'hdl-gold': '#f4d03f', // ✨ Dorado claro
                  }
                }
              }
            }
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    </head>

    <!-- Cuerpo principal de la página -->
    <body class="antialiased">
        <!-- Contenedor principal dividido en dos columnas (pantalla a la mitad) -->
        <div class="grid lg:grid-cols-2 h-screen">
            <!-- Columna izquierda con fondo verde oscuro y presentación del hotel -->
            <div class="bg-hdl-maroon flex flex-col justify-center items-center p-12 text-hdl-gold">
                <div class="max-w-md">
                     <img src="/images/hotel-exterior.jpg" alt="Piscina del Hotel Don Luis" class="w-full h-auto object-cover rounded-lg shadow-lg mb-8" />
                    <h2 class="text-2xl font-semibold text-center">
                        Ya sea por trabajo o descanso, Hotel Don Luis es tu mejor elección
                    </h2>
                </div>
            </div>

            <!-- Columna derecha con fondo blanco y presentación general -->
            <div class="bg-white flex flex-col p-8">
                <!-- Sección de navegación segun la autenticación -->
                @if (Route::has('login'))
                    <nav class="flex justify-end gap-2">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-md px-4 py-2 bg-hdl-maroon text-hdl-gold font-semibold hover:bg-opacity-90 transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-md px-4 py-2 bg-hdl-maroon text-hdl-gold font-semibold hover:bg-opacity-90 transition">
                                Login
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded-md px-4 py-2 text-black font-semibold border border-gray-300 hover:bg-gray-100 transition">
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif

                <!-- Contenido principal (logo y nombre del hotel) -->
                <main class="flex-grow flex flex-col justify-center items-center text-center">
                    <img src="/images/logo-hdl.jpg" alt="Logo Hotel Don Luis" class="w-48 h-auto mb-6" />

                    <h1 class="text-4xl font-bold text-hdl-maroon">Hotel Don Luis</h1>
                    <p class="text-2xl text-gray-700 font-semibold">SCR</p>
                </main>

                <footer class="text-center text-sm text-gray-500">
                    <p>C. 39 191-A, entre 38 Y 40, Centro, 97760 Valladolid, Yuc.</p>
                    <p class="font-semibold">+52 985 856 5617</p>
                </footer>
            </div>
        </div>
    </body>
</html>
