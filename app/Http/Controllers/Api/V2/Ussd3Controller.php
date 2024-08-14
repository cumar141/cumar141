<?php
namespace App\Http\Controllers\Api\V2;
use Illuminate\Http\Request;
class Ussd3Controller extends Controller
{
    public static function ussd(Request $request)
    {
        //$request->all();
        $text=$request->input('text');
        $session_id = $request->input('sessionId');
        $phone_number = $request->input('phoneNumber');
        $service_code = $request->input('serviceCode');
        $network_code = $request->input('networkCode');
        $level = explode("*", $text);
        //if (isset($text)) {
   
        if ( $text == "" ) {
            $response="CON Welcome to Company title\n";
            $response .= "1. Airtime\n";
            $response .= "2. History \n";
            $response .= "3. Top-Up \n";
            $response .= "0. Exit";
        }
        // foreach($level as $i){
        //     if ($i == 0){
        //         header('Content-type: text/plain');
        //         echo $response;
        //     }
        // }
        if(isset($level[0]) && $level[0] == 1 && !isset($level[1]))
        {
            $response="CON Select Network: \n";
            $response .= "1. Mtn Eswatini \n";
            $response .= "2. Eswatini Mobile \n";
            $response .= "99. Back \n";
        }
        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && !isset($level[2]))
        {
            $response="CON Select Number to Recharge \n";
            $response .="1. My number\n";
            $response .="2. Enter number \n";
            $response .= "99. Back";
        }
        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 1 && !isset($level[3]))
        {
            $response="CON Enter Amounnt \n";
        }

        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 1 && isset($level[3]) && !isset($level[4]))
        {
            $response="CON Pay using MOMO \n";
            $response .="1. Yes\n";
            $response .="2. No \n";
            $response .= "0. Exit";
        }

        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 1 && isset($level[3]) && isset($level[4]) && isset($level[4]) == 1 && !isset($level[5]))
        {
            $response="CON Enter MOMO PIN \n";
        }

        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 1 && isset($level[3]) && isset($level[4]) && isset($level[4]) == 1 && isset($level[5])  && !isset($level[6]))
        {
            $response="END Account has been recharged with the sum of #$level[3]\n thanks";
        }

        #not my number case
        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 2 && !isset($level[3]))
        {
            $response="CON Enter Mobile Number \n";
        }


        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 2 && isset($level[3]) && !isset($level[4]))
        {
            $response="CON Enter Amount to recharge eg(#100)\n";
        }

        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 2 && isset($level[3]) && isset($level[4]) && !isset($level[5]))
        {
            $response="CON Pay using MOMO \n";
            $response .="1. Yes\n";
            $response .="0. No \n";
            $response .= "0. Exit";
        }

        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 2 && isset($level[3]) && isset($level[4]) && isset($level[5]) && isset($level[5]) == 1 && !isset($level[6]))
        {
            $response="CON Enter MOMO PIN \n";
        }

        if(isset($level[0]) && $level[0] == 1 && isset($level[1]) && ($level[1] == 1 || $level[1] == 2) && isset($level[2]) && $level[2] == 2 && isset($level[3]) && isset($level[4]) && isset($level[5])&& isset($level[5]) == 1 && isset($level[6]) && !isset($level[7]))
        {
            $response="END Account has been recharged with the sum of #$level[4]\n thanks";
        }

        // if(isset($level[0]) && $level[0] == 3 && isset($level[1]) && $level[1] == 1 && isset($level[2]) && $level[2] == 5 && isset($level[3]) && $level[3] == 3 && isset($level[4]) && $level[4] == 1 && isset($level[5]) && $level[5] == 1 && !isset($level[6]))
        // {
            
        //     $response="END Transaction Successfull \n";
        //     $response .="thanks for patronage \n";
           
        // }
            header('Content-type: text/plain');
            echo $response;
        //}
    }

    public static function add_menu($menu, $menu_item){

    }
    public static function base_menu($title){
        $response="CON $title \n";
    }
}