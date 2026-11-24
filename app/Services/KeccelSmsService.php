<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class KeccelSmsService
{
    /**
     * Envoie un SMS via l'API Keccel.
     *
     * @param   string $to          Le numéro de téléphone du destinataire.
     * @param   string $message     Le message personnalisé.
     * @param   string $from        L'expéditeur du message (par défaut 'GUEST').
     * @return  array               La réponse de l'API Keccel.
     */
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
    // public function sendSMS(string $mobile, string $otp, string $message)
    // {
    //     // Données pour l'API Keccel
    //     $data = [
    //         "campaignId" => 1,
    //         "routeId" => 1,
    //         "sender" => "boongo", // Sender ID "env('KECCEL_API_SENDER')"
    //         "mode" => "text",
    //         "message" => $message,
    //         "contacts" => [
    //             ["mobile" => $mobile, "parameters" => ["otp" => $otp]],
    //         ],
    //         "notifyUrl" => "https://example.com", // URL de notification
    //         "dlt_entity_id" => "1234567890", // ID d'entité DLT
    //         "dlt_template_id" => "1234567890", // ID du modèle DLT
    //     ];

    //     // Envoi de la requête via HTTP client de Laravel
    //     $response = Http::withHeaders([
    //         'Authorization' => "Bearer A64YP3KD9M59MF9", // Utiliser une clé API via .env "env('KECCEL_API_TOKEN')"
    //         'Content-Type' => 'application/json',
    //     ])->post('https://sms.keccel.com/api/v2/sms', $data);

    //     return $response->json();
    // }
}
