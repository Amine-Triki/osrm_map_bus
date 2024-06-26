<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'line_name',
        'points',
        'coordinates',
        'distance',
        'geometry',
    ];

    protected $dates = ['deleted_at']; // Permet à Eloquent de gérer le champ deleted_at
}
