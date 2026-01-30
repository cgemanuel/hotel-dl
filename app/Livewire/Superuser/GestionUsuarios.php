<?php

namespace App\Livewire\Superuser;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GestionUsuarios extends Component
{
    use WithPagination;

    public $search = '';
    public $rol_filtro = '';

    public $mostrarModal = false;
    public $editando_id = null;
    public $mostrarPassword = false;

    // Campos del formulario
    public $name = '';
    public $email = '';
    public $telefono = '';
    public $rol = 'recepcionista';
    public $password = '';
    public $password_confirmation = '';

    protected $queryString = ['search', 'rol_filtro'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingRolFiltro() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'rol_filtro']);
        $this->resetPage();
    }

    public function abrirModalCrear()
    {
        $this->reset(['name', 'email', 'telefono', 'rol', 'password', 'password_confirmation', 'editando_id']);
        $this->rol = 'recepcionista';
        $this->mostrarModal = true;
    }

    public function abrirModalEditar($userId)
    {
        $user = User::findOrFail($userId);

        $this->editando_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->telefono = $user->telefono;
        $this->rol = $user->rol;

        $this->mostrarModal = true;
    }

    public function guardar()
    {
        if ($this->editando_id) {
            // Actualizar
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $this->editando_id,
                'telefono' => 'required|string|max:20',
                'rol' => 'required|in:recepcionista,gerente,superusuario',
            ];

            if ($this->password) {
                $rules['password'] = 'required|string|min:8|confirmed';
            }

            $this->validate($rules);

            $user = User::findOrFail($this->editando_id);
            $user->name = $this->name;
            $user->email = $this->email;
            $user->telefono = $this->telefono;
            $user->rol = $this->rol;

            if ($this->password) {
                $user->password = Hash::make($this->password);
            }

            $user->save();

            session()->flash('message', 'Usuario actualizado exitosamente.');
        } else {
            // Crear
            $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'telefono' => 'required|string|max:20',
                'rol' => 'required|in:recepcionista,gerente,superusuario',
                'password' => 'required|string|min:8|confirmed',
            ]);

            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'rol' => $this->rol,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);

            session()->flash('message', 'Usuario creado exitosamente.');
        }

        $this->cerrarModal();
    }

    public function eliminar($userId)
    {
        $user = User::findOrFail($userId);

        // Evitar que se elimine a sí mismo
        if ($user->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');
            return;
        }

        $user->delete();
        session()->flash('message', 'Usuario eliminado exitosamente.');
    }

    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);
        $newPassword = 'HDL' . rand(1000, 9999);

        $user->password = Hash::make($newPassword);
        $user->save();

        session()->flash('password_reset', "Contraseña reseteada para {$user->name}: {$newPassword}");
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->reset(['name', 'email', 'telefono', 'rol', 'password', 'password_confirmation', 'editando_id']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = User::query()->orderBy('id', 'desc');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('telefono', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->rol_filtro) {
            $query->where('rol', $this->rol_filtro);
        }

        $usuarios = $query->paginate(15);

        return view('livewire.superuser.gestion-usuarios', [
            'usuarios' => $usuarios
        ]);
    }
}
