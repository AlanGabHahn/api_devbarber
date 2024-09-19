<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAppointment extends Model
{
    use HasFactory;

    protected $table = 'userappointments';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'barber_id',
        'ap_datetime'
    ];
}
