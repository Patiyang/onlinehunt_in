<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;

class FirebaseNotificationService
{
    protected string $projectId;
    protected string $credentialsPath;

    public function __construct()
    {
        $this->projectId = env('firebase.project_id');
        $this->credentialsPath = env('firebase.credentials');
    }

    /**
     * Send a notification to an FCM topic.
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = []
    ): array {
        $accessToken = $this->getAccessToken();

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            'message' => [
                'topic' => $topic,

                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],

                'data' => array_map(
                    'strval',
                    $data
                ),
            ]
        ];

        $client = service('curlrequest');

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $bodyResponse = json_decode($response->getBody(), true);

            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status' => $statusCode,
                'response' => $bodyResponse
            ];

        } catch (\Throwable $e) {

            log_message(
                'error',
                'Firebase notification error: ' . $e->getMessage()
            );

            return [
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate a short-lived OAuth 2.0 access token.
     */
    protected function getAccessToken(): string
    {
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $this->credentialsPath
        );

        $token = $credentials->fetchAuthToken();

        if (empty($token['access_token'])) {
            throw new \RuntimeException(
                'Unable to obtain Firebase access token.'
            );
        }

        return $token['access_token'];
    }
}