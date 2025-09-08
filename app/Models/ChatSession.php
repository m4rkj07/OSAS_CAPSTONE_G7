<?php

// === Updated ChatSession Model (app/Models/ChatSession.php) ===
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'status',
        'context',
        'incident_data',
        'language',
        'last_message',
        'report_id' // Link to your existing Report model
    ];

    protected $casts = [
        'context' => 'array',
        'incident_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}