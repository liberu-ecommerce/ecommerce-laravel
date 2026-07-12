<?php

namespace Tests\Unit;

use App\Models\ChatAnalytics;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChatService();
    }

    public function test_create_conversation_returns_conversation_with_messages(): void
    {
        $conversation = $this->service->createConversation([
            'session_id' => 'sess_001',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(ChatConversation::class, $conversation);
        $this->assertEquals('queued', $conversation->status);
        $this->assertEquals('John Doe', $conversation->customer_name);
        $this->assertCount(1, $conversation->messages);
    }

    public function test_create_conversation_sets_queue_position(): void
    {
        $first = $this->service->createConversation(['session_id' => 'sess_q1']);
        $second = $this->service->createConversation(['session_id' => 'sess_q2']);

        $this->assertEquals(1, $first->queue_position);
        $this->assertEquals(2, $second->queue_position);
    }

    public function test_create_conversation_creates_analytics_record(): void
    {
        $conversation = $this->service->createConversation(['session_id' => 'sess_analytics']);

        $this->assertNotNull($conversation->analytics);
        $this->assertInstanceOf(ChatAnalytics::class, $conversation->analytics);
    }

    public function test_add_message_creates_message(): void
    {
        $conversation = $this->service->createConversation(['session_id' => 'sess_msg']);

        $message = $this->service->addMessage($conversation->id, [
            'message' => 'Hello, I need help.',
            'sender_type' => 'customer',
        ]);

        $this->assertInstanceOf(ChatMessage::class, $message);
        $this->assertEquals('Hello, I need help.', $message->message);
        $this->assertEquals('customer', $message->sender_type);
        $this->assertFalse($message->is_read);
    }

    public function test_add_message_updates_analytics(): void
    {
        $conversation = $this->service->createConversation(['session_id' => 'sess_analytics_update']);

        $this->service->addMessage($conversation->id, [
            'message' => 'Customer message',
            'sender_type' => 'customer',
        ]);

        $analytics = ChatAnalytics::where('conversation_id', $conversation->id)->first();
        $this->assertGreaterThan(0, $analytics->customer_message_count);
    }

    public function test_assign_agent_updates_conversation_status(): void
    {
        $agent = User::factory()->create();
        $conversation = $this->service->createConversation(['session_id' => 'sess_assign']);

        $updated = $this->service->assignAgent($conversation->id, $agent->id);

        $this->assertEquals('active', $updated->status);
        $this->assertEquals($agent->id, $updated->agent_id);
        $this->assertNotNull($updated->started_at);
    }

    public function test_assign_agent_sends_system_message(): void
    {
        $agent = User::factory()->create();
        $conversation = $this->service->createConversation(['session_id' => 'sess_agent_msg']);
        $messageCountBefore = $conversation->messages()->count();

        $this->service->assignAgent($conversation->id, $agent->id);

        $this->assertGreaterThan($messageCountBefore, $conversation->messages()->count());
    }

    public function test_close_conversation_sets_status_closed(): void
    {
        $conversation = $this->service->createConversation(['session_id' => 'sess_close']);
        $agent = User::factory()->create();
        $this->service->assignAgent($conversation->id, $agent->id);

        $closed = $this->service->closeConversation($conversation->id);

        $this->assertEquals('closed', $closed->status);
        $this->assertNotNull($closed->ended_at);
    }

    public function test_get_next_queued_conversation_returns_oldest(): void
    {
        $first = $this->service->createConversation(['session_id' => 'sess_first']);
        $second = $this->service->createConversation(['session_id' => 'sess_second']);

        $next = $this->service->getNextQueuedConversation();

        $this->assertEquals($first->id, $next->id);
    }

    public function test_get_next_queued_returns_null_when_empty(): void
    {
        $next = $this->service->getNextQueuedConversation();

        $this->assertNull($next);
    }

    public function test_mark_messages_as_read_marks_unread_messages(): void
    {
        $conversation = $this->service->createConversation(['session_id' => 'sess_read']);
        $this->service->addMessage($conversation->id, [
            'message' => 'Customer msg',
            'sender_type' => 'customer',
        ]);

        $this->service->markMessagesAsRead($conversation->id, 'agent');

        $unread = ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->count();

        $this->assertEquals(0, $unread);
    }

    public function test_add_satisfaction_rating_updates_analytics(): void
    {
        $conversation = $this->service->createConversation(['session_id' => 'sess_rating']);

        $this->service->addSatisfactionRating($conversation->id, 5, 'Excellent service!');

        $analytics = ChatAnalytics::where('conversation_id', $conversation->id)->first();
        $this->assertEquals(5, $analytics->satisfaction_rating);
        $this->assertEquals('Excellent service!', $analytics->satisfaction_feedback);
    }

    public function test_get_conversation_returns_with_messages(): void
    {
        $conversation = $this->service->createConversation(['session_id' => 'sess_get']);

        $result = $this->service->getConversation($conversation->id);

        $this->assertNotNull($result);
        $this->assertInstanceOf(ChatConversation::class, $result);
        $this->assertTrue($result->relationLoaded('messages'));
    }

    public function test_get_conversation_returns_null_for_unknown_id(): void
    {
        $result = $this->service->getConversation(99999);

        $this->assertNull($result);
    }

    public function test_get_conversation_by_session(): void
    {
        $this->service->createConversation(['session_id' => 'known_session']);

        $result = $this->service->getConversationBySession('known_session');

        $this->assertNotNull($result);
        $this->assertEquals('known_session', $result->session_id);
    }

    public function test_get_conversation_by_session_returns_null_for_unknown(): void
    {
        $result = $this->service->getConversationBySession('unknown_session');

        $this->assertNull($result);
    }

    public function test_get_agent_conversations_returns_active_for_agent(): void
    {
        $agent = User::factory()->create();
        $conversation = $this->service->createConversation(['session_id' => 'sess_agent_list']);
        $this->service->assignAgent($conversation->id, $agent->id);

        $results = $this->service->getAgentConversations($agent->id);

        $this->assertCount(1, $results);
        $this->assertEquals($conversation->id, $results->first()->id);
    }

    public function test_assign_agent_records_positive_response_time(): void
    {
        $agent = User::factory()->create();
        $this->freezeTime();
        $conversation = $this->service->createConversation(['session_id' => 'sess_rt']);

        $this->travel(30)->seconds();
        $this->service->assignAgent($conversation->id, $agent->id);

        $analytics = ChatAnalytics::where('conversation_id', $conversation->id)->first();
        $this->assertGreaterThanOrEqual(0, $analytics->response_time_seconds);
        $this->assertEqualsWithDelta(30, $analytics->response_time_seconds, 1);
    }

    public function test_close_conversation_records_positive_resolution_time(): void
    {
        $agent = User::factory()->create();
        $this->freezeTime();
        $conversation = $this->service->createConversation(['session_id' => 'sess_res']);
        $this->service->assignAgent($conversation->id, $agent->id);

        $this->travel(60)->seconds();
        $this->service->closeConversation($conversation->id);

        $analytics = ChatAnalytics::where('conversation_id', $conversation->id)->first();
        $this->assertGreaterThanOrEqual(0, $analytics->resolution_time_seconds);
        $this->assertEqualsWithDelta(60, $analytics->resolution_time_seconds, 1);
    }
}
