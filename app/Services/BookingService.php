<?php
namespace App\Services;
use App\Models\{Machine, Reservation, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class BookingService
{
    public function book(User $user, int $machineId, Carbon $start): Reservation
    {
        return DB::transaction(function () use ($user, $machineId, $start) {
            // Serialize bookings per student; SQLite uses IMMEDIATE transactions.
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedUser->role === 'estudiante', 403);
            $machine = Machine::whereKey($machineId)->lockForUpdate()->firstOrFail();
            if ($start->isPast() || $start->gt(now()->addDays(30)->endOfDay()) || $start->hour < 8 || $start->hour > 19 || $start->minute !== 0 || $start->second !== 0) {
                throw ValidationException::withMessages(['slot' => 'Elige un turno futuro, entre las 08:00 y las 19:00, dentro de los próximos 30 días.']);
            }
            if ($machine->status !== 'disponible') {
                throw ValidationException::withMessages(['machine_id' => 'Esta máquina está en mantenimiento. Elige otra.']);
            }
            if ($lockedUser->reservations()->whereIn('status', Reservation::ACTIVE)->count() >= 3) {
                throw ValidationException::withMessages(['slot' => 'Ya tienes 3 reservas activas. Cancela una pendiente o espera a recoger tu ropa.']);
            }
            if (DB::table('reservation_slots')->where('machine_id', $machine->id)->where('starts_at', $start)->exists()) {
                throw ValidationException::withMessages(['slot' => 'Este turno acaba de ocuparse. Selecciona otra máquina u horario.']);
            }
            $reservation = Reservation::create(['user_id' => $user->id, 'machine_id' => $machine->id, 'starts_at' => $start, 'ends_at' => $start->copy()->addHour(), 'status' => 'pendiente']);
            DB::table('reservation_slots')->insert(['reservation_id' => $reservation->id, 'machine_id' => $machine->id, 'starts_at' => $start]);
            return $reservation;
        }, 3);
    }
    public function transition(Reservation $reservation, User $actor, string $status): void
    {
        DB::transaction(function () use ($reservation, $actor, $status) {
            $item = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if (!$actor->isStaff()) abort_unless($item->user_id === $actor->id && $status === 'cancelado', 403);
            if (!in_array($status, Reservation::TRANSITIONS[$item->status], true) || (!$actor->isStaff() && $item->status !== 'pendiente')) {
                throw ValidationException::withMessages(['status' => 'El estado cambió o esta transición no está permitida.']);
            }
            if ($status === 'en_proceso' && $item->starts_at->isFuture()) {
                throw ValidationException::withMessages(['status' => 'El lavado solo puede comenzar desde la hora reservada.']);
            }
            $item->update(['status' => $status]);
            if ($status === 'cancelado') DB::table('reservation_slots')->where('reservation_id', $item->id)->delete();
        }, 3);
    }
}
