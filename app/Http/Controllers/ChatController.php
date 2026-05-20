<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Start a new chat conversation.
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
        ]);

        $conversation = $this->chatService->createConversation($validated);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = $this->chatService->addMessage($conversationId, [
            'message' => $validated['message'],
            'sender_type' => Auth::check() && Auth::user()->hasRole(['super_admin', 'admin']) ? 'agent' : 'customer',
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Get conversation messages.
     */
    public function getMessages($conversationId)
    {
        $conversation = $this->chatService->getConversation($conversationId);

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        // Mark messages as read
        $readerType = Auth::check() && Auth::user()->hasRole(['super_admin', 'admin']) ? 'agent' : 'customer';
        $this->chatService->markMessagesAsRead($conversationId, $readerType);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Get conversation by session ID.
     */
    public function getBySession($sessionId)
    {
        $conversation = $this->chatService->getConversationBySession($sessionId);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Close a conversation.
     */
    public function close($conversationId)
    {
        $conversation = $this->chatService->closeConversation($conversationId);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Submit satisfaction rating.
     */
    public function submitRating(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $this->chatService->addSatisfactionRating(
            $conversationId,
            $validated['rating'],
            $validated['feedback'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }

    /**
     * Get agent's active conversations (for agent dashboard).
     */
    public function agentConversations()
    {
        if (!Auth::check() || !Auth::user()->hasRole(['super_admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversations = $this->chatService->getAgentConversations(Auth::id());

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Assign agent to a conversation.
     */
    public function assignAgent(Request $request, $conversationId)
    {
        if (!Auth::check() || !Auth::user()->hasRole(['super_admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation = $this->chatService->assignAgent($conversationId, Auth::id());

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Get next queued conversation.
     */
    public function nextQueued()
    {
        if (!Auth::check() || !Auth::user()->hasRole(['super_admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation = $this->chatService->getNextQueuedConversation();

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }
}
