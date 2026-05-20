<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class ChatWidget extends Component
{
    public $isOpen = false;
    public $sessionId;
    public $conversationId = null;
    public $messages = [];
    public $newMessage = '';
    public $isLoading = false;
    public $showRating = false;
    public $rating = 0;
    public $feedback = '';

    public function mount()
    {
        // Generate or retrieve session ID from browser storage
        $this->sessionId = session('chat_session_id', Str::uuid()->toString());
        session(['chat_session_id' => $this->sessionId]);
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        
        if ($this->isOpen && !$this->conversationId) {
            $this->loadOrCreateConversation();
        }
    }

    public function loadOrCreateConversation()
    {
        $this->isLoading = true;
        
        // This would typically call the API
        $this->dispatch('chat-conversation-started');
        
        $this->isLoading = false;
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) {
            return;
        }

        $this->dispatch('chat-send-message', [
            'message' => $this->newMessage,
            'conversationId' => $this->conversationId,
        ]);

        $this->newMessage = '';
    }

    public function closeChat()
    {
        if ($this->conversationId) {
            $this->showRating = true;
        } else {
            $this->isOpen = false;
        }
    }

    public function submitRating()
    {
        if ($this->rating > 0) {
            $this->dispatch('chat-submit-rating', [
                'conversationId' => $this->conversationId,
                'rating' => $this->rating,
                'feedback' => $this->feedback,
            ]);
        }
        
        $this->reset(['isOpen', 'conversationId', 'messages', 'showRating', 'rating', 'feedback']);
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
