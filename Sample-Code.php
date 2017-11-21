<?php namespace App\Http\Controllers\Frontend;

/*use App\Http\Requests;
use Request;*/

use Request;

// use App\Http\Requests;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Request;
// use Illuminate\Http\Request;

use Response;
//use App\Http\Controllers\Controller;
use App\Helper\Helpers;
use Input; /* For input */
use Validator;
use Session;
use DB;
use Mail;
use Hash;

/* Model List */
use App\Model\User;
use App\Model\UserApiToken;
use App\Model\SubscriptionPackage;
use App\Model\Client;
use App\Model\UserSubscription;



/* Libraries */
use App\libraries\StripeInterface;
use App\libraries\TcpdfInterface;


class SubscriptionController extends BaseController {

	public function __construct()
        {
                $obj = new helpers();
                view()->share('obj',$obj);
                header('Content-Type: application/json');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Credentials: true');
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
                header("Access-Control-Allow-Headers: X-Requested-With");
        }


        /*
         * @method: sendSubscriptionMail
         * @params: FrXXXXXXXXXXXXData $XXXX_data, SubscriptionData $plan_data
         * @description: Sends email when XXXX is assigned a plan or changes a plan
         * @return: JSON
         * @createdBy: Manindra SDEI
         */
        public function sendSubscriptionMail($ff_data = null, $plan_data = null, $user_email = null){
            //return true;
                if(empty($ff_data) || empty($plan_data) || empty($user_email)){
                        $response_arr['msg'] = "Email Receiver Details Not Found";
                        $response_arr['status'] = "0";
                        echo json_encode($response_arr); exit;
                }

                $email_template = $this->email_template_content(5);
                $email_template_detls = $this->email_template_details(5);
                //$email_template = $this->get_email_template('freight_forwarder_registration');

                $body = str_replace(
                                /*THIS*/array("[company_name]", "[plan_name]",'[ff_domain]',"[plan_price]"),
                                /*WITH THIS*/array($ff_data["company_name"], $plan_data["subscription_name"], $ff_data["account_url"], $plan_data["price"]),
                                /*IN THIS*/$email_template
                        );
                //$admin_users_email = $this->get_site_email(); // info@freightalia.com
                $frs_default_from = $this->get_default_frs_sendout_email();
                $frs_default_name = $this->get_default_frs_name();

                $user_name = 'Company123';

                $email_subject = $email_template_detls['subject'];
                $email_signature = $email_template_detls['email_signature'];
                $email_active = $email_template_detls['email_active'];
                if($email_active == '1'){

                    $company_name = $ff_data["company_name"];

                        if(Mail::send(
                                //TEMPLATE VIEW FILE
                                ['html' => 'emails.ff_subscription'],
                                //VIEW FILE VARS
                                array('message_body' => $body,'email_signature' => $email_signature),
                                //EMAIL OBJECT {$message}
                                function($message) use ($frs_default_from, $frs_default_name, $user_email,$user_name, $email_subject){
                                        $message->from($frs_default_from, $frs_default_name);
                                        $message->to($user_email)->subject($email_subject);
                                }
                        )){
                                #MAIL SENT
                                return true;

                        }
                        else{
                                #MAIL SENDING ERROR
                                $response_arr['msg'] = "Email sending error";
                                $response_arr['status'] = "0";
                        }
                }
                else{
                        # INACTIVE TEMPLATE
                        $response_arr['msg'] = "Email content inactive";
                        $response_arr['status'] = "0";
                }

                echo json_encode($response_arr);
                die;
        }


}
