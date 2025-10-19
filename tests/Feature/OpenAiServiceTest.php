<?php

namespace Tests\Feature;

use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OpenAiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OpenAiService $openAiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openAiService = new OpenAiService();
        Cache::flush();
    }
    #[Test]
    public function service_connects_to_ai()
    {

        $service = new OpenAiService();
        $description = 'This is a research paper about machine learning and artificial intelligence.';

        $result = $service->retrieveTagsForDescription($description);
        $this->assertFalse($service->wasFallbackUsed());
    }

    #[Test]
    public function service_returns_empty_array_for_null_description()
    {
        $result = $this->openAiService->retrieveTagsForDescription(null);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function service_returns_empty_array_for_empty_description()
    {
        $result = $this->openAiService->retrieveTagsForDescription('');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function service_uses_fallback_when_no_api_key()
    {
        // Mock the config to return empty API key
        config(['services.openai.api_key' => '']);

        $service = new OpenAiService();
        $description = 'This is a research paper about machine learning and artificial intelligence.';

        $result = $service->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertTrue($service->wasFallbackUsed());
        $this->assertEquals('No OpenAI API key configured', $service->getFallbackReason());
    }

    #[Test]
    public function service_successfully_extracts_tags_from_openai_api()
    {
        // Mock HTTP response for successful OpenAI API call
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'machine learning, artificial intelligence, neural networks, deep learning, algorithms'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $description = 'This paper presents a novel approach to machine learning using neural networks and deep learning algorithms.';
        $result = $this->openAiService->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertFalse($this->openAiService->wasFallbackUsed());
        
        // Check that the tags are properly parsed
        $expectedTags = ['machine learning', 'artificial intelligence', 'neural networks', 'deep learning', 'algorithms'];
        $this->assertEquals($expectedTags, $result);
    }

    #[Test]
    public function service_handles_openai_api_failure_gracefully()
    {
        // Mock HTTP response for failed OpenAI API call
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'error' => [
                    'type' => 'insufficient_quota',
                    'message' => 'You exceeded your current quota'
                ]
            ], 429)
        ]);

        $description = 'This paper discusses quantum computing and its applications in cryptography.';
        $result = $this->openAiService->retrieveTagsForDescription($description);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertTrue($this->openAiService->wasFallbackUsed());
    }

    #[Test]
    public function service_caches_openai_responses()
    {
        // Mock HTTP response
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'quantum computing, cryptography, security'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $description = 'This paper explores quantum computing applications in cryptography.';
        
        // First call should hit the API
        $result1 = $this->openAiService->retrieveTagsForDescription($description);
        
        // Second call should use cache
        $result2 = $this->openAiService->retrieveTagsForDescription($description);

        $this->assertEquals($result1, $result2);
        
        // Verify only one HTTP request was made
        Http::assertSentCount(1);
    }

    #[Test]
    public function service_handles_authentication_error()
    {
        // Mock HTTP response for authentication error
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'error' => [
                    'type' => 'invalid_request_error',
                    'message' => 'Invalid API key'
                ]
            ], 401)
        ]);

        $description = 'This paper discusses blockchain technology.';
        $result = $this->openAiService->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertTrue($this->openAiService->wasFallbackUsed());
    }

    #[Test]
    public function service_handles_rate_limiting()
    {
        // Mock HTTP response for rate limiting
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'error' => [
                    'type' => 'rate_limit_exceeded',
                    'message' => 'Rate limit exceeded'
                ]
            ], 429)
        ]);

        $description = 'This paper presents new findings in renewable energy.';
        $result = $this->openAiService->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertTrue($this->openAiService->wasFallbackUsed());
    }

    #[Test]
    public function fallback_extraction_works_correctly()
    {
        // Test fallback extraction directly
        $description = 'This research paper examines machine learning algorithms and neural networks for image recognition tasks in computer vision applications.';
        
        // Force fallback by not providing API key
        config(['services.openai.api_key' => '']);
        $service = new OpenAiService();
        
        $result = $service->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertTrue($service->wasFallbackUsed());
        
        // Check that meaningful keywords are extracted
        $this->assertContains('machine', $result);
        $this->assertContains('learning', $result);
        $this->assertContains('algorithms', $result);
    }

    #[Test]
    public function service_parses_tags_correctly_from_response()
    {
        // Mock HTTP response with various tag formats
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'machine learning, artificial intelligence, neural networks, deep learning, algorithms, data science, computer vision, natural language processing, robotics, automation'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $description = 'This paper covers various AI topics.';
        $result = $this->openAiService->retrieveTagsForDescription($description);
        $this->assertFalse($this->openAiService->wasFallbackUsed());
        $this->assertIsArray($result);
        $this->assertCount(10, $result);
        $this->assertContains('machine learning', $result);
        $this->assertContains('artificial intelligence', $result);
        $this->assertContains('neural networks', $result);
    }

    #[Test]
    public function service_cleans_up_tag_formatting()
    {
        // Mock HTTP response with messy formatting
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '  machine learning  ,  artificial intelligence  ,  neural networks  ,  deep learning  ,  algorithms  '
                        ]
                    ]
                ]
            ], 200)
        ]);

        $description = 'This paper discusses AI topics.';
        $result = $this->openAiService->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        
        // Check that tags are properly trimmed
        foreach ($result as $tag) {
            $this->assertNotEquals(' ', substr($tag, 0, 1), 'Tag should not start with space');
            $this->assertNotEquals(' ', substr($tag, -1), 'Tag should not end with space');
        }
    }

    #[Test]
    public function service_limits_number_of_tags()
    {
        // Mock HTTP response with many tags
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'tag1, tag2, tag3, tag4, tag5, tag6, tag7, tag8, tag9, tag10, tag11, tag12, tag13, tag14, tag15'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $description = 'This paper has many topics.';
        $result = $this->openAiService->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result));
    }

    #[Test]
    public function service_can_be_configured_with_different_model()
    {
        $service = new OpenAiService();
        $service->setModel('gpt-4');

        // This test verifies the method exists and doesn't throw an error
        $this->assertInstanceOf(OpenAiService::class, $service);
    }

    #[Test]
    public function service_can_be_configured_with_different_base_url()
    {
        $service = new OpenAiService();
        $service->setBaseUrl('https://custom-api.com/v1');

        // This test verifies the method exists and doesn't throw an error
        $this->assertInstanceOf(OpenAiService::class, $service);
    }

    #[Test]
    public function service_logs_errors_appropriately()
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('OpenAI API key not configured. Using fallback tag extraction.');

        // Mock the config to return empty API key
        config(['services.openai.api_key' => '']);

        $service = new OpenAiService();
        $service->retrieveTagsForDescription('Test description');
    }

    #[Test]
    public function service_handles_html_in_description()
    {
        // Mock HTTP response
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'machine learning, artificial intelligence, html parsing'
                        ]
                    ]
                ]
            ], 200)
        ]);

        $description = '<p>This paper discusses <strong>machine learning</strong> and <em>artificial intelligence</em> topics.</p>';
        $result = $this->openAiService->retrieveTagsForDescription($description);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }
}
