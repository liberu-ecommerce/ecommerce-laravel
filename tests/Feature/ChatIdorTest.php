<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChatIdorTest extends TestCase
{
    use RefreshDatabase;

    private function conversation(string $sessionId = 'sess_victim'): ChatConversation
    {
        return app(ChatService::class)->createConversation([
            'session_id' => $sessionId,
            'customer_email' => 'victim@example.com',
        ]);
    }

    public function test_cannot_read_another_sessions_conversation(): void
    {
        $conv = $this->conversation();

        // No session ownership, not an agent — must not dump the conversation.
        $this->getJson(route('chat.messages', $conv->id))->assertStatus(403);
    }

    public function test_cannot_send_message_to_another_sessions_conversation(): void
    {
        $conv = $this->conversation();

        $this->postJson(route('chat.message', $conv->id), ['message' => 'hi'])->assertStatus(403);
    }

    public function test_cannot_close_another_sessions_conversation(): void
    {
        $conv = $this->conversation();

        $this->postJson(route('chat.close', $conv->id))->assertStatus(403);
    }

    public function test_owner_with_session_can_read_own_conversation(): void
    {
        $conv = $this->conversation('sess_owner');

        $this->withSession(['chat_conversations' => ['sess_owner']])
            ->getJson(route('chat.messages', $conv->id))
            ->assertStatus(200);
    }

    public function test_admin_can_read_any_conversation(): void
    {
        Role::findOrCreate('super_admin', 'web');
        $admin = User::factory()->create()->assignRole('super_admin');
        $conv = $this->conversation();

        $this->actingAs($admin)->getJson(route('chat.messages', $conv->id))->assertStatus(200);
    }

    public function test_starting_a_chat_grants_that_session_access(): void
    {
        $id = $this->postJson(route('chat.start'), ['customer_email' => 'me@example.com'])
            ->json('conversation.id');

        // The start request bound this conversation's session to the browser session.
        $this->getJson(route('chat.messages', $id))->assertStatus(200);
    }
}
