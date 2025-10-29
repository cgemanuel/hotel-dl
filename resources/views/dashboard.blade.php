<x-layouts.app :title="__('DASHBOARD')">
    <!-- Contenedor principal del contenido del dashboard -->
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Sección con una cuadrícula de 3 columnas -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">

            <!-- Cuadro 1-->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>

            <!-- Cuadro 2-->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>

            <!-- Cuadro 3-->
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>

        <!-- Sección inferior del dashboard (es el cuadro grande de abajo)-->
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
