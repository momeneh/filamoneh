<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenAiService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $model = 'gpt-3.5-turbo';
    protected bool $usedFallback = false;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?? '';
        $this->baseUrl = config('services.openai.base_url') ?? 'https://api.openai.com/v1';
        $this->model = config('services.openai.model') ?? 'gpt-3.5-turbo';
    }

    /**
     * Extract relevant tags from a paper description
     *
     * @param string|null $description
     * @return array
     */
    public function retrieveTagsForDescription(?string $description): array
    {
        if (empty($description)) {
            return [];
        }

        try {
            // Reset fallback flag
            $this->usedFallback = false;
            
            // Check if we have API key configured
            if (empty($this->apiKey)) {
                Log::warning('OpenAI API key not configured. Using fallback tag extraction.');
                $this->usedFallback = true;
                return $this->fallbackTagExtraction($description);
            }

            $response = $this->makeOpenAiRequest($description);
            
            if ($response && isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                return $this->parseTagsFromResponse($content);
            }

            // If we get here, it means the API call failed and we need to use fallback
            // Log::info('Using fallback tag extraction due to API failure');
            $this->usedFallback = true;
            return $this->fallbackTagExtraction($description);
            // return [];

        } catch (\Exception $e) {
            Log::error('Error calling OpenAI API', [
                'message' => $e->getMessage(),
                'description_length' => strlen($description)
            ]);
            
            $this->usedFallback = true;
            return $this->fallbackTagExtraction($description);
        }
    }

    /**
     * Make a request to OpenAI API
     *
     * @param string $description
     * @return array|null
     */
    protected function makeOpenAiRequest(string $description): ?array
    {
        $cacheKey = 'openai_tags2_' . md5($description);
        
        // Check cache first to avoid repeated API calls
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $prompt = $this->buildPrompt($description);
        
        $response = Http::withToken($this->apiKey)
        ->asJson()
        ->acceptJson()
        // withHeaders([
        //     'Authorization' => 'Bearer ' . $this->apiKey,
        //     'Content-Type' => 'application/json',
        // ])
        ->post($this->baseUrl . '/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that extracts relevant academic tags from research paper descriptions. Return only a comma-separated list of tags, no explanations.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 150,
            'temperature' => 0.3,
        ]);

        if ($response->successful()) {
            $result = $response->json();
            info($result);
            // Cache the result for 1 hour to avoid repeated API calls
            Cache::put($cacheKey, $result, now()->addHour());
            return $result;
        }

        // Handle specific error cases
        $errorBody = $response->json();
        $errorType = $errorBody['error']['type'] ?? 'unknown';
        $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';

        // Log the specific error
        Log::warning('OpenAI API request failed', [
            'status' => $response->status(),
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'description_length' => strlen($description)
        ]);

        // Handle quota issues specifically
        if (in_array($errorType, ['insufficient_quota', 'quota_exceeded'])) {
            Log::warning('OpenAI API quota exceeded - using fallback tag extraction');
            return null; // This will trigger fallback
        }

        // Handle rate limiting
        if ($response->status() === 429) {
            Log::warning('OpenAI API rate limited - using fallback tag extraction');
            return null; // This will trigger fallback
        }

        // Handle authentication errors
        if ($response->status() === 401) {
            Log::error('OpenAI API authentication failed - check API key');
            return null; // This will trigger fallback
        }

        return null;
    }

    /**
     * Build the prompt for tag extraction
     *
     * @param string $description
     * @return string
     */
    protected function buildPrompt(string $description): string
    {
        return "Extract 5-10 relevant academic tags from this research paper description. Focus on key concepts, methodologies, and subject areas.The text may contain HTML tags ,ignore them . Return only the tags separated by commas:\n\n{$description}";
    }

    /**
     * Parse tags from OpenAI response
     *
     * @param string $response
     * @return array
     */
    protected function parseTagsFromResponse(string $response): array
    {
        // Clean up the response and extract tags
        $tags = array_map('trim', explode(',', $response));
        
        // Filter out empty tags and clean them
        $tags = array_filter($tags, function($tag) {
            return !empty($tag) && strlen($tag) > 1;
        });

        // Clean up each tag (remove extra punctuation, normalize)
        $tags = array_map(function($tag) {
            return trim($tag, " \t\n\r\0\x0B.,;:!?\"'()[]{}");
        }, $tags);

        // Remove duplicates and limit to 10 tags
        $tags = array_unique($tags);
        $tags = array_slice($tags, 0, 10);

        return array_values($tags);
    }

    /**
     * Fallback tag extraction when OpenAI is not available
     *
     * @param string $description
     * @return array
     */
    protected function fallbackTagExtraction(string $description): array
    {
        // Simple keyword extraction as fallback
        $words = preg_split('/\s+/', strtolower($description));
        
        // Common academic words to filter out
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
            'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those',
            'from', 'into', 'through', 'during', 'before', 'after', 'above',
            'below', 'between', 'among', 'within', 'without', 'against', 'toward',
            'towards', 'upon', 'about', 'over', 'under', 'across', 'behind',
            'beneath', 'beside', 'beyond', 'inside', 'outside', 'underneath'
        ];

        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        // Get unique keywords and limit to 8
        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, 8);

        return array_values($keywords);

    }

    /**
     * Set the OpenAI model to use
     *
     * @param string $model
     * @return $this
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set the base URL for OpenAI API
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Check if fallback tag extraction was used
     *
     * @return bool
     */
    public function wasFallbackUsed(): bool
    {
        return $this->usedFallback;
    }

    /**
     * Get the reason why fallback was used
     *
     * @return string|null
     */
    public function getFallbackReason(): ?string
    {
        if (!$this->usedFallback) {
            return null;
        }

        if (empty($this->apiKey)) {
            return 'No OpenAI API key configured';
        }

        return 'OpenAI API unavailable (quota exceeded, rate limited, or authentication failed)';
    }
}
