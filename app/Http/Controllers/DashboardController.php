<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;

class DashboardController extends Controller
{
    /**
     * Libera una reserva (marca como 'completada') desde el Dashboard.
     * Libera habitaciones y estacionamiento asociados.
     */
    public function liberar(Request $request, int $id)
    {
        try {
            DB::beginTransaction();

            $reserva = DB::table('reservas')->where('idreservas', $id)->first();

            if (!$reserva) {
                DB::rollBack();
                return redirect()->route('dashboard')
                    ->with('dashboard_error', 'Reserva no encontrada.');
            }

            if (!in_array($reserva->estado, ['confirmada', 'pendiente'])) {
                DB::rollBack();
                return redirect()->route('dashboard')
                    ->with('dashboard_error', 'Solo se pueden liberar reservas confirmadas o pendientes.');
            }

            // Auditoría
            AuditService::logUpdated('Reserva', $id,
                ['estado' => $reserva->estado],
                ['estado' => 'completada']
            );

            // Marcar reserva como completada
            DB::table('reservas')
                ->where('idreservas', $id)
                ->update(['estado' => 'completada', 'updated_at' => now()]);

            // Liberar habitaciones
            $habitaciones = DB::table('habitaciones_has_reservas')
                ->where('reservas_idreservas', $id)
                ->pluck('habitaciones_idhabitacion');

            foreach ($habitaciones as $habitacionId) {
                DB::table('habitaciones')
                    ->where('idhabitacion', $habitacionId)
                    ->update(['estado' => 'disponible']);
            }

            // Liberar estacionamiento si tiene
            if ($reserva->estacionamiento_no_espacio) {
                DB::table('estacionamiento')
                    ->where('no_espacio', $reserva->estacionamiento_no_espacio)
                    ->update(['estado' => 'disponible']);
            }

            DB::commit();

            return redirect()->route('dashboard')
                ->with('dashboard_message', "Reserva liberada exitosamente. Estado: COMPLETADA");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dashboard')
                ->with('dashboard_error', 'Error al liberar la reserva: ' . $e->getMessage());
        }
    }
}
