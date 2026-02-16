<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'agent_id',
        'status',
        'started_at',
        'ended_at',
        'queue_position',
        'customer_name',
        'customer_email',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the customer associated with the conversation.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the agent assigned to the conversation.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /**
     * Get the analytics for the conversation.
     */
    public function analytics(): HasOne
    {
        return $this->hasOne(ChatAnalytics::class, 'conversation_id');
    }

    /**
     * Scope to get active conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get queued conversations.
     */
    public function scopeQueued($query)
    {
        return $query->where('status', 'queued')->orderBy('queue_position');
    }

    /**
     * Scope to get conversations for a specific agent.
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }
}
