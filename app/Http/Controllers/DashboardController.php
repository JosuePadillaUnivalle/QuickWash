<?php
namespace App\Http\Controllers;
use App\Models\{Machine, Reservation};
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Reservation::query();
        if (!$request->user()->isStaff()) $query->where('user_id', $request->user()->id);
        $active = (clone $query)->whereIn('status', Reservation::ACTIVE)->count();
        $ready = (clone $query)->where('status', 'listo')->count();
        $completed = (clone $query)->where('status', 'entregado')->count();
        $recent = (clone $query)->with(['machine', 'user'])->latest()->limit(5)->get();
        $machines = Machine::orderBy('name')->get();
        $available = $machines->where('status', 'disponible')->count();
        return view('dashboard', compact('active', 'ready', 'completed', 'recent', 'machines', 'available'));
    }
}
