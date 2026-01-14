<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Utility extends Model
{
    use HasFactory;

    // protected $table = 'deceased';

    // // Optional: specify primary key if not 'id'
    // protected $primaryKey = 'dd_id';

    // // Optional: allow mass assignment for columns
    // protected $fillable = [
    //     'dd_id',
    //     'hd_id',
    //     'dd_date_died',
    //     'dd_total_amt',
    //     'dd_amt_rcv', 
    //     'dd_dead_line', 
    //     'dd_status', 
    //     'created_at', 
    //     'updated_at' 
    // ]; 

    // Optional: if your table doesn't have timestamps
    public $timestamps = false;
}
