<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Reservation extends Model
{
    public const ACTIVE = ['pendiente', 'en_proceso', 'listo'];
    public const LABELS = ['pendiente' => 'Pendiente', 'en_proceso' => 'En proceso', 'listo' => 'Listo para recoger', 'entregado' => 'Entregado', 'cancelado' => 'Cancelado'];
    public const TRANSITIONS = ['pendiente' => ['en_proceso', 'cancelado'], 'en_proceso' => ['listo'], 'listo' => ['entregado'], 'entregado' => [], 'cancelado' => []];
    protected $fillable = ['user_id', 'machine_id', 'starts_at', 'ends_at', 'status'];
    protected function casts(): array { return ['starts_at' => 'datetime', 'ends_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function machine() { return $this->belongsTo(Machine::class)->withTrashed(); }
    public function getCodeAttribute(): string { return 'QW-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT); }
}
