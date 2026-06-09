<?php

namespace Tests\Unit;

use App\Models\ChatAnalytics;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatConversationModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeConversation(array $overrides = []): ChatConversation
    {
        return ChatConversation::create(array_merge([
            'session_id' => 'test-session-' . uniqid(),
            'status' => 'queued',
            'queue_position' => 1,
        ], $overrides));
    }

    public function test_conversation_can_be_created(): void
    {
        $conversation = $this->makeConversation();

        $this->assertInstanceOf(ChatConversation::class, $conversation);
        $this->assertDatabaseHas('chat_conversations', ['status' => 'queued']);
    }

    public function test_active_scope_returns_active_conversations(): void
    {
        $active = $this->makeConversation(['status' => 'active']);
        $queued = $this->makeConversation(['status' => 'queued', 'session_id' => 'sess_queued']);

        $results = ChatConversation::active()->get();

        $this->assertTrue($results->contains('id', $active->id));
        $this->assertFalse($results->contains('id', $queued->id));
    }

    public function test_queued_scope_returns_queued_conversations_ordered(): void
    {
        $first = $this->makeConversation(['status' => 'queued', 'queue_position' => 1, 'session_id' => 'sess1']);
        $second = $this->makeConversation(['status' => 'queued', 'queue_position' => 2, 'session_id' => 'sess2']);

        $results = ChatConversation::queued()->get();

        $this->assertEquals($first->id, $results->first()->id);
    }

    public function test_for_agent_scope_filters_by_agent(): void
    {
        $agent = User::factory()->create();
        $other = User::factory()->create();

        $agentConv = $this->makeConversation(['agent_id' => $agent->id, 'status' => 'active', 'session_id' => 'sess_agent']);
        $otherConv = $this->makeConversation(['agent_id' => $other->id, 'status' => 'active', 'session_id' => 'sess_other']);

        $results = ChatConversation::forAgent($agent->id)->get();

        $this->assertTrue($results->contains('id', $agentConv->id));
        $this->assertFalse($results->contains('id', $otherConv->id));
    }

    public function test_conversation_has_messages_relationship(): void
    {
        $conversation = $this->makeConversation();
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'customer',
            'message' => 'Hello!',
            'is_read' => false,
        ]);

        $this->assertCount(1, $conversation->messages);
    }

    public function test_conversation_has_analytics_relationship(): void
    {
        $conversation = $this->makeConversation();
        ChatAnalytics::create(['conversation_id' => $conversation->id]);

        $this->assertNotNull($conversation->analytics);
    }

    public function test_conversation_customer_relationship(): void
    {
        $user = User::factory()->create();
        $conversation = $this->makeConversation(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $conversation->customer);
        $this->assertEquals($user->id, $conversation->customer->id);
    }

    public function test_conversation_agent_relationship(): void
    {
        $agent = User::factory()->create();
        $conversation = $this->makeConversation(['agent_id' => $agent->id]);

        $this->assertInstanceOf(User::class, $conversation->agent);
        $this->assertEquals($agent->id, $conversation->agent->id);
    }

    public function test_started_at_cast_to_datetime(): void
    {
        $conversation = $this->makeConversation(['started_at' => now()]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $conversation->started_at);
    }
}
