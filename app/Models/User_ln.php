<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

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
        'fname',
        'mname',
        'lname',
        'birthday',
        'age',
        'phone1',
        'phone2',
        'relation',
        'purok',
        'barangay',
        'city',
        'province',
        'zip_code',
        'status',
    ];

    protected $appends = ['full_name', 'address'];

    public function getFullNameAttribute()
    {
        return trim("{$this->fname} {$this->lname}");
    }

    public function getProvinceNameAttribute()
    {
        if (empty($this->province)) {
            return null;
        }
        
        // Check if it's numeric (ID) or already a name
        if (is_numeric($this->province)) {
            $province = DB::table('address_provinces')->where('id', $this->province)->first();
            return $province ? $province->name : $this->province;
        }
        
        return $this->province;
    }

    public function getCityNameAttribute()
    {
        if (empty($this->city)) {
            return null;
        }
        
        if (is_numeric($this->city)) {
            $city = DB::table('address_cities')->where('id', $this->city)->first();
            return $city ? $city->name : $this->city;
        }
        
        return $this->city;
    }

    public function getBarangayNameAttribute()
    {
        if (empty($this->barangay)) {
            return null;
        }
        
        if (is_numeric($this->barangay)) {
            $barangay = DB::table('address_barangays')->where('id', $this->barangay)->first();
            return $barangay ? $barangay->name : $this->barangay;
        }
        
        return $this->barangay;
    }

    public function getPurokNameAttribute()
    {
        if (empty($this->purok)) {
            return null;
        }
        
        if (is_numeric($this->purok)) {
            $purok = DB::table('address_puroks')->where('id', $this->purok)->first();
            return $purok ? $purok->name : $this->purok;
        }
        
        return $this->purok;
    }

    // Updated address attribute that uses the names
    public function getAddressAttribute()
    {
        $parts = [];
        
        // Add purok if exists
        if ($this->purok_name) {
            $parts[] = $this->purok_name;
        }
        
        // Add barangay if exists
        if ($this->barangay_name) {
            $parts[] = $this->barangay_name;
        }
        
        // Add city if exists
        if ($this->city_name) {
            $parts[] = $this->city_name;
        }
        
        // Add province if exists
        if ($this->province_name) {
            $parts[] = $this->province_name;
        }
        
        // Add zip code if exists
        if ($this->zip_code) {
            $parts[] = $this->zip_code;
        }
        
        return implode(', ', $parts);
    }

}
