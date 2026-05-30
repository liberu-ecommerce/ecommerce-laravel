<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class ChatWidget extends Component
{
    public bool $isOpen = false;
    public string $sessionId = '';
    public ?int $conversationId = null;
    public array $messages = [];
    public string $newMessage = '';
    public bool $isLoading = false;
    public bool $showRating = false;
    public int $rating = 0;
    public string $feedback = '';

    public function mount(): void
    {
        $this->sessionId = session('chat_session_id', Str::uuid()->toString());
        session(['chat_session_id' => $this->sessionId]);
    }

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen && !$this->conversationId) {
            $this->loadOrCreateConversation();
        }
    }

    public function loadOrCreateConversation(): void
    {
        $this->isLoading = true;
        $this->dispatch('chat-conversation-started');
        $this->isLoading = false;
    }

    public function sendMessage(): void
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

    public function closeChat(): void
    {
        if ($this->conversationId) {
            $this->showRating = true;
        } else {
            $this->isOpen = false;
        }
    }

    public function submitRating(): void
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
