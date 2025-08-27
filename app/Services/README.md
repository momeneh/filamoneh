# OpenAiService

This service provides AI-powered tag extraction functionality for research papers using OpenAI's API.

## Features

- **AI Tag Extraction**: Automatically extracts relevant academic tags from paper descriptions
- **Fallback Support**: Includes a fallback keyword extraction when OpenAI is unavailable
- **Fallback Detection**: Automatically detects when fallback is used and provides reasons
- **Caching**: Caches API responses to reduce API calls and costs
- **Error Handling**: Graceful error handling with comprehensive logging
- **Configurable**: Easy to configure API settings

## Configuration

Add the following environment variables to your `.env` file:

```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-3.5-turbo
```

## Usage

### Basic Usage

```php
use App\Services\OpenAiService;

$openAiService = new OpenAiService();
$tags = $openAiService->retrieveTagsForDescription($description);
```

### In Filament Forms

The service is already integrated into the PaperResource form. Users can click the "Extract Tags from Description" button to automatically extract tags from the paper description.

### Custom Configuration

```php
$openAiService = new OpenAiService();
$openAiService
    ->setModel('gpt-4')
    ->setBaseUrl('https://your-custom-endpoint.com/v1');
```

### Fallback Detection

```php
$openAiService = new OpenAiService();
$tags = $openAiService->retrieveTagsForDescription($description);

// Check if fallback was used
if ($openAiService->wasFallbackUsed()) {
    $reason = $openAiService->getFallbackReason();
    // Handle fallback case
}
```

## How It Works

1. **API Call**: Sends the description to OpenAI's API with a specialized prompt
2. **Response Processing**: Parses the AI response to extract clean tags
3. **Fallback**: If OpenAI is unavailable, uses keyword extraction as fallback
4. **Caching**: Caches results to avoid repeated API calls for the same content

## Fallback Behavior

When OpenAI API is not available or fails, the service automatically falls back to a simple keyword extraction algorithm that:
- Removes common stop words
- Extracts meaningful keywords (3+ characters)
- Limits results to 8 tags maximum

## Error Handling

The service logs all errors and API failures. Check your Laravel logs for detailed error information.

## Cost Optimization

- Responses are cached for 1 hour to reduce API calls
- Uses `gpt-3.5-turbo` by default (cheaper than GPT-4)
- Limits token usage to 150 tokens per request

## Security

- API keys are stored in environment variables
- No sensitive data is logged
- Input validation and sanitization included
