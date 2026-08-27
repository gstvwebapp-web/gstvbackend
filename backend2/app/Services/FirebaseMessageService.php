<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\Guzzle6HttpHandler;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseMessageService
{
    protected $projectId;
    protected $credentials;
    protected $authHttpHandler;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID', 'gstv-app-ecbc7');

        // Load JSON from storage (safer than inline)
        $jsonKeyPath = storage_path('app/firebase-service-account.json');
        $jsonKey = json_decode(file_get_contents($jsonKeyPath), true);

        // Correct FCM scope
        $this->credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $jsonKey
        );

        $this->authHttpHandler = new Guzzle6HttpHandler(new Client());
    }

    private function getHttpClient()
    {
        // Always refresh token before request
        $this->credentials->fetchAuthToken($this->authHttpHandler);
        $token = $this->credentials->getLastReceivedToken()['access_token'];

        return new Client([
            'base_uri' => 'https://fcm.googleapis.com',
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    public function sendNotification($title, $body, $topic, $notificationtype, $newFeatureImage, $notificationurl, $notificationslug)
    {
        $message = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => strip_tags($title),
                    'body'  => strip_tags($body), // ✅ FIXED (was empty string)
                    'image' => $newFeatureImage,
                ],
                'data' => [
                    'type' => $notificationtype,
                    'Image' => $newFeatureImage,
                    'url'   => $notificationurl,
                    'slug'  => $notificationslug,
                ],
            ],
        ];

        try {
            $response = $this->getHttpClient()->post("/v1/projects/{$this->projectId}/messages:send", [
                'json' => $message,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error sending FCM topic notification: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function sendNotification1($title, $body, $token, $notificationtype, $newFeatureImage, $notificationurl, $notificationslug)
    {
        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => strip_tags($title),
                    'body'  => strip_tags($body),
                    'image' => $newFeatureImage,
                ],
                'data' => [
                    'type' => $notificationtype,
                    'Image' => $newFeatureImage,
                    'url'   => $notificationurl,
                    'slug'  => $notificationslug,
                ],
            ],
        ];

        try {
            $response = $this->getHttpClient()->post("/v1/projects/{$this->projectId}/messages:send", [
                'json' => $message,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error sending FCM token notification: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
