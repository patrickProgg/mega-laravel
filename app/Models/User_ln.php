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

    protected $appends = ['full_name', 'address'];

    public function getFullNameAttribute()
    {
        return trim("{$this->fname} {$this->lname}");
    }

    public function getAddressAttribute()
    {
        return trim("{$this->barangay} {$this->city} {$this->province}");
    }

}
