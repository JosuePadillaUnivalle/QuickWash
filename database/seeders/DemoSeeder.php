<?php
namespace Database\Seeders;
use App\Models\{User, Machine, Reservation};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) throw new \RuntimeException('Los datos de demostración solo se permiten en local/testing.');
        $this->call(DatabaseSeeder::class);
        $student = User::firstOrCreate(['email' => 'estudiante@quickwash.test'], ['name' => 'Alex Rivera', 'password' => 'QuickWash2026!']);
        $staff = User::firstOrCreate(['email' => 'personal@quickwash.test'], ['name' => 'María Flores', 'password' => 'QuickWash2026!']);
        $staff->forceFill(['role' => 'personal'])->save();
        if ($student->reservations()->exists()) return;
        $machines = Machine::where('status', 'disponible')->orderBy('id')->get();
        foreach ([['pendiente', 1, 10], ['listo', -1, 9], ['entregado', -3, 14], ['cancelado', -4, 11]] as $index => [$status, $day, $hour]) {
            DB::transaction(function () use ($student, $machines, $index, $status, $day, $hour) {
                $start = today()->addDays($day)->setTime($hour, 0);
                $machine = $machines[$index % $machines->count()];
                $reservation = Reservation::create(['user_id' => $student->id, 'machine_id' => $machine->id, 'starts_at' => $start, 'ends_at' => $start->copy()->addHour(), 'status' => $status]);
                if ($status !== 'cancelado') DB::table('reservation_slots')->insert(['reservation_id' => $reservation->id, 'machine_id' => $machine->id, 'starts_at' => $start]);
            });
        }
    }
}
