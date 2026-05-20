<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatAnalytics;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ChatService
{
    /**
     * Create a new chat conversation.
     */
    public function createConversation(array $data): ChatConversation
    {
        $maxQueuePosition = ChatConversation::where('status', 'queued')->max('queue_position') ?? 0;

        $conversation = ChatConversation::create([
            'session_id' => $data['session_id'] ?? Str::uuid()->toString(),
            'user_id' => $data['user_id'] ?? Auth::id(),
            'status' => 'queued',
            'queue_position' => $maxQueuePosition + 1,
            'customer_name' => $data['customer_name'] ?? (Auth::check() ? Auth::user()->name : null),
            'customer_email' => $data['customer_email'] ?? (Auth::check() ? Auth::user()->email : null),
        ]);

        // Create analytics record
        ChatAnalytics::create([
            'conversation_id' => $conversation->id,
        ]);

        // Send welcome message
        $this->addMessage($conversation->id, [
            'message' => 'Thank you for contacting us. An agent will be with you shortly.',
            'sender_type' => 'system',
        ]);

        return $conversation->load('messages');
    }

    /**
     * Add a message to a conversation.
     */
    public function addMessage(int $conversationId, array $data): ChatMessage
    {
        $message = ChatMessage::create([
            'conversation_id' => $conversationId,
            'user_id' => $data['user_id'] ?? Auth::id(),
            'sender_type' => $data['sender_type'] ?? 'customer',
            'message' => $data['message'],
            'is_read' => false,
        ]);

        // Update analytics
        $this->updateAnalytics($conversationId);

        return $message;
    }

    /**
     * Assign an agent to a conversation.
     */
    public function assignAgent(int $conversationId, int $agentId): ChatConversation
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        
        $conversation->update([
            'agent_id' => $agentId,
            'status' => 'active',
            'started_at' => now(),
        ]);

        // Calculate response time
        $analytics = $conversation->analytics;
        if ($analytics && !$analytics->response_time_seconds) {
            $responseTime = now()->diffInSeconds($conversation->created_at);
            $analytics->update(['response_time_seconds' => $responseTime]);
        }

        // Send agent joined message
        $this->addMessage($conversationId, [
            'message' => 'An agent has joined the chat.',
            'sender_type' => 'system',
        ]);

        return $conversation->fresh();
    }

    /**
     * Close a conversation.
     */
    public function closeConversation(int $conversationId): ChatConversation
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        
        $conversation->update([
            'status' => 'closed',
            'ended_at' => now(),
        ]);

        // Update resolution time
        $analytics = $conversation->analytics;
        if ($analytics && $conversation->started_at) {
            $resolutionTime = now()->diffInSeconds($conversation->started_at);
            $analytics->update(['resolution_time_seconds' => $resolutionTime]);
        }

        return $conversation->fresh();
    }

    /**
     * Get next conversation from queue.
     */
    public function getNextQueuedConversation(): ?ChatConversation
    {
        return ChatConversation::queued()->first();
    }

    /**
     * Get active conversations for an agent.
     */
    public function getAgentConversations(int $agentId)
    {
        return ChatConversation::forAgent($agentId)
            ->where('status', 'active')
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Mark messages as read.
     */
    public function markMessagesAsRead(int $conversationId, string $readerType = 'agent'): void
    {
        ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_type', '!=', $readerType)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Update analytics for a conversation.
     */
    protected function updateAnalytics(int $conversationId): void
    {
        $analytics = ChatAnalytics::where('conversation_id', $conversationId)->first();
        
        if ($analytics) {
            $messages = ChatMessage::where('conversation_id', $conversationId)->get();
            
            $analytics->update([
                'message_count' => $messages->count(),
                'agent_message_count' => $messages->where('sender_type', 'agent')->count(),
                'customer_message_count' => $messages->where('sender_type', 'customer')->count(),
            ]);
        }
    }

    /**
     * Add satisfaction rating.
     */
    public function addSatisfactionRating(int $conversationId, int $rating, ?string $feedback = null): void
    {
        $analytics = ChatAnalytics::where('conversation_id', $conversationId)->first();
        
        if ($analytics) {
            $analytics->update([
                'satisfaction_rating' => $rating,
                'satisfaction_feedback' => $feedback,
            ]);
        }
    }

    /**
     * Get conversation with messages.
     */
    public function getConversation(int $conversationId): ?ChatConversation
    {
        return ChatConversation::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }, 'agent', 'customer'])->find($conversationId);
    }

    /**
     * Get conversation by session ID.
     */
    public function getConversationBySession(string $sessionId): ?ChatConversation
    {
        return ChatConversation::where('session_id', $sessionId)
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->first();
    }
}
