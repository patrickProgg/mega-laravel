<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User_ln extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'User_ln';
    protected $primaryKey = 'ln_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'hd_id',
        'phone1',
        'fname',
        'lname',
        'email',
        'status',
        'password',
    ];

}
