<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    private ChatService $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = app(ChatService::class);
    }

    public function test_start_chat_creates_conversation(): void
    {
        $response = $this->postJson(route('chat.start'), [
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('conversation.status', 'queued');
    }

    public function test_start_chat_accepts_no_required_fields(): void
    {
        $response = $this->postJson(route('chat.start'), []);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_get_conversation_by_session(): void
    {
        $this->chatService->createConversation(['session_id' => 'sess_get_test']);

        $response = $this->get(route('chat.session', 'sess_get_test'));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_get_conversation_by_session_returns_null_for_unknown(): void
    {
        $response = $this->get(route('chat.session', 'unknown-session-xyz'));

        $response->assertStatus(200);
        $response->assertJsonPath('conversation', null);
    }

    public function test_send_message_to_conversation(): void
    {
        $conversation = $this->chatService->createConversation(['session_id' => 'sess_msg_test']);

        $response = $this->postJson(route('chat.message', $conversation->id), [
            'message' => 'Hello, I need help!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message.message', 'Hello, I need help!');
    }

    public function test_send_message_requires_message_field(): void
    {
        $conversation = $this->chatService->createConversation(['session_id' => 'sess_msg_validate']);

        $response = $this->postJson(route('chat.message', $conversation->id), []);

        $response->assertStatus(422);
    }

    public function test_get_messages_for_conversation(): void
    {
        $conversation = $this->chatService->createConversation(['session_id' => 'sess_msgs']);

        $response = $this->get(route('chat.messages', $conversation->id));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_get_messages_returns_404_for_unknown(): void
    {
        $response = $this->get(route('chat.messages', 99999));

        $response->assertStatus(404);
    }

    public function test_close_conversation(): void
    {
        $conversation = $this->chatService->createConversation(['session_id' => 'sess_close_test']);

        $response = $this->postJson(route('chat.close', $conversation->id));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('conversation.status', 'closed');
    }

    public function test_submit_satisfaction_rating(): void
    {
        $conversation = $this->chatService->createConversation(['session_id' => 'sess_rating_test']);

        $response = $this->postJson(route('chat.rating', $conversation->id), [
            'rating' => 5,
            'feedback' => 'Excellent!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_submit_rating_requires_valid_rating(): void
    {
        $conversation = $this->chatService->createConversation(['session_id' => 'sess_rating_val']);

        $response = $this->postJson(route('chat.rating', $conversation->id), [
            'rating' => 6,
        ]);

        $response->assertStatus(422);
    }

    public function test_assign_agent_requires_admin_role(): void
    {
        $user = User::factory()->create();
        $conversation = $this->chatService->createConversation(['session_id' => 'sess_agent_auth']);

        $response = $this->actingAs($user)
            ->postJson(route('chat.assign', $conversation->id));

        $response->assertStatus(403);
    }

    public function test_next_queued_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('chat.agent.next'));

        $response->assertStatus(403);
    }

    public function test_agent_conversations_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('chat.agent.conversations'));

        $response->assertStatus(403);
    }
}
