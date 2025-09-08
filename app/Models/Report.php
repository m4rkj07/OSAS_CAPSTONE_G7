<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'incident_type',
        'full_description',
        'location',
        'created_at',
        'status',
        'reported_by',
        'contact_info',
        'esi_level',
        'evidence_image',
        'user_id',
        'archived',
        'transfer_report',
        'assigned_officer_id',
    ];
    
    public $timestamps = true;

    protected $casts = [
        'archived' => 'boolean',
    ];

     public function scopeActive($query)
    {
        return $query->where('archived', false);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function chatSession()
    {
        return $this->hasOne(ChatSession::class, 'report_id');
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }
        
}
