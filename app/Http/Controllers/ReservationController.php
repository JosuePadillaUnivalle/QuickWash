<?php
namespace App\Http\Controllers;
use App\Models\{Machine, Reservation};
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(array_keys(Reservation::LABELS))], 'date' => 'nullable|date_format:Y-m-d', 'search' => 'nullable|string|max:100']);
        $query = Reservation::with(['machine', 'user']);
        if (!$request->user()->isStaff()) $query->where('user_id', $request->user()->id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('date')) $query->whereDate('starts_at', $request->date);
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->whereHas('machine', fn ($m) => $m->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$search.'%'));
            });
        }
        return view('reservations.index', ['reservations' => $query->latest('starts_at')->paginate(10)->withQueryString()]);
    }
    public function create(Request $request)
    {
        $request->validate(['date' => 'nullable|date_format:Y-m-d|after_or_equal:today|before_or_equal:'.now()->addDays(30)->toDateString(), 'time' => ['nullable', Rule::in(array_map(fn ($h) => sprintf('%02d:00', $h), range(8, 19)))]]);
        $date = $request->input('date', now()->hour >= 19 ? now()->addDay()->toDateString() : today()->toDateString());
        $hour = $date === today()->toDateString() ? max(8, now()->hour + 1) : 8;
        $time = $request->input('time', sprintf('%02d:00', $hour));
        $start = Carbon::parse($date.' '.$time);
        $occupied = DB::table('reservation_slots')->where('starts_at', $start)->pluck('machine_id')->all();
        return view('reservations.create', ['machines' => Machine::orderBy('name')->get(), 'occupied' => $occupied, 'date' => $date, 'time' => $time, 'start' => $start, 'active' => $request->user()->reservations()->whereIn('status', Reservation::ACTIVE)->count()]);
    }
    public function store(Request $request, BookingService $service)
    {
        $data = $request->validate(['machine_id' => 'required|integer|exists:machines,id', 'date' => 'required|date_format:Y-m-d', 'time' => 'required|date_format:H:i']);
        $reservation = $service->book($request->user(), (int) $data['machine_id'], Carbon::parse($data['date'].' '.$data['time']));
        return redirect()->route('reservations.index')->with('success', 'Reserva '.$reservation->code.' confirmada. Te esperamos el '.$reservation->starts_at->format('d/m/Y').' a las '.$reservation->starts_at->format('H:i').'.');
    }
    public function cancel(Request $request, Reservation $reservation, BookingService $service)
    {
        $service->transition($reservation, $request->user(), 'cancelado');
        return back()->with('success', 'Reserva cancelada. El turno vuelve a estar disponible.');
    }
    public function updateStatus(Request $request, Reservation $reservation, BookingService $service)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Reservation::LABELS))]]);
        $service->transition($reservation, $request->user(), $data['status']);
        return back()->with('success', 'Estado actualizado. El estudiante puede verlo en sus reservas.');
    }
}
