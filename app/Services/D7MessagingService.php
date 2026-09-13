<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class D7MessagingService
{
    protected string $token;
    protected string $smsOriginator;
    protected string $whatsappOriginator;

    protected string $baseUrl = 'https://api.d7networks.com/messages/v1/send';

    public function __construct()
    {
        $this->token = config('services.d7.token');
        $this->smsOriginator = config('services.d7.sms_originator');
        $this->whatsappOriginator = config('services.d7.whatsapp_originator');
    }

    public function sendMessage(string $channel, array|string $recipients, string $message): array
    {
        $channel = strtolower($channel);
        $recipientArray = is_array($recipients) ? $recipients : [$recipients];

        try {
            $payload = match ($channel) {
                'sms' => $this->buildSmsPayload($recipientArray, $message),
                'whatsapp' => $this->buildWhatsAppPayload($recipientArray, $message),
                default => throw new \InvalidArgumentException("Unsupported D7 channel: {$channel}"),
            };

            $response = Http::withToken($this->token)
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post($this->baseUrl, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'response' => $response->json(),
                ];
            }

            Log::error("D7 {$channel} failure", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => "D7 {$channel} failure",
                'error' => $response->json() ?: $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("D7 {$channel} exception", [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "D7 {$channel} exception: {$e->getMessage()}",
            ];
        }
    }

    private function buildSmsPayload(array $recipients, string $message): array
    {
        return [
            'messages' => [
                [
                    'channel' => 'sms',
                    'recipients' => $recipients,
                    'content' => $message,
                    'msg_type' => 'text',
                    'data_coding' => 'text',
                ],
            ],
            'message_globals' => [
                'originator' => $this->smsOriginator,
            ],
        ];
    }

    private function buildWhatsAppPayload(array $recipients, string $message): array
    {
        $formattedRecipients = array_map(
            fn($number) => [
                'recipient' => $number,
                'recipient_type' => 'individual',
            ],
            $recipients
        );

        return [
            'messages' => [
                [
                    'channel' => 'whatsapp',
                    'recipients' => $formattedRecipients,
                    'content' => $message,
                    'msg_type' => 'text',
                    'data_coding' => 'text',
                ],
            ],
            'message_globals' => [
                'originator' => $this->whatsappOriginator,
            ],
        ];
    }
}
