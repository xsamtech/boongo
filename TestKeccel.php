<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class KeccelSmsService
{
    public function sendSMS(string $to, string $message, string $from = 'GUEST')
    {
        $response = Http::asJson()->post('https://api.keccel.com/sms/v2/message.asp', [
            'token' => 'A64YP3KD9M59MF9',
            'to' => $to,
            'from' => $from,
            'message' => $message,
        ]);

        return $response->json();
    }
}