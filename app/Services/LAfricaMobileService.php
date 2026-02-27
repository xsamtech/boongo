<?php

namespace App\Services;

// use Exception;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class LAfricaMobileService
{
    public function sendMessage($to, $messageText)
    {
        // $accountid = 'REBORN_SARLU_01'; // config('services.lafricamobile.access_key');
        // $password = 'XRonxsjJHN9J0yX'; // config('services.lafricamobile.access_password');
        $curl = curl_init();
        $to = ltrim($to, '+');

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://lamsms.lafricamobile.com/api',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                                        "accountid": "REBORN_SARLU_01",
                                        "password": "XRonxsjJHN9J0yX",
                                        "sender": "LAM TEST",
                                        "ret_id": "Push_1",
                                        "ret_url": "https://mon-site.com/reception",
                                        "text": "Votre code de vérification : ' . $messageText . '. Boongo, le Guide du savoir !",
                                        "to": ' . $to . '
                                    }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return [
            'success' => true,
            'message' => __('notifications.sms_sent_successfully'),
            'data'    => $response,
        ];

        // try {

        // } catch (Exception $apiException) {
        //     return [
        //         'success' => false,
        //         'message' => __('notifications.create_user_SMS_failed'),
        //         'error'   => $apiException->getMessage(),
        //     ];
        // }
    }
}
