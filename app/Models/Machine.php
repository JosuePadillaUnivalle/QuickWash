<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Machine extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'type', 'capacity', 'location', 'status'];
    public function reservations() { return $this->hasMany(Reservation::class); }
}
