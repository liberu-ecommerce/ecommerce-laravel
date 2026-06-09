<?php

namespace Tests\Unit;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(array $overrides = []): Page
    {
        return Page::create(array_merge([
            'title' => 'Test Page',
            'slug' => 'test-page-' . uniqid(),
            'content' => '<p>Hello world</p>',
            'status' => Page::STATUS_DRAFT,
        ], $overrides));
    }

    public function test_page_can_be_created(): void
    {
        $page = $this->makePage();

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Test Page', $page->title);
    }

    public function test_default_status_is_draft(): void
    {
        $page = $this->makePage();

        $this->assertEquals(Page::STATUS_DRAFT, $page->status);
    }

    public function test_is_published_returns_true_for_published_pages(): void
    {
        $page = $this->makePage(['status' => Page::STATUS_PUBLISHED]);

        $this->assertTrue($page->isPublished());
        $this->assertFalse($page->isDraft());
    }

    public function test_is_draft_returns_true_for_draft_pages(): void
    {
        $page = $this->makePage(['status' => Page::STATUS_DRAFT]);

        $this->assertTrue($page->isDraft());
        $this->assertFalse($page->isPublished());
    }

    public function test_published_scope_filters_correctly(): void
    {
        $published = $this->makePage(['status' => Page::STATUS_PUBLISHED]);
        $draft = $this->makePage(['status' => Page::STATUS_DRAFT]);

        $results = Page::published()->pluck('id');

        $this->assertContains($published->id, $results);
        $this->assertNotContains($draft->id, $results);
    }

    public function test_get_statuses_returns_array(): void
    {
        $statuses = Page::getStatuses();

        $this->assertArrayHasKey(Page::STATUS_DRAFT, $statuses);
        $this->assertArrayHasKey(Page::STATUS_PUBLISHED, $statuses);
    }

    public function test_status_constants_are_correct(): void
    {
        $this->assertEquals('draft', Page::STATUS_DRAFT);
        $this->assertEquals('published', Page::STATUS_PUBLISHED);
    }
}
