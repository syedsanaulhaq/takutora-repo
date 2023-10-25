<?php
namespace Drupal\oauth_login_oauth2;

class handler{

    /**
     * Sending feedback email to drupalsupport
     */
    public static function sendFeedbackEmail($email,$reason,$q_feedback,$skip_feedback)
    {
        $config = \Drupal::config('oauth_login_oauth2.settings');

        $modules_info = \Drupal::service('extension.list.module')->getExtensionInfo('oauth_login_oauth2');
        $modules_version = $modules_info['version'];

        $_SESSION['mo_other'] = "False";

        $app_name = $config->get('miniorange_oauth_login_config_application');
        $authorize_endpoint = $app_name == 'oauth2' ? $config->get('miniorange_auth_client_authorize_endpoint'): NULL;

        if ($skip_feedback){
            $message = $authorize_endpoint != NULL
                ? 'Skipped feedback <br>'.'Reason: '.$reason.'<br><br>Selected App: '.$app_name.'<br><br>Authorize Endpoint: '.$authorize_endpoint
                : 'Skipped feedback <br>'.'Reason: '.$reason.'<br><br>Selected App: '.$app_name;
        }else {
            $message = $authorize_endpoint != NULL
                ?'Reason: '.$reason.'<br>Feedback: '.$q_feedback . '<br><br>Selected App: '.$app_name.'<br><br>Authorize Endpoint: '.$authorize_endpoint
                :'Reason: '.$reason.'<br>Feedback: '.$q_feedback . '<br><br>Selected App: '.$app_name;
        }

        $config = \Drupal::config('oauth_login_oauth2.settings');
        $email  = $email;
        if(empty($email)){
            $site_mail         = \Drupal::config('system.site')->get('mail');
            $admin_email       = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email');
            $email             = !empty($admin_email) ?  $admin_email : $site_mail;
        }
        $phone = $config->get('miniorange_oauth_client_customer_admin_phone');
        $install_date = $config->get('miniorange_oauth_install_date');
        $customerKey= $config->get('miniorange_oauth_client_customer_id');
        $apikey = $config->get('miniorange_oauth_client_customer_api_key');
        if($customerKey==''){
            $customerKey="16555";
            $apikey="fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        }

        $currentTimeInMillis = Utilities::get_oauth_timestamp();
        $stringToHash 		 = $customerKey .  $currentTimeInMillis . $apikey;
        $hashValue 			 = hash("sha512", $stringToHash);
        $fromEmail 			 = $email;
        $subject             = $skip_feedback == 1 ? ("Skipped Feedback for Drupal " . \DRUPAL::VERSION . " OAuth Login Module | ".$modules_version) : ("Feedback for Drupal " . \DRUPAL::VERSION . " OAuth Login Module | ".$modules_version) ;
        $query        = '[Drupal ' . Utilities::mo_get_drupal_core_version() . ' OAuth Login | '.$modules_version.' | PHP Version '.phpversion().' ]: ' . $message;

        $uninstall_data = '<div>
                    <b>Token Endpoint Status     : </b> '. print_r($config->get('miniorange_auth_client_access_token_status'), true) .' <br>
                    <b>UserInfo Endpoint Status  : </b>'.$config->get('miniorange_auth_client_userinfo_status').' <br>
                    <b>Test Configuration Status : </b>'. $config->get('miniorange_auth_client_test_configuration_status').' <br>
                    <b>SSO Status                : </b>'. $config->get('miniorange_auth_client_sso_status').'</div>';

        $content = '<div>Hello, <br><br>
                    Company : <a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
                    Phone Number : '.$phone.'<br><br>Email : <a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br>
                    Installed on : '.$install_date.'<br><br>
                    Query : '.$query.'<br><br>'.$uninstall_data.'</div>';

        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $fromEmail,
                'fromName' 	    => 'miniOrange',
                'toEmail' 		=> MiniorangeOAuthClientConstants::SUPPORT_EMAIL,
                'toName' 		=> MiniorangeOAuthClientConstants::SUPPORT_EMAIL,
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);
        $response = Utilities::callService(MiniorangeOAuthClientConstants::FEEDBACK_URL,
            $field_string,
            array('Content-Type' => 'application/json',
                'Customer-Key' => $customerKey,
                'Timestamp' => $currentTimeInMillis,
                'Authorization' => $hashValue
            ),
        );


    }
}