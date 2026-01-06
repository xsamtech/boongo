<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class InfobipService
{
    protected $apiUrl = 'https://vy6rne.api.infobip.com/whatsapp/1/message/template';
    protected $apiKey = 'd7dc849ac3d0ebd6a297aaa3fcbcaf6a-26287075-fb02-4856-80be-51ff3560f380'; // Mets ta clé API ici (ou utilise un .env)
    protected $from = '447860088970';  // Ton numéro WhatsApp ou un numéro autorisé

    public function sendMessage($to, $templateName, $placeholder, $language = 'en')
    {
        // Création du client Guzzle
        $client = new Client();

        // Préparer les données avec un seul placeholder
        $data = [
            'messages' => [
                [
                    'from' => $this->from,
                    'to' => $to,
                    'messageId' => (string) Str::uuid(),  // Génère un ID unique pour chaque message
                    'content' => [
                        'templateName' => $templateName,
                        'templateData' => [
                            'body' => [
                                'placeholders' => [$placeholder]  // Un seul placeholder
                            ]
                        ],
                        'language' => $language,
                    ]
                ]
            ]
        ];

        try {
            // Envoi de la requête POST via Guzzle
            $response = $client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'App ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $data,
            ]);

            // Vérification du statut de la réponse
            if ($response->getStatusCode() == 200) {
                return $response->getBody()->getContents();  // Retourner la réponse si tout s'est bien passé
            } else {
                throw new Exception('Unexpected HTTP status: ' . $response->getStatusCode());
            }
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
