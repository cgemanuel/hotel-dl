/** @type {import('tailwindcss').Config} */
module.exports = {
    // La sección 'content' ya está siendo manejada por Vite/Laravel,
    // pero es bueno especificar las rutas de tus archivos blade.
    darkMode: 'class',
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./app/View/Components/**/*.php",
        "./app/Livewire/**/*.php",
    ],

    // ************************************************************
    // SAFELISTING: FUERZA A TAILWIND A INCLUIR LAS CLASES DINÁMICAS
    // ************************************************************
    // Estas clases se generan dinámicamente en tu código PHP (bgClase),
    // y la purga las está eliminando del CSS final.
    safelist: [
        // Estados de Habitaciones: Disponible
        'bg-green-600', 'bg-green-800', 'hover:bg-green-900',
        'border-green-400',

        // Estados de Habitaciones: Ocupada
        'bg-red-600', 'bg-red-800', 'hover:bg-red-900',
        'border-red-400',

        // Estados de Habitaciones: En Mantenimiento (por consistencia)
        'bg-yellow-500', 'bg-yellow-600', 'hover:bg-yellow-600',
        'border-yellow-400',
    ],

    theme: {
        extend: {
            // Aquí puedes dejar tus extensiones de tema si las tienes
        },
    },
    plugins: [
        // Aquí puedes agregar plugins si es necesario, por ejemplo:
        // require('@tailwindcss/forms'),
    ],
}
