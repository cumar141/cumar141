<?php

/**
 * @package LoginController
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman <[abdur.techvill@gmail.com]>
 * @created 30-11-2022
 */

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\User\UserLoginResource;
use App\Exceptions\Api\V2\LoginException;
use App\Http\Requests\UserLoginRequest;
use App\Services\AuthService;
use Carbon\Carbon;
use App\Models\{
    ActivityLog,
    UserDetail
};
use Exception;
use App\Http\Controllers\Controller;
use App\Services\Mail\DeviceNotificationMailService;
class LoginController extends Controller
{
    /**
     * User Login
     * @param UserLoginRequest $request
     * @param AuthService $service
     * @return JsonResponse
     * @throws LoginException
     */
    public function login(UserLoginRequest $request, AuthService $service)
    {
        try {
            $response = $service->login($request->email, $request->password, $request);
            $verifyDevice = (new ActivityLog())->createActivityLog($response['id'], 'User', $request->ip(), $request->header('device-id'), $request->header('os'), $request->header('device-model'), $request->header('user-agent'));
            (new UserDetail())->updateUserLoginInfo($response, Carbon::now()->toDateTimeString(), $request->getClientIp());
            if ($verifyDevice == 1) {
                $service->clearLoginsession(); //---> added this line for clear old session very time login
                return $this->successResponse(new UserLoginResource($response));
            } else {
                return response()->json([
                "response" => [
                    "status" => [
                        "status" => false,
                        "verify" => true,
                        "message" => "This user is registered on another device"
                    ],
                    "records" => []
                ]
            ]);
        }
        } catch (LoginException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }
    
    public function verifyDevice(UserLoginRequest $request, AuthService $service)
    {
        try {
            $response = $service->login($request->email, $request->password, $request);
            if ($response) {
            $verifyDevice = (new ActivityLog())->verifyDevicelogs($response['id'], 'User', $request->ip(), $request->header('device-id'), $request->header('os'), $request->header('device-model'), $request->header('user-agent'));
            $data['phone'] = $request->email;
            $data['device'] = $request->header('device-model');
            $data['location'] = $request->ip();
            if ($verifyDevice == true) {
                if ($response['email']){
                    (new DeviceNotificationMailService)->send($response, $data);
                }
                return response()->json([
                     "response"=>[
                         "status"=>[
                             "status" => True,
                             "verify" => False,
                             "message" => "Your Device is successfull verified!"
                         ],
                     "records"=>[]
                     ]
                ]);
             }else{
                 return response()->json([
                     "response"=>[
                         "status"=>[
                             "status" => False,
                             "verify" => False,
                             "message" => "No device was updated!"
                        ],
                     "records"=>[]
                     ]
                ]);
            }
            }
        } catch (LoginException $e) {
            return $this->unprocessableResponse([], $e->getMessage());
        } catch (Exception $e) {
            return $this->unprocessableResponse([], __("Failed to process the request."));
        }
    }

}
