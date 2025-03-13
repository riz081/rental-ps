<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use HasFactory;
class Service extends Model
{
    protected $fillable = ['name', 'description', 'price'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
