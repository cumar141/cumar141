<?php

/**
 * @package DeviceNotificationMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 29-05-2023
 */

namespace App\Services\Mail;

use Exception;
use App\Services\Mail\TechVillageMail;
use App\Services\LocationService;
use App\Http\Controllers\Users\EmailController;
use Carbon\Carbon;
class SecurityAlertNotificationMailService extends TechVillageMail
{
    /**
     * The array of status and message whether email sent or not.
     *
     * @var array
     */
    protected $mailResponse = [];
    protected $email;

    public function __construct()
    {
        $this->email                = new EmailController();
        parent::__construct();
        $this->mailResponse = [
            'status'  => true,
            'message' => __('Please check your email for a verification link. Click on the link to verify your email address.')
        ];
    }
    /**
     * Send verification link to user to verify email
     * @param object $user
     * @param array $optional
     * @return array $response
     */
    public function send($user, $optional = [])
    {
        try {
             $response = $this->getEmailTemplate('security-alert');
                if (!$response['status']) {
                    return $response;
                }
                
                $Location = (new LocationService)->getLocation($optional['location']);
                
                $data = [
                    '{user}'  => getColumnValue($user),
                    '{email}' => $user->formattedPhone,
                    '{device}' => $optional['device'],
                    '{location}' => $optional['location'] . ' ' . $Location['continentName'] . ' ' . $Location['countryName'] . ' ' . $Location['capital'],
                    '{date}' => Carbon::now()->toDateTimeString(),
                    '{soft_name}'        => settings('name'),
                ];
                
                $message = str_replace(array_keys($data), $data, $response['template']->body);
    
                $this->email->sendEmail($user->email, $response['template']->subject . ' ' .'for' . ' ' . $user->formattedPhone, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }

}
