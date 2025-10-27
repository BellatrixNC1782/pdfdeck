<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileVisitor extends Model
{
    use HasFactory;
    protected $table = 'profile_visitors';
    
    protected $fillable = [
        'ip',
        'browser',
        'system',
        'country',
        'country_code',
        'region',
        'city',
        'timezone',
        'lat',
        'lng',
        'isp',
        'organization',
        'guid',
        'page_type',
    ];

}
