<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')

    <style>
        /* Ajustar z-index del sidebar de Flux */
        [data-flux-sidebar] {
            z-index: 40 !important;
        }

        /* Asegurar que los modales estén por encima */
        .modal-overlay {
            z-index: 9998 !important;
        }

        .modal-container {
            z-index: 9999 !important;
        }
    </style>
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

<flux:sidebar sticky stashable
    class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="{{ route('dashboard') }}"
       class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
       wire:navigate>
        <x-app-logo />
    </a>

    {{-- ===================== MENÚ PRINCIPAL ===================== --}}
    <flux:navlist variant="outline">

        <flux:navlist.group :heading="__('Platform')" class="grid">

            <flux:navlist.item icon="home"
                :href="route('dashboard')"
                :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:navlist.item>

            <flux:navlist.item icon="calendar"
                :href="route('reservas.index')"
                :current="request()->routeIs('reservas.*')"
                wire:navigate>
                {{ __('Reservas') }}
            </flux:navlist.item>

            <flux:navlist.item icon="home"
                :href="route('habitaciones.index')"
                :current="request()->routeIs('habitaciones.*')"
                wire:navigate>
                {{ __('Habitaciones') }}
            </flux:navlist.item>

            <flux:navlist.item icon="square-3-stack-3d"
                :href="route('estacionamiento.index')"
                :current="request()->routeIs('estacionamiento.*')"
                wire:navigate>
                {{ __('Estacionamiento') }}
            </flux:navlist.item>

            <flux:navlist.item icon="document-text"
                :href="route('facturacion.index')"
                :current="request()->routeIs('facturacion.*')"
                wire:navigate>
                {{ __('Facturación') }}
            </flux:navlist.item>

            <flux:navlist.item icon="magnifying-glass-circle"
                :href="route('reservas.calendario-visual')"
                wire:navigate>
                {{ __('Calendario Visual') }}
            </flux:navlist.item>

            <flux:navlist.item icon="document-chart-bar"
                :href="route('reportes.ingresos')"
                :current="request()->routeIs('reportes.ingresos')"
                wire:navigate>
                {{ __('Reportes de Ingresos') }}
            </flux:navlist.item>

            <flux:navlist.item icon="clipboard-document-list"
                :href="route('servicios-adicionales.index')"
                :current="request()->routeIs('servicios-adicionales.*')"
                wire:navigate>
                {{ __('Servicios Adicionales') }}
            </flux:navlist.item>

            <flux:navlist.item icon="magnifying-glass-circle"
                :href="route('reservas.busqueda-avanzada')"
                wire:navigate>
                {{ __('Búsqueda Avanzada') }}
            </flux:navlist.item>

        </flux:navlist.group>


        {{-- ===================== GERENCIA ===================== --}}
        @if(auth()->check() && in_array(auth()->user()->rol, ['gerente', 'superusuario']))
        <flux:navlist.group
            :heading="__('Gerencia')"
            class="grid border-t border-zinc-200 dark:border-zinc-700 pt-4 mt-4">

            <flux:navlist.item icon="cog"
                :href="route('gerente.habitaciones')"
                wire:navigate>
                {{ __('Gestión Habitaciones') }}
            </flux:navlist.item>

            <flux:navlist.item icon="square-3-stack-3d"
                :href="route('gerente.estacionamiento')"
                wire:navigate>
                {{ __('Gestión Estacionamiento') }}
            </flux:navlist.item>

            <flux:navlist.item icon="clipboard-document-check"
                :href="route('audit-log.index')"
                wire:navigate>
                {{ __('Historial de Auditoría') }}
            </flux:navlist.item>

            <flux:navlist.item icon="clipboard-document-list"
                :href="route('reportes.reportes-avanzados')"
                wire:navigate>
                {{ __('Reportes Avanzados') }}
            </flux:navlist.item>

        </flux:navlist.group>
        @endif


        {{-- ===================== SUPERUSUARIO ===================== --}}
        @if(auth()->check() && auth()->user()->rol === 'superusuario')
        <flux:navlist.group
            :heading="__('Superusuario')"
            class="grid border-t border-zinc-200 dark:border-zinc-700 pt-4 mt-4">

            <flux:navlist.item icon="shield-check"
                :href="route('superuser.usuarios')"
                wire:navigate>
                {{ __('Gestión de Usuarios') }}
            </flux:navlist.item>

        </flux:navlist.group>
        @endif

    </flux:navlist>

    <flux:spacer />

    {{-- ===================== LINKS EXTERNOS ===================== --}}
    <flux:navlist variant="outline">
        <flux:navlist.item icon="folder-git-2"
            href="https://github.com/laravel/livewire-starter-kit"
            target="_blank">
            {{ __('Repository') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text"
            href="https://laravel.com/docs/starter-kits#livewire"
            target="_blank">
            {{ __('Documentation') }}
        </flux:navlist.item>
    </flux:navlist>


    {{-- ===================== USER MENU DESKTOP ===================== --}}
    <flux:dropdown class="hidden lg:block" position="bottom" align="start">

        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">
            <flux:menu.radio.group>
                <div class="p-2 text-sm">
                    <div class="font-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-zinc-500">{{ auth()->user()->email }}</div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:menu.item as="button" type="submit"
                    icon="arrow-right-start-on-rectangle">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>

</flux:sidebar>

{{ $slot }}

@fluxScripts
</body>
</html>
