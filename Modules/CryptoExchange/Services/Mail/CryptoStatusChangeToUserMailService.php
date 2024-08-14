<?php

/**
 * @package CryptoStatusChangeToUserMailService
 * @author tehcvillage <support@techvill.org>
 * @contributor Abu Sufian Rubel <[sufian.techvill@gmail.com]>
 * @created 03-06-2023
 */

namespace Modules\CryptoExchange\Services\Mail;

use Exception;
use Illuminate\Support\Str;
use App\Services\Mail\TechVillageMail;

class CryptoStatusChangeToUserMailService extends TechVillageMail
{
    /**
     * The array of status and message whether email sent or not.
     *
     * @var array
     */
    protected $mailResponse = [];

    public function __construct()
    {
        parent::__construct();
        $this->mailResponse = [
            'status'  => true,
            'message' => __('We have sent you the crypto Exchange status. Please check your email.')
        ];
    }
    /**
     * Send forgot password code to cryptoExchange email
     * @param object $cryptoExchange
     * @return array $response
     */
    public function send($cryptoExchange, $optional = [])
    {
        try {
            $response = $this->getEmailTemplate('crypto-status-updated-to-ser');

            if (!$response['status']) {
                return $response;
            }
            
            $data = [
                "{user}" => getColumnValue($cryptoExchange->user),
                "{uuid}" => $cryptoExchange->uuid,
                "{amount}" => $optional['amount'],
                "{added/subtracted}" => $optional['status'],
                "{from/to}" => $optional['fromTo'],
                "{status}" => $cryptoExchange->status,
                "{soft_name}" => settings('name'),
            ];
            
            $message = str_replace(array_keys($data), $data, $response['template']->body);
            $subject = str_replace("{uuid}", $cryptoExchange->uuid, $response['template']->subject);

            $this->email->sendEmail($optional['email'], $subject, $message);
        } catch (Exception $e) {
            $this->mailResponse = ['status' => false, 'message' => $e->getMessage()];
        }
        return $this->mailResponse;
    }
}