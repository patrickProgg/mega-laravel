<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user_hd';

    protected $primaryKey = 'hd_id';

    protected $appends = ['full_name', 'address', 'province_name', 'city_name', 'barangay_name', 'purok_name'];

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
        'email',
        'password',
        'birthday',
        'age',
        'phone1',
        'phone2',
        'purok',
        'barangay',
        'city',
        'province',
        'zip_code',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->fname} {$this->lname}");
    }

    // Relationships to address tables
    // public function provinceRelation()
    // {
    //     return $this->belongsTo(AddressProvince::class, 'province', 'id');
    // }

    // public function cityRelation()
    // {
    //     return $this->belongsTo(AddressCity::class, 'city', 'id');
    // }

    // public function barangayRelation()
    // {
    //     return $this->belongsTo(AddressBarangay::class, 'barangay', 'id');
    // }

    // public function purokRelation()
    // {
    //     return $this->belongsTo(AddressPurok::class, 'purok', 'id');
    // }

    // Accessors for address names
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

    public function members()
    {
        return $this->hasMany(User_ln::class, 'hd_id', 'hd_id'); 
    }
}