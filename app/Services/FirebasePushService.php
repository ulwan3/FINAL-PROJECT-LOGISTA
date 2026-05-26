<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    private string $serviceAccountPath;

    public function __construct()
    {
        $this->serviceAccountPath = storage_path(
            'app/firebase/firebase-service-account.json'
        );
    }

    public function sendToAllTokens(string $title, string $body, array $data = []): void
    {
        $tokens = DB::table('fcm_tokens')
            ->select('token')
            ->whereNotNull('token')
            ->pluck('token')
            ->toArray();

        if (count($tokens) === 0) {
            Log::info('FCM token kosong. Push notification tidak dikirim.');
            return;
        }

        foreach ($tokens as $token) {
            $this->sendToToken($token, $title, $body, $data);
        }
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        $serviceAccount = $this->getServiceAccount();

        $projectId = $serviceAccount['project_id'] ?? null;

        if (!$projectId) {
            Log::error('Firebase project_id tidak ditemukan di service account.');
            return;
        }

        $accessToken = $this->getAccessToken();

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $this->normalizeData($data),
                'android' => [
                    'priority' => 'HIGH',
                ],
            ],
        ];

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post($url, $payload);

        if ($response->successful()) {
            Log::info('Push notification berhasil dikirim.', [
                'token' => substr($token, 0, 20) . '...',
                'response' => $response->json(),
            ]);

            return;
        }

        $error = $response->json();

        Log::error('Gagal kirim push notification.', [
            'status' => $response->status(),
            'token' => substr($token, 0, 20) . '...',
            'error' => $error,
        ]);

        $errorStatus = $error['error']['status'] ?? null;

        if (in_array($errorStatus, ['NOT_FOUND', 'INVALID_ARGUMENT', 'UNREGISTERED'])) {
            DB::table('fcm_tokens')
                ->where('token', $token)
                ->delete();

            Log::warning('FCM token invalid dihapus dari database.', [
                'token' => substr($token, 0, 20) . '...',
            ]);
        }
    }

    private function getServiceAccount(): array
    {
        if (!file_exists($this->serviceAccountPath)) {
            throw new \Exception(
                'File Firebase service account tidak ditemukan: ' . $this->serviceAccountPath
            );
        }

        $json = file_get_contents($this->serviceAccountPath);
        $data = json_decode($json, true);

        if (!$data) {
            throw new \Exception('File Firebase service account tidak valid.');
        }

        return $data;
    }

    private function getAccessToken(): string
    {
        return Cache::remember('firebase_access_token', 50 * 60, function () {
            $serviceAccount = $this->getServiceAccount();

            $clientEmail = $serviceAccount['client_email'] ?? null;
            $privateKey = $serviceAccount['private_key'] ?? null;
            $tokenUri = $serviceAccount['token_uri'] ?? 'https://oauth2.googleapis.com/token';

            if (!$clientEmail || !$privateKey) {
                throw new \Exception('client_email atau private_key Firebase tidak ditemukan.');
            }

            $now = time();

            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
            ];

            $claimSet = [
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ];

            $jwtHeader = $this->base64UrlEncode(json_encode($header));
            $jwtClaim = $this->base64UrlEncode(json_encode($claimSet));

            $signatureInput = $jwtHeader . '.' . $jwtClaim;

            openssl_sign(
                $signatureInput,
                $signature,
                $privateKey,
                'sha256WithRSAEncryption'
            );

            $jwtSignature = $this->base64UrlEncode($signature);

            $jwt = $signatureInput . '.' . $jwtSignature;

            $response = Http::asForm()->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('Gagal mengambil Firebase access token.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                throw new \Exception('Gagal mengambil Firebase access token.');
            }

            return $response->json('access_token');
        });
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(
            strtr(
                base64_encode($data),
                '+/',
                '-_'
            ),
            '='
        );
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[$key] = (string) $value;
        }

        return $normalized;
    }
}