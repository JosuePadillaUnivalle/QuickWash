<?php
namespace App\Http\Controllers;
use App\Models\{Machine, Reservation};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class MachineController extends Controller
{
    public function index() { return view('machines.index', ['machines' => Machine::orderBy('name')->get()]); }
    public function create() { return view('machines.form', ['machine' => new Machine]); }
    public function edit(Machine $machine) { return view('machines.form', compact('machine')); }
    private function validated(Request $request, ?Machine $machine = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:60', Rule::unique('machines')->ignore($machine?->id)], 'type' => ['required', Rule::in(['lavadora', 'secadora'])], 'capacity' => 'required|integer|min:1|max:50', 'location' => 'required|string|max:100', 'status' => ['required', Rule::in(['disponible', 'mantenimiento'])]]);
    }
    public function store(Request $request)
    {
        Machine::create($this->validated($request));
        return redirect()->route('machines.index')->with('success', 'Máquina creada correctamente.');
    }
    public function update(Request $request, Machine $machine)
    {
        $data = $this->validated($request, $machine);
        DB::transaction(function () use ($machine, $data) {
            $locked = Machine::whereKey($machine->id)->lockForUpdate()->firstOrFail();
            if ($data['status'] === 'mantenimiento' && $locked->reservations()->whereIn('status', Reservation::ACTIVE)->exists()) throw ValidationException::withMessages(['status' => 'No puedes poner en mantenimiento una máquina con reservas activas.']);
            $locked->update($data);
        }, 3);
        return redirect()->route('machines.index')->with('success', 'Máquina actualizada.');
    }
    public function destroy(Machine $machine)
    {
        DB::transaction(function () use ($machine) {
            $locked = Machine::whereKey($machine->id)->lockForUpdate()->firstOrFail();
            if ($locked->reservations()->whereIn('status', Reservation::ACTIVE)->exists()) throw ValidationException::withMessages(['machine' => 'No puedes eliminar una máquina con reservas activas.']);
            $locked->delete();
        }, 3);
        return back()->with('success', 'Máquina eliminada. Su historial de reservas se conserva.');
    }
}
