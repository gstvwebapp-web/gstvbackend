<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\Guzzle6HttpHandler;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $httpClient;
    protected $projectId;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID');
        // JSON content directly embedded as a string
        $jsonKey = '{
            "type": "service_account",
            "project_id": "gstv-app-ecbc7",
            "private_key_id": "f222de9ea64b9a1709c8dcdd62dbc560bb618b8e",
            "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDC3eA4ihOKLNW5\n+jCmplGmGwWYtX0iY4ko+rBKWmY12bwd2K14N+RoGuFkSht2umAmxs2PUVmRHVRH\n8RE6//VxfaR2quAPNw4b5rwASgvjBF4bY9NiB7DoUj78thsz7Pw3cT4VA1EF0GGE\nHk5Tt9g0tqiBBginQp5ToUHy7aIG9DjfteONPBE2xyBPS9YkJSqPtBgmQ5dqWfNv\nAfbszWbxCdlqpjbE0rXX9Cj6kt+Iv2+2nLInzwE5fRFI6VOpLCie0I9GJW0bax9Z\ng254KH1hgY6NnpRk5Q2coARTGEK7fFEAVAjPey/SMxhRoIbMk8nMTESk1Y4tCGyK\nWt0q4tR/AgMBAAECggEAGr4cYWYBnHresfE18AjvN56JvBVcIPYMAsW0n9+cG1PQ\nT0BHPjJddDQMLplE/5xASdIKr9p4MkQWJvFcbf5mjraxdV+p9CyYERgvQNLoorUc\nvSGMBu4LRzbSUhgjz5J8Ypd176hO3D9mwpzuHusiDsnqghpz4ZBw7gU5ab9W9yEb\nkcXXwyP6+Ti3cwvU1tbE4s4ioHkT9cqfu47ysRdPuuYpTp4y5T22wx8AkGlwfQHK\nqVbQmBm+deV94B26HIqKCXuIBnySzuQrE6wnnWr/bnejegUYxogKHq11Si+8p+q+\n2hPOj9EQJk6S3ZlOujzVE4Ot4clpvLTkbqH+r6ELoQKBgQDyiepKYH+Qwqqg9eRa\ndLMhCGN1GOeaIpMcXXXoZL4DHnk7d6VTQ6xe7boU8uYpmwb9DHgbw5RggBmr1jox\nKGcldZ2/+QKpL9gZ5++gIUJ2PyOKxvl95T1kX7hWMn6XkuVGHo76b7yPlWO9hdej\ncd7MmUPx7nukWBFFwjQZsaqi9wKBgQDNrp4vF5EeyjGKkhEg3jGMBFqsFhPZyksY\nPp0gsKYb3GqVgHsRSiQeiJqil2oxyefSCrSr066g9k0k9gaRY4wu0r0n75aEhNm5\nrbbHhTg0v3RyDY80kQ0koogLRQ+naUaj0icuCTn8wvdIsDopyShXpW8X3XrjOZgb\nsdLuAjFwuQKBgD5WDA2Jt7+xCMHN8R7+l3uJ+GYEPKJ0+HJ14vQT0HJkxqvuGGPK\nJOdRGl5//3/6BFXNeqFIJz6WLgLoUJSJ2dja3eyVR4XMcjJWVlRFuBtEJxJoCX++\nB4VgxLQCzl6OtrbK69yaE1CkCLqRR9cOUUEsszXfIZAfwnC3dUSVUVIJAoGATchL\naeOvrcUpFw5GC/f7JMhAwfNXbnViELkdEs+7Aa5JYGBGKjOvlRxz3ikEr+97C6Og\nz5ot4BFE+0VPFoxe5xYrEn84nMTg0acwCCQJLWl5z+fw0CBKqCAO8rQbR2Q4optG\n1dIHa98PRR3f4Q2BhCO773qCjoupSk/lRLzvlYECgYEAkuBUkSgwfuYQXib8T135\nG/J1UEHkXXm1ZPsyBsJfaGF8mkr/5VODBMTEuqGDFkbSs/Vjdjtta1u674bVB6/a\nHkPu+b3hqu6Gt7t6TMUKv3lBr4fQHpYv2T0XDgOuDKvGBKc+kVHeCyb+9JUOw7fw\nxOF6I7Rswv4OaSh/sr5AmNs=\n-----END PRIVATE KEY-----\n",
            "client_email": "firebase-adminsdk-uzwcl@gstv-app-ecbc7.iam.gserviceaccount.com",
            "client_id": "101730500405861264512",
            "auth_uri": "https://accounts.google.com/o/oauth2/auth",
            "token_uri": "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-uzwcl%40gstv-app-ecbc7.iam.gserviceaccount.com",
            "universe_domain": "googleapis.com"
        }';

        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/cloud-platform',
            json_decode($jsonKey, true)
        );

        $authHttpHandler = new Guzzle6HttpHandler(new Client());
        $credentials->fetchAuthToken($authHttpHandler);

        $this->httpClient = new Client([
            'base_uri' => 'https://fcm.googleapis.com',
            'headers' => [
                'Authorization' => 'Bearer ' . $credentials->getLastReceivedToken()['access_token'],
                'Content-Type' => 'application/json',
            ],
        ]);
    }
 
	public function sendNotification($title, $body, $topic, $notificationtype, $newFeatureImage, $notificationurl, $notificationslug)
{
    	$projectId = 'gstv-app-ecbc7'; 
        $cleanTitle = strip_tags($title);
        $cleanBody = strip_tags($body);
        // Define the message payload
        $message = [
            'message' => [
                'topic' => $topic, // Use the topic to send the notification
                'notification' => [
                    'title' => $cleanTitle,
                    'body' => '',
                    'image' => $newFeatureImage,
                ],
            'data' => [
                'type' => $notificationtype,
                'Image' => $newFeatureImage,
                'url' => $notificationurl,
                'slug' => $notificationslug,
            ],
        ],
    ];

    try {
        // Send the request to FCM
        $response = $this->httpClient->post("/v1/projects/{$projectId}/messages:send", [
            'json' => $message,
        ]);

        // Get the status code and response body
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody()->getContents(), true);

        if ($statusCode != 200) {
            Log::error("Error sending FCM notification: ", $responseBody);
        }

        return $responseBody;
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $responseBody = $e->getResponse()->getBody()->getContents();
        $errorResponse = json_decode($responseBody, true);

        // Handle specific error cases
        if ($errorResponse && isset($errorResponse['error']['status']) && $errorResponse['error']['status'] === 'NOT_FOUND') {
            foreach ($errorResponse['error']['details'] as $detail) {
                if ($detail['errorCode'] === 'UNREGISTERED') {
                    Log::warning("FCM Error: Device token is unregistered. Removing from database: " . $tokens);
                    // Handle unregistered token scenario, e.g., remove from database
                }
            }
        } else {
            Log::error("Error sending FCM notification: " . $responseBody);
        }

        return $errorResponse;
    }
}

   
    public function sendNotification1($title, $body, $token,$notificationtype, $newFeatureImage, $notificationurl,$notificationslug)
    {
        $message = [
                'message' => [
                    'token' => $token, // Use the topic to send the notification
                    'notification' => [
                        'title' => strip_tags($title),
                    	'body'  => strip_tags($body),
                        'image' => $newFeatureImage,
                    ],
                'data' => [
                    'type' => $notificationtype,
                    'Image' => $newFeatureImage,
                    'url' => $notificationurl,
                    'slug' => $notificationslug,
                ],
            ],
        ];

        $response = $this->httpClient->post("/v1/projects/{$this->projectId}/messages:send", [
            'json' => $message,
        ]);

        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);

        if ($statusCode != 200) {
            Log::error("Error sending FCM notification: ", $responseBody);
        }

        return $responseBody;
    }
}
