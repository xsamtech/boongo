<?php

namespace App\Http\Controllers\API;

use stdClass;
use App\Mail\OTPCode;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\PasswordReset as ResourcesPasswordReset;
use App\Http\Resources\User as ResourcesUser;
use App\Services\D7MessagingService;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class PasswordResetController extends BaseController
{
    protected D7MessagingService $d7MessagingService;

    public function __construct(D7MessagingService $d7MessagingService)
    {
        $this->d7MessagingService = $d7MessagingService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $password_resets = PasswordReset::orderByDesc('updated_at')->get();

        return $this->handleResponse(ResourcesPasswordReset::collection($password_resets), __('notifications.find_all_password_resets_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    #[BodyParameter('email', description: 'Colonne password_resets.email : VARCHAR(255); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('phone', description: 'Colonne password_resets.phone : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('former_password', description: 'Colonne password_resets.former_password : TEXT; NOT NULL.', required: false, type: 'string')]
    public function store(Request $request)
    {
        $random_int_stringified = (string) random_int(1000000, 9999999);
        // Get inputs
        $inputs = [
            'email' => $request->email,
            'phone' => $request->phone,
            'token' => $random_int_stringified,
            'former_password' => $request->former_password
        ];

        // Validate required fields
        if (trim($inputs['email']) == null AND trim($inputs['phone']) == null) {
            return $this->handleError(__('validation.email_or_phone.required'), 400);
        }

        if ($inputs['email'] != null) {
            $existing_password_reset = PasswordReset::where('email', $inputs['email'])->first();

            if ($existing_password_reset != null) {
                $existing_password_reset->delete();
            }

            $password_reset = PasswordReset::create($inputs);

            return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.create_password_reset_success'));
        }

        if ($inputs['phone'] != null) {
            $existing_password_reset = PasswordReset::where('phone', $inputs['phone'])->first();

            if ($existing_password_reset != null) {
                $existing_password_reset->delete();
            }

            $password_reset = PasswordReset::create($inputs);

            return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.create_password_reset_success'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $password_reset = PasswordReset::find($id);

        if (is_null($password_reset)) {
            return $this->handleError(__('notifications.find_password_reset_404'));
        }

        return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.find_password_reset_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PasswordReset  $password_reset
     * @return \Illuminate\Http\Response
     */
    #[BodyParameter('id', description: 'Colonne password_resets.id : BIGINT; NOT NULL. Identifiant du corps utilisé par les contrôles existants, distinct du paramètre de route.', required: false, type: 'integer')]
    #[BodyParameter('email', description: 'Colonne password_resets.email : VARCHAR(255); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('phone', description: 'Colonne password_resets.phone : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('token', description: 'Colonne password_resets.token : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('former_password', description: 'Colonne password_resets.former_password : TEXT; NOT NULL.', required: false, type: 'string')]
    public function update(Request $request, PasswordReset $password_reset)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'email' => $request->email,
            'phone' => $request->phone,
            'token' => $request->token,
            'former_password' => $request->former_password
        ];
        if ($inputs['email'] != null) {
            // Select all password resets and a specific password reset to check constraint
            $password_resets = PasswordReset::all();
            $current_password_reset = PasswordReset::find($inputs['id']);

            // Check if email already exists
            foreach ($password_resets as $another_password_reset):
                if (((!empty($current_password_reset->email)) && ($current_password_reset->email != $inputs['email'])) && ($another_password_reset->email == $inputs['email'])) {
                    return $this->handleError($inputs['email'], __('validation.custom.email.exists'), 400);
                }
            endforeach;

            $password_reset->update([
                'email' => $inputs['email'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['phone'] != null) {
            // Select all password resets and a specific password reset to check constraint
            $password_resets = PasswordReset::all();
            $current_password_reset = PasswordReset::find($inputs['id']);

            // Check if phone already exists
            foreach ($password_resets as $another_password_reset):
                if (((!empty($current_password_reset->phone)) && ($current_password_reset->phone != $inputs['phone'])) && ($another_password_reset->phone == $inputs['phone'])) {
                    return $this->handleError($inputs['phone'], __('validation.custom.phone.exists'), 400);
                }
            endforeach;

            $password_reset->update([
                'phone' => $inputs['phone'],
                'updated_at' => now(),
            ]);
        }

        foreach (['token', 'former_password'] as $field) {
            if ($inputs[$field] != null) {
                $password_reset->update([
                    $field => $inputs[$field],
                    'updated_at' => now()
                ]);
            }
        }

        return $this->handleResponse(new ResourcesPasswordReset($password_reset), __('notifications.update_password_reset_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PasswordReset  $password_reset
     * @return \Illuminate\Http\Response
     */
    public function destroy(PasswordReset $password_reset)
    {
        $password_reset->delete();

        $password_resets = PasswordReset::all();

        return $this->handleResponse(ResourcesPasswordReset::collection($password_resets), __('notifications.delete_password_reset_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a password reset by e-mail or phone number
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchByEmailOrPhone($data)
    {
        $random_int_stringified = (string) random_int(1000000, 9999999);

        if (is_numeric($data)) {
            $password_reset = PasswordReset::where('phone', $data)->first();
            $user = User::where('phone', $data)->first();

            if (is_null($user)) {
                return $this->handleError(__('notifications.find_user_404'));
            }

            if (is_null($password_reset)) {
                return $this->handleError(__('notifications.find_password_reset_404'));
            }

            $password_reset->update([
                'token' => $random_int_stringified,
                'updated_at' => now()
            ]);

            $object = new stdClass();
            $object->user = new ResourcesUser($user);
            $object->password_reset = new ResourcesPasswordReset($password_reset);

            return $this->handleResponse($object, __('notifications.find_password_reset_success'));

        } else {
            $password_reset = PasswordReset::where('email', $data)->first();
            $user = User::where('email', $data)->first();

            if (is_null($user)) {
                return $this->handleError(__('notifications.find_user_404'));
            }

            if (is_null($password_reset)) {
                return $this->handleError(__('notifications.find_password_reset_404'));
            }

            $password_reset->update([
                'token' => $random_int_stringified,
                'updated_at' => now()
            ]);

            $object = new stdClass();
            $object->user = new ResourcesUser($user);
            $object->password_reset = new ResourcesPasswordReset($password_reset);

            return $this->handleResponse($object, __('notifications.find_password_reset_success'));
        }
    }

    /**
     * Search a password reset by e-mail
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchByEmail($data)
    {
        $password_reset = PasswordReset::where('email', $data)->first();
        $user = User::where('email', $data)->first();

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        if (is_null($password_reset)) {
            return $this->handleError(__('notifications.find_password_reset_404'));
        }

        if ($password_reset->email != null) {
            $random_int_stringified = (string) random_int(1000000, 9999999);

            $password_reset->update([
                'token' => $random_int_stringified,
                'updated_at' => now()
            ]);

            Mail::to($password_reset->email)->send(new OTPCode($password_reset->token));
        }

        $object = new stdClass();
        $object->user = new ResourcesUser($user);
        $object->password_reset = new ResourcesPasswordReset($password_reset);

        return $this->handleResponse($object, __('notifications.find_password_reset_success'));
    }

    /**
     * Search a password reset by phone
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchByPhone($data)
    {
        $password_reset = PasswordReset::where('phone', $data)->first();
        $user = User::where('phone', $data)->first();
        // $basic  = new \Vonage\Client\Credentials\Basic(config('vonage.api_key'), config('vonage.api_secret'));
        // $client = new \Vonage\Client($basic);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        if (is_null($password_reset)) {
            return $this->handleError(__('notifications.find_password_reset_404'));
        }

        if ($password_reset->phone != null) {
            $random_int_stringified = (string) random_int(1000000, 9999999);

            $password_reset->update([
                'token' => $random_int_stringified,
                'updated_at' => now()
            ]);

            $channel = $this->getChannelFromPhone($password_reset->phone);
            $sendOTP = $this->d7MessagingService->sendMessage($channel, $password_reset->phone, (string) $password_reset->token);

            if (!$sendOTP['success']) {
                return $this->handleError('Oups!', $sendOTP['message'], 500);
            }

            // try {
            //     $client->sms()->send(new \Vonage\SMS\Message\SMS($password_reset->phone, 'Boongo', (string) $password_reset->token));

            // } catch (\Throwable $th) {
            //     $response_error = json_decode($th->getMessage(), false);

            //     return $this->handleError($response_error, __('notifications.create_user_SMS_failed'), 500);
            // }
        }

        $object = new stdClass();
        $object->user = new ResourcesUser($user);
        $object->password_reset = new ResourcesPasswordReset($password_reset);

        return $this->handleResponse($object, __('notifications.find_password_reset_success'));
    }

    /**
     * Check the password reset token validity
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $entity
     * @return \Illuminate\Http\Response
     */
    #[BodyParameter('email', description: 'Colonne password_resets.email : VARCHAR(255); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('token', description: 'Colonne password_resets.token : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    #[BodyParameter('phone', description: 'Colonne password_resets.phone : VARCHAR(45); NULL autorisé.', required: false, type: 'string')]
    public function checkToken(Request $request, $entity)
    {
        $random_int_stringified = (string) random_int(1000000, 9999999);
        $object = new stdClass();

        if ($entity == 'email') {
            $inputs = [
                'email' => $request->email,
                'token' => $request->token
            ];

            if (trim($inputs['email']) == null) {
                return $this->handleError(__('validation.custom.email.incorrect'));
            }

            if (trim($inputs['token']) == null) {
                return $this->handleError($inputs['token'], __('validation.custom.email.incorrect'), 400);
            }

            $user = User::where('email', $inputs['email'])->first();
            $password_reset = PasswordReset::where('email', $inputs['email'])->first();

            if (is_null($user)) {
                return $this->handleError(__('notifications.find_user_404'));
            }

            if (is_null($password_reset)) {
                return $this->handleError(__('notifications.find_password_reset_404'));
            }

            if ($password_reset->token != $inputs['token']) {
                return $this->handleError($inputs['token'], __('notifications.bad_token'), 400);
            }

            $user->update([
                'email_verified_at' => now(),
                'updated_at' => now(),
            ]);

            $password_reset->update([
                'token' => $random_int_stringified,
                'updated_at' => now()
            ]);

            $object->user = new ResourcesUser($user);
            $object->password_reset = new ResourcesPasswordReset($password_reset);

            return $this->handleResponse($object, __('notifications.verified_token'));
        }

        if ($entity == 'phone') {
            $inputs = [
                'phone' => $request->phone,
                'token' => $request->token
            ];

            if (trim($inputs['phone']) == null) {
                return $this->handleError(__('validation.custom.phone.incorrect'));
            }

            if (trim($inputs['token']) == null) {
                return $this->handleError($inputs['token'], __('validation.required'), 400);
            }

            $user = User::where('phone', $inputs['phone'])->first();
            $password_reset = PasswordReset::where('phone', $inputs['phone'])->first();

            if (is_null($user)) {
                return $this->handleError(__('notifications.find_user_404'));
            }

            if (is_null($password_reset)) {
                return $this->handleError(__('notifications.find_password_reset_404'));
            }

            if ($password_reset->token != $inputs['token']) {
                return $this->handleError($inputs['token'], __('notifications.bad_token'), 400);
            }

            $user->update([
                'phone_verified_at' => now(),
                'updated_at' => now(),
            ]);

            $password_reset->update([
                'token' => $random_int_stringified,
                'updated_at' => now()
            ]);

            $object->user = new ResourcesUser($user);
            $object->password_reset = new ResourcesPasswordReset($password_reset);

            return $this->handleResponse($object, __('notifications.verified_token'));
        }
    }

    /**
     * Méthode d'aide pour obtenir le canal selon le numéro.
     * Accessible partout dans ce contrôleur avec $this->getChannelFromPhone(...)
     */
    private function getChannelFromPhone(string $phone): string
    {
        if (str_starts_with($phone, '+243') || str_starts_with($phone, '243')) {
            return 'sms';
        }

        return 'whatsapp';
    }
}
