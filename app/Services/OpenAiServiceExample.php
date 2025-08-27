<?php

namespace App\Services;

/**
 * Example usage of OpenAiService
 * 
 * This file demonstrates how to use the OpenAiService in your Laravel application.
 * You can delete this file after understanding how to use the service.
 */

class OpenAiServiceExample
{
    /**
     * Example 1: Basic usage in a controller
     */
    public function exampleInController()
    {
        $openAiService = new OpenAiService();
        
        $description = "This research paper discusses machine learning algorithms and artificial intelligence applications in modern computing systems.";
        
        $tags = $openAiService->retrieveTagsForDescription($description);
        
        // $tags will contain an array of extracted tags
        // If OpenAI is available: ['machine learning', 'artificial intelligence', 'computing systems', ...]
        // If OpenAI is not available: ['machine', 'learning', 'artificial', 'intelligence', 'computing', ...]
        
        return $tags;
    }
    
    /**
     * Example 2: Using in a command or job
     */
    public function exampleInCommand()
    {
        $openAiService = new OpenAiService();
        
        // Customize the service
        $openAiService
            ->setModel('gpt-4')
            ->setBaseUrl('https://your-custom-endpoint.com/v1');
        
        $description = "Quantum computing applications in cryptography and security systems.";
        $tags = $openAiService->retrieveTagsForDescription($description);
        
        return $tags;
    }
    
    /**
     * Example 3: Batch processing multiple papers
     */
    public function exampleBatchProcessing()
    {
        $openAiService = new OpenAiService();
        
        $papers = [
            'Paper 1' => 'Machine learning algorithms for data analysis',
            'Paper 2' => 'Blockchain technology in financial services',
            'Paper 3' => 'Internet of Things and smart cities'
        ];
        
        $results = [];
        
        foreach ($papers as $title => $description) {
            $tags = $openAiService->retrieveTagsForDescription($description);
            $results[$title] = $tags;
        }
        
        return $results;
    }
    
    /**
     * Example 4: Error handling
     */
    public function exampleWithErrorHandling()
    {
        try {
            $openAiService = new OpenAiService();
            $description = "Some paper description here";
            
            $tags = $openAiService->retrieveTagsForDescription($description);
            
            if (empty($tags)) {
                // Handle case where no tags were extracted
                \Log::warning('No tags extracted from description');
            }
            
            return $tags;
            
        } catch (\Exception $e) {
            \Log::error('Error extracting tags', [
                'message' => $e->getMessage(),
                'description' => $description ?? 'N/A'
            ]);
            
            // Return empty array or default tags
            return [];
        }
    }
}

/*
 * Environment Configuration (.env file):
 * 
 * Add these lines to your .env file:
 * 
 * OPENAI_API_KEY=your_actual_openai_api_key_here
 * OPENAI_BASE_URL=https://api.openai.com/v1
 * OPENAI_MODEL=gpt-3.5-turbo
 * 
 * Note: If you don't set OPENAI_API_KEY, the service will automatically
 * fall back to keyword extraction without making API calls.
 */
