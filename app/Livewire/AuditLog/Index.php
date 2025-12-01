<?php

namespace App\Livewire\AuditLog;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $action_filter = '';
    public $model_filter = '';
    public $user_filter = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';

    public $mostrarDetalles = false;
    public $logSeleccionado = null;

    protected $queryString = [
        'search',
        'action_filter',
        'model_filter',
        'user_filter',
        'fecha_inicio',
        'fecha_fin'
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingActionFilter() { $this->resetPage(); }
    public function updatingModelFilter() { $this->resetPage(); }
    public function updatingUserFilter() { $this->resetPage(); }
    public function updatingFechaInicio() { $this->resetPage(); }
    public function updatingFechaFin() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'action_filter', 'model_filter', 'user_filter', 'fecha_inicio', 'fecha_fin']);
        $this->resetPage();
    }

    public function verDetalles($logId)
    {
        $this->logSeleccionado = AuditLog::find($logId);
        $this->mostrarDetalles = true;
    }

    public function cerrarDetalles()
    {
        $this->mostrarDetalles = false;
        $this->logSeleccionado = null;
    }

    public function render()
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('user_name', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%')
                  ->orWhere('model_id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->action_filter) {
            $query->where('action', $this->action_filter);
        }

        if ($this->model_filter) {
            $query->where('model', $this->model_filter);
        }

        if ($this->user_filter) {
            $query->where('user_id', $this->user_filter);
        }

        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereBetween('created_at', [$this->fecha_inicio, $this->fecha_fin]);
        } elseif ($this->fecha_inicio) {
            $query->where('created_at', '>=', $this->fecha_inicio);
        } elseif ($this->fecha_fin) {
            $query->where('created_at', '<=', $this->fecha_fin);
        }

        $logs = $query->paginate(20);

        // Obtener datos para filtros
        $usuarios = DB::table('users')->select('id', 'name')->get();
        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $models = AuditLog::select('model')->distinct()->pluck('model');

        return view('livewire.audit-log.index', [
            'logs' => $logs,
            'usuarios' => $usuarios,
            'actions' => $actions,
            'models' => $models,
        ]);
    }
}
