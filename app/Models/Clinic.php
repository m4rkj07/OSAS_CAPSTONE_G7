<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'full_description',
        'location',
        'status',
        'esi_level',
        'reported_by',
        'contact_info',
        'evidence_image',
        'user_id',
        'archived',
    ];

    protected $casts = [
        'archived' => 'boolean',
        'esi_level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the prefect report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
