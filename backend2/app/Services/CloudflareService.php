<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CloudflareService
{
    /**
     * Clear Cloudflare cache
     *
     * @return bool
     */
   
  
  	public function clearCache(array $urls)
    {
        $apiKey = '95d209c71811aecc85b2ec250c9f77ef19a98';
        $email = 'gstv.web@gmail.com';
        $zoneId = '62af169de5e4101325b78201e20f2eb3';

        $response = Http::withHeaders([
            'X-Auth-Email' => $email,
            'X-Auth-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", [
            'files' => $urls,
        ]);

        if ($response->failed()) {
            // Log the response status and body for debugging
            \Log::error('Cloudflare API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'rate_limit_limit' => $response->header('X-RateLimit-Limit'),
                'rate_limit_remaining' => $response->header('X-RateLimit-Remaining'),
                'rate_limit_reset' => $response->header('X-RateLimit-Reset'),
            ]);
        }

        return $response->successful();
    }

    public function clearCacheall()
    {
        $apiKey = '95d209c71811aecc85b2ec250c9f77ef19a98';
        $email = 'gstv.web@gmail.com';
        $zoneId = '62af169de5e4101325b78201e20f2eb3';

        $response = Http::withHeaders([
            'X-Auth-Email' => $email,
            'X-Auth-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", [
            'purge_everything' => true, 
        ]);

        if ($response->failed()) {
            // Log the response status and body for debugging
            \Log::error('Cloudflare API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'rate_limit_limit' => $response->header('X-RateLimit-Limit'),
                'rate_limit_remaining' => $response->header('X-RateLimit-Remaining'),
                'rate_limit_reset' => $response->header('X-RateLimit-Reset'),
            ]);
        }

        return $response->successful();
    }
    public function clearCachenewscategory(array $urlsToPurge)
    {
       
        $apiKey = '95d209c71811aecc85b2ec250c9f77ef19a98';
        $email = 'gstv.web@gmail.com';
        $zoneId = '62af169de5e4101325b78201e20f2eb3';

        $response = Http::withHeaders([
            'X-Auth-Email' => $email,
            'X-Auth-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache", [
            'files' => $urlsToPurge
        ]);

        if ($response->failed()) {
            \Log::error('Cloudflare API purge failed', [
                'urls' => $urlsToPurge,
                'status' => $response->status(),
                'body' => $response->body(),
                'rate_limit_limit' => $response->header('X-RateLimit-Limit'),
                'rate_limit_remaining' => $response->header('X-RateLimit-Remaining'),
                'rate_limit_reset' => $response->header('X-RateLimit-Reset'),
            ]);
            return false;
        }

        \Log::info('Cloudflare URLs purged successfully', ['urls' => $urlsToPurge]);

        return true;
    }
}
