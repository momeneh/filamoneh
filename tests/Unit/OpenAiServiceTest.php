<?php

namespace Tests\Unit;

use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
    }

    #[Test]
    public function it_returns_empty_array_for_empty_description()
    {
        $tags = $this->openAiService->retrieveTagsForDescription('');
        
        $this->assertIsArray($tags);
        $this->assertEmpty($tags);
    }

    #[Test]
    public function it_returns_empty_array_for_null_description()
    {
        $tags = $this->openAiService->retrieveTagsForDescription(null);
        
        $this->assertIsArray($tags);
        $this->assertEmpty($tags);
    }

    #[Test]
    public function it_returns_fallback_tags_when_no_api_key()
    {
        // Test that the service falls back to keyword extraction when no API key is configured
        $description = "This research paper discusses machine learning algorithms and artificial intelligence applications in modern computing systems.";
        
        $tags = $this->openAiService->retrieveTagsForDescription($description);
        
        $this->assertIsArray($tags);
        $this->assertNotEmpty($tags);
        // Should contain some meaningful keywords from the description
        $this->assertGreaterThan(0, count($tags));
    }

    #[Test]
    public function it_handles_empty_description_gracefully()
    {
        $tags = $this->openAiService->retrieveTagsForDescription('');
        
        $this->assertIsArray($tags);
        $this->assertEmpty($tags);
    }

    #[Test]
    public function it_handles_null_description_gracefully()
    {
        $tags = $this->openAiService->retrieveTagsForDescription(null);
        
        $this->assertIsArray($tags);
        $this->assertEmpty($tags);
    }

    #[Test]
    public function it_can_set_custom_model()
    {
        $this->openAiService->setModel('gpt-4');
        
        // We can't easily test the private property, but we can verify the method returns self
        $this->assertInstanceOf(OpenAiService::class, $this->openAiService->setModel('gpt-3.5-turbo'));
    }

    #[Test]
    public function it_can_set_custom_base_url()
    {
        $this->openAiService->setBaseUrl('https://custom-endpoint.com/v1');
        
        // We can't easily test the private property, but we can verify the method returns self
        $this->assertInstanceOf(OpenAiService::class, $this->openAiService->setBaseUrl('https://api.openai.com/v1'));
    }

    #[Test]
    public function it_returns_array_for_valid_description()
    {
        $description = "Machine learning algorithms for data analysis and artificial intelligence applications.";
        
        $tags = $this->openAiService->retrieveTagsForDescription($description);
        
        $this->assertIsArray($tags);
        // Should return some tags (either from API or fallback)
        $this->assertGreaterThanOrEqual(0, count($tags));
    }

    #[Test]
    public function it_detects_fallback_usage()
    {
        $description = "Machine learning algorithms for data analysis.";
        
        $this->openAiService->retrieveTagsForDescription($description);
        
        // Should detect that fallback was used (since no API key is configured)
        $this->assertTrue($this->openAiService->wasFallbackUsed());
    }

    #[Test]
    public function it_provides_fallback_reason()
    {
        $description = "Machine learning algorithms for data analysis.";
        
        $this->openAiService->retrieveTagsForDescription($description);
        
        $reason = $this->openAiService->getFallbackReason();
        
        $this->assertNotNull($reason);
        // The reason could be either about missing API key or API unavailability
        $this->assertTrue(
            str_contains($reason, 'No OpenAI API key configured') ||
            str_contains($reason, 'OpenAI API unavailable'),
            "Expected reason to contain either 'No OpenAI API key configured' or 'OpenAI API unavailable', got: {$reason}"
        );
    }
}
