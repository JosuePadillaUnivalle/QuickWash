<?php
namespace Tests\Feature;
use App\Models\{User, Machine, Reservation};
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class QuickWashTest extends TestCase
{
    use RefreshDatabase;
    private User $student;
    private User $staff;
    private Machine $machine;
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 14)->setTime(7, 0));
        $this->student = User::factory()->create();
        $this->staff = User::factory()->create(['role' => 'personal']);
        $this->machine = Machine::create(['name' => 'Lavadora 01', 'capacity' => 8, 'type' => 'lavadora', 'location' => 'Planta baja', 'status' => 'disponible']);
    }
    private function payload(string $time = '10:00'): array { return ['machine_id' => $this->machine->id, 'date' => '2026-09-15', 'time' => $time]; }
    private function book(string $time = '10:00'): Reservation { return app(BookingService::class)->book($this->student, $this->machine->id, \Carbon\Carbon::parse('2026-09-15 '.$time)); }
    public function test_registration_ignores_supplied_staff_role(): void
    {
        $this->post('/registro', ['name' => 'Ana', 'email' => 'ANA@example.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'personal'])->assertRedirect('/inicio');
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com', 'role' => 'estudiante']);
        $this->assertAuthenticated();
    }
    public function test_login_logout_and_invalid_credentials(): void
    {
        $this->post('/ingresar', ['email' => $this->student->email, 'password' => 'bad'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/ingresar', ['email' => $this->student->email, 'password' => 'password'])->assertRedirect('/inicio');
        $this->assertAuthenticatedAs($this->student);
        $this->post('/salir')->assertRedirect('/ingresar');
        $this->assertGuest();
    }
    public function test_guest_and_role_boundaries(): void
    {
        $this->get('/reservas')->assertRedirect('/ingresar');
        $this->actingAs($this->student)->get('/maquinas')->assertForbidden();
        $this->post('/maquinas', [])->assertForbidden();
        $this->actingAs($this->staff)->get('/reservar')->assertForbidden();
        $this->post('/reservas', $this->payload())->assertForbidden();
    }
    public function test_one_machine_cannot_be_booked_twice_and_adjacent_slot_is_allowed(): void
    {
        $this->actingAs($this->student)->post('/reservas', $this->payload())->assertRedirect('/reservas');
        $other = User::factory()->create();
        $this->actingAs($other)->post('/reservas', $this->payload())->assertSessionHasErrors('slot');
        $this->post('/reservas', $this->payload('11:00'))->assertRedirect('/reservas');
        $this->assertDatabaseCount('reservations', 2);
        $this->assertDatabaseCount('reservation_slots', 2);
    }
    public function test_student_is_limited_to_three_active_reservations(): void
    {
        $this->book('08:00'); $this->book('09:00'); $this->book('10:00');
        $this->actingAs($this->student)->post('/reservas', $this->payload('11:00'))->assertSessionHasErrors('slot');
        $this->assertDatabaseCount('reservations', 3);
    }
    public function test_cancellation_releases_slot_and_active_quota(): void
    {
        $first = $this->book('08:00'); $this->book('09:00'); $this->book('10:00');
        $this->actingAs($this->student)->patch(route('reservations.cancel', $first))->assertRedirect()->assertSessionHasNoErrors();
        $this->post('/reservas', $this->payload('08:00'))->assertRedirect('/reservas');
        $this->assertDatabaseHas('reservations', ['id' => $first->id, 'status' => 'cancelado']);
        $this->assertDatabaseCount('reservation_slots', 3);
    }
    public function test_student_cannot_cancel_others_reservations_or_change_status(): void
    {
        $reservation = $this->book();
        $other = User::factory()->create();
        $this->actingAs($other)->patch(route('reservations.cancel', $reservation))->assertForbidden();
        $this->get('/reservas')->assertDontSee($reservation->code);
        $this->actingAs($this->student)->patch(route('reservations.status', $reservation), ['status' => 'entregado'])->assertForbidden();
    }
    public function test_only_pending_reservations_can_be_cancelled(): void
    {
        $reservation = $this->book();
        foreach (['en_proceso', 'listo', 'entregado', 'cancelado'] as $status) {
            $reservation->update(['status' => $status]);
            $this->actingAs($this->student)->patch(route('reservations.cancel', $reservation))->assertSessionHasErrors('status');
            $this->assertSame($status, $reservation->fresh()->status);
        }
    }
    public function test_staff_lifecycle_changes_only_status_and_student_sees_ready(): void
    {
        $reservation = $this->book();
        $this->travelTo(\Carbon\Carbon::parse('2026-09-15 10:00'));
        foreach (['en_proceso', 'listo', 'entregado'] as $status) {
            $this->actingAs($this->staff)->patch(route('reservations.status', $reservation), ['status' => $status, 'user_id' => $this->staff->id, 'machine_id' => 999, 'starts_at' => '2030-01-01'])->assertSessionHasNoErrors();
            $this->assertSame($status, $reservation->fresh()->status);
            $this->assertSame($this->student->id, $reservation->fresh()->user_id);
            $this->assertSame($this->machine->id, $reservation->fresh()->machine_id);
            $this->actingAs($this->student)->get('/reservas')->assertOk()->assertSee(Reservation::LABELS[$status]);
        }
        $this->actingAs($this->staff)->patch(route('reservations.status', $reservation), ['status' => 'pendiente'])->assertSessionHasErrors('status');
    }
    public function test_future_work_cannot_start_and_states_cannot_be_skipped(): void
    {
        $reservation = $this->book();
        foreach (['en_proceso', 'listo', 'entregado'] as $status) $this->actingAs($this->staff)->patch(route('reservations.status', $reservation), ['status' => $status])->assertSessionHasErrors('status');
        $this->assertSame('pendiente', $reservation->fresh()->status);
    }
    public function test_invalid_slots_and_maintenance_are_rejected(): void
    {
        foreach ([['date' => '2026-09-13'], ['date' => '2026-11-01'], ['time' => '07:00'], ['time' => '20:00'], ['time' => '10:30']] as $override) $this->actingAs($this->student)->post('/reservas', array_replace($this->payload(), $override))->assertSessionHasErrors();
        $this->machine->update(['status' => 'mantenimiento']);
        $this->post('/reservas', $this->payload())->assertSessionHasErrors('machine_id');
        $this->assertDatabaseCount('reservations', 0);
    }
    public function test_machine_crud_and_protection_of_active_reservations(): void
    {
        $data = ['name' => 'Secadora nueva', 'type' => 'secadora', 'capacity' => 10, 'location' => 'Sala 2', 'status' => 'disponible'];
        $this->actingAs($this->staff)->post('/maquinas', $data)->assertRedirect(route('machines.index'));
        $created = Machine::where('name', 'Secadora nueva')->firstOrFail();
        $this->put(route('machines.update', $created), array_replace($data, ['name' => 'Secadora editada']))->assertSessionHasNoErrors();
        $this->delete(route('machines.destroy', $created))->assertSessionHasNoErrors();
        $this->assertSoftDeleted($created);
        $reservation = $this->book();
        $this->delete(route('machines.destroy', $this->machine))->assertSessionHasErrors('machine');
        $this->put(route('machines.update', $this->machine), array_replace($this->machine->toArray(), ['status' => 'mantenimiento']))->assertSessionHasErrors('status');
        $this->patch(route('reservations.status', $reservation), ['status' => 'cancelado'])->assertSessionHasNoErrors();
        $this->delete(route('machines.destroy', $this->machine))->assertSessionHasNoErrors();
        $this->get('/reservas')->assertOk()->assertSee('Lavadora 01');
    }
    public function test_all_views_render_and_filters_work(): void
    {
        $reservation = $this->book();
        $this->get('/ingresar')->assertOk(); $this->get('/registro')->assertOk();
        foreach (['/inicio', '/reservar', '/reservas', '/reservas?status=pendiente'] as $url) $this->actingAs($this->student)->get($url)->assertOk();
        $this->get('/reservar?date=2026-09-15&time=10:00')->assertSee('Ocupada en este turno');
        $this->get('/reservas?status=cancelado')->assertDontSee($reservation->code);
        foreach (['/inicio', '/reservas', '/maquinas', '/maquinas/create', '/maquinas/'.$this->machine->id.'/edit'] as $url) $this->actingAs($this->staff)->get($url)->assertOk();
    }
    public function test_database_rejects_duplicate_slot_even_without_service(): void
    {
        $reservation = $this->book();
        $other = Reservation::create(['user_id' => $this->student->id, 'machine_id' => $this->machine->id, 'starts_at' => $reservation->starts_at, 'ends_at' => $reservation->ends_at]);
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        DB::table('reservation_slots')->insert(['reservation_id' => $other->id, 'machine_id' => $this->machine->id, 'starts_at' => $reservation->starts_at]);
    }
}
