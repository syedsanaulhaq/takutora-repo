<?php

/**
 * @file
 * Contains \Drupal\miniorange_oauth_client\Form\MiniorangeOAuthClientCustomerSetup.
 */

namespace Drupal\oauth_login_oauth2\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oauth_login_oauth2\MiniorangeOAuthClientCustomer;
use Drupal\Core\Form\FormBase;
use Drupal\oauth_login_oauth2\Utilities;

class MiniorangeOAuthClientCustomerSetup extends FormBase
{
    public function getFormId() {
        return 'miniorange_oauth_client_customer_setup';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        global $base_url;

        $current_status = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_status');
        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "oauth_login_oauth2/oauth_login_oauth2.admin",
                    "oauth_login_oauth2/oauth_login_oauth2.style_settings",
                    "oauth_login_oauth2/oauth_login_oauth2.module",
                    "oauth_login_oauth2/oauth_login_oauth2.slide_support_button",
                )
            ),
        );
        if ($current_status == 'VALIDATE_OTP')
        {
            $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
            );

            $form['markup_register'] = array(
                '#type' => 'fieldset',
                '#title' => t('OTP VALIDATION<hr>'),
                '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
                '#markup' => '<br><br>',
            );

            $form['markup_register']['miniorange_oauth_client_customer_otp_token'] = array(
                '#type' => 'textfield',
                '#title' => t('OTP'),
                '#attributes' => array('style' => 'width:30%;'),
            );

            $form['markup_register']['mo_btn_brk'] = array('#markup' => '<br><br>');

            $form['markup_register']['miniorange_oauth_client_customer_validate_otp_button'] = array(
                '#type' => 'submit',
                '#value' => t('Validate OTP'),
                '#submit' => array('::miniorange_oauth_client_validate_otp_submit'),
            );

            $form['markup_register']['miniorange_oauth_client_customer_setup_resendotp'] = array(
                '#type' => 'submit',
                '#value' => t('Resend OTP'),
                '#submit' => array('::miniorange_oauth_client_resend_otp'),
            );

            $form['markup_register']['miniorange_oauth_client_customer_setup_back'] = array(
                '#type' => 'submit',
                '#value' => t('Back'),
                '#submit' => array('::miniorange_oauth_client_back'),
            );

            Utilities::schedule_a_meeting($form, $form_state);
            $form['markup_register']['header_top_div_end'] = array('#markup' => '</div>');
            Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
            return $form;
        }else if($current_status == 'already_registered'){

            $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container"><h2>Login with mini<span class="orange">O</span><span>range</h2><hr>',
            );

            $form['miniorange_oauth_client_customer_setup_username'] = array(
                '#type' => 'textfield',
                '#title' => t('Email'),
                '#required' => True,
                '#attributes' => array(
                    'style' => 'width:50%'
                ),
            );

            $form['miniorange_oauth_client_customer_setup_password'] = array(
                '#type' => 'password',
                '#title' => t('Password'),
                '#required' => True,
                '#attributes' => array(
                    'style' => 'width:50%'
                ),
            );

            $form['login_submit'] = array(
                '#type' => 'submit',
                '#button_type' => 'primary',
                '#value' => t('Login')
            );

            $form['back_button'] = array(
                '#type' => 'submit',
                '#submit' => array('::back_to_register_tab'),
                '#limit_validation_errors' => array(),
                '#value' => t('Create an account?'),
                '#suffix' => '</div>'
            );

            Utilities::schedule_a_meeting($form, $form_state);
            $form['mo_markup_div_end']=array('#markup'=>'</div>');
            Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
            return $form;

        }elseif ($current_status == 'PLUGIN_CONFIGURATION')
        {

            $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container">',
            );

            $form['markup_register'] = array(
                '#type' => 'fieldset',
                '#attributes' => array( 'style' => 'margin-bottom:2%' ),
                '#markup' => '<br>',
            );

            $form['markup_register']['mo_message_wlcm'] = array(
                '#markup' => '<div class="mo_oauth_client_welcome_message">Thank you for registering with miniOrange',
            );

            $form['markup_register']['mo_user_profile'] = array(
                '#markup' => '</div><br><br><b>Profile Details: </b>'
            );

            $modules_info = \Drupal::service('extension.list.module')->getExtensionInfo('oauth_login_oauth2');
            $modules_version = $modules_info['version'];

            $header = ['Attribute','Value'];

            $options = array(
                array( 'Customer Email', \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email'),),
                array( 'Customer ID', \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_id'),),
                array( 'Drupal Version', \DRUPAL::VERSION,),
                array( 'PHP Version', phpversion(),),
                array( 'Module Version', $modules_version,),
            );

            $form['markup_register']['fieldset']['customerinfo'] = array(
                '#theme' => 'table',
                '#header' => $header,
                '#rows' => $options,
                '#attributes' => ['class' => ['mo_register_login_table']],
            );

            $form['markup_register']['fieldset']['remove_account_info'] = array(
                '#markup' => t('<br/><h4>Remove Account:</h4><p>This section will help you to remove your current
                        logged in account without losing your current configurations.</p>')
            );

            $form['markup_register']['fieldset']['remove_account_button'] = array(
                '#markup' => '<a href="removeaccount" data-dialog-type="modal" data-dialog-options="{&quot;width&quot;:&quot;50%&quot;}" class = "use-ajax button">Remove Account</a>'
            );

            $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

            $form['markup_register']['miniorange_oauth_client_support_div_cust'] = array(
                '#markup' => '<br><br><br><br></div>'
            );

            Utilities::schedule_a_meeting($form, $form_state);
            $form['mo_markup_div_end2']=array('#markup'=>'</div>');
            Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
            return $form;
        }

        $form['header_top_style_1'] = array('#markup' => '<div class="mo_oauth_table_layout_1">');

        $form['markup_top'] = array(
            '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container"><h2>Register with mini<span class="orange">O</span><span>range</h2><hr>',
        );

        $form['markup_register'] = array(
            '#type' => 'fieldset',
            '#title' => t('Why should I register?'),
            '#attributes' => array( 'style' => 'padding:2% 2% 5%; margin-bottom:2%' ),
        );

        $form['markup_register']['markup_2'] = array(
            '#markup' => '<br><div class="mo_oauth_highlight_background_note_export">
            You should register so that in case you need help, we can help you with step-by-step instructions. <b>You will also need a miniOrange account to upgrade to the premium version of the module.</b> We do not store any information except the email that you will use to register with us. Please enter a valid email ID that you have access to. We will send OTP to this email for verification.<br></div><br>',
        );

        $form['markup_register']['mo_register'] = array(
            '#markup' => t('<div class="mo_oauth_highlight_background_note_export" style="width: auto">If you face any issues during registration then you can <b><a href="https://www.miniorange.com/businessfreetrial" target="_blank">click here</a></b> to register and use the same credentials below to login into the module.</div><br>'),
        );

        $form['markup_register']['miniorange_oauth_client_customer_setup_username'] = array(
            '#type' => 'textfield',
            '#title' => t('Email'),
            '#attributes' => array('style' => 'width:50%;', 'placeholder' => 'Enter your email'),
            '#required' => TRUE,
        );

        $form['markup_register']['miniorange_oauth_client_customer_setup_phone'] = array(
            '#type' => 'textfield',
            '#title' => t('Phone'),
            '#attributes' => array('style' => 'width:50%;'),
            '#description' => '<b>NOTE:</b> We will only call if you need support.'
        );

        $form['markup_register']['miniorange_oauth_client_customer_setup_password'] = array(
            '#type' => 'password_confirm',
            '#required' => TRUE,
        );

        $form['markup_register']['miniorange_oauth_client_customer_setup_button'] = array(
            '#type' => 'submit',
            '#value' => t('Submit'),
            '#attributes' => ['style' => 'float:left;'],
            '#button_type' => 'primary',
            '#prefix' => '<br><span>',
        );

        $form['markup_register']['miniorange_oauth_login_customer_setup_alredy_registered_button'] = array(
            '#type' => 'submit',
            '#value' => t('Already have an account?'),
            '#submit' => array('::already_registered'),
            '#limit_validation_errors' => array(),
            '#suffix' => '</span>',
        );

        $form['markup_register']['markup_divEnd'] = array(
            '#markup' => '</div>'
        );

        Utilities::schedule_a_meeting($form, $form_state);
        $form['mo_markup_div_end']=array('#markup'=>'</div>');
        Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {

        $current_status = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_status');

        if($current_status == 'already_registered'){
            $username = trim($form['miniorange_oauth_client_customer_setup_username']['#value']);
            $phone = '';
            $password = trim($form['miniorange_oauth_client_customer_setup_password']['#value']);
        }else{
            $username = trim($form['markup_register']['miniorange_oauth_client_customer_setup_username']['#value']);
            $phone = $form['markup_register']['miniorange_oauth_client_customer_setup_phone']['#value'];
            $password = trim($form['markup_register']['miniorange_oauth_client_customer_setup_password']['#value']['pass1']);

            if(strlen($password)<6){
                \Drupal::messenger()->addMessage(t('Password is too short.'), 'error');
                return;
            }
        }

        if(empty($username)||empty($password)){
            \Drupal::messenger()->addMessage(t('The <b><u>Email </u></b> and <b><u>Password</u></b> fields are mandatory.'), 'error');
            return;
        }
        if (!\Drupal::service('email.validator')->isValid($username)) {
            \Drupal::messenger()->addMessage(t('The email address <i>' . $username . '</i> is not valid.'), 'error');
            return;
        }
        $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, $password, NULL);
        $check_customer_response = json_decode($customer_config->checkCustomer());
        if ($check_customer_response->status == 'CUSTOMER_NOT_FOUND') {
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_email', $username)->save();
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_phone', $phone)->save();
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_password', $password)->save();
            $send_otp_response = json_decode($customer_config->sendOtp());

            if ($send_otp_response->status == 'SUCCESS') {
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_tx_id', $send_otp_response->txId)->save();
                $current_status = 'VALIDATE_OTP';
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                \Drupal::messenger()->addMessage(t('Verify email address by entering the passcode sent to @username', [
                    '@username' => $username
                ]));
            }
        }
        elseif ($check_customer_response->status == 'CURL_ERROR') {
            \Drupal::messenger()->addMessage(t('cURL is not enabled. Please enable cURL'), 'error');
        }
        else {
            $customer_keys_response = json_decode($customer_config->getCustomerKeys());
            if (json_last_error() == JSON_ERROR_NONE) {
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_id', $customer_keys_response->id)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_token', $customer_keys_response->token)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_email', $username)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_phone', $phone)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_api_key', $customer_keys_response->apiKey)->save();
                $current_status = 'PLUGIN_CONFIGURATION';
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                \Drupal::messenger()->addMessage(t('Successfully retrieved your account.'));
            }
            elseif($check_customer_response->status == 'TRANSACTION_LIMIT_EXCEEDED') {
                \Drupal::messenger()->addMessage(t('An error has been occured. Please try after some time or contact us at <a href="mailto:drupalsupport@xecurify.com" target="_blank">drupalsupport@xecurify.com</a>.'), 'error');
                return;
            }
            else{
                \Drupal::messenger()->addMessage(t('Invalid credentials.'), 'error');
                return;
            }
        }
    }

    public static function setup_call(array &$form, FormStateInterface $form_state){
        Utilities::schedule_a_call($form, $form_state);
    }

    public function already_registered(){
        $current_status = 'already_registered';
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', $current_status)->save();
    }

    public function back_to_register_tab(){
        $current_status = '';
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', $current_status)->save();
    }

    public function miniorange_oauth_client_back(&$form, $form_state) {
        $current_status = 'CUSTOMER_SETUP';
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', $current_status)->save();
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->clear('miniorange_miniorange_oauth_client_customer_admin_email')->save();
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->clear('miniorange_oauth_client_customer_admin_phone')->save();
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->clear('miniorange_oauth_client_tx_id')->save();
        \Drupal::messenger()->addMessage(t('Register/Login with your miniOrange Account'),'status');
    }

    public function miniorange_oauth_client_resend_otp(&$form, $form_state) {
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->clear('miniorange_oauth_client_tx_id')->save();
        $username = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email');
        $phone = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_phone');
        $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, NULL, NULL);
        $send_otp_response = json_decode($customer_config->sendOtp());
        if ($send_otp_response->status == 'SUCCESS') {
            // Store txID.
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_tx_id', $send_otp_response->txId)->save();
            $current_status = 'VALIDATE_OTP';
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', $current_status)->save();
            \Drupal::messenger()->addMessage(t('Verify email address by entering the passcode sent to @username', array('@username' => $username)));
        }
    }

    public function miniorange_oauth_client_validate_otp_submit(&$form, $form_state) {
        $otp_token = trim($form['markup_register']['miniorange_oauth_client_customer_otp_token']['#value']);
        if ($otp_token == NULL)
        {
            \Drupal::messenger()->addMessage(t('Please enter OTP first.'), 'error');
            return;
        }
        $username = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email');
        $phone = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_phone');
        $tx_id = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_tx_id');
        $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, NULL, $otp_token);
        $validate_otp_response = json_decode($customer_config->validateOtp($tx_id));
        if ($validate_otp_response->status == 'SUCCESS')
        {
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->clear('miniorange_oauth_client_tx_id')->save();
            $password = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_password');
            $customer_config = new MiniorangeOAuthClientCustomer($username, $phone, $password, NULL);
            $create_customer_response = json_decode($customer_config->createCustomer());
            if ($create_customer_response->status == 'SUCCESS') {
                $current_status = 'PLUGIN_CONFIGURATION';
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', $current_status)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_email', $username)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_phone', $phone)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_admin_token', $create_customer_response->token)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_id', $create_customer_response->id)->save();
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_customer_api_key', $create_customer_response->apiKey)->save();
                \Drupal::messenger()->addMessage(t('Customer account created.'));
            }
            else if(trim($create_customer_response->message) == 'Email is not enterprise email.' || ($create_customer_response->status) == "INVALID_EMAIL_QUICK_EMAIL")
            {
                \Drupal::messenger()->addMessage(t('There was an error creating an account for you.<br> You may have entered an invalid Email-Id
                        <strong>(We discourage the use of disposable emails) </strong>
                        <br>Please try again with a valid email.'), 'error');
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_status', 'CUSTOMER_SETUP')->save();
            }
            else {
                \Drupal::messenger()->addMessage(t('Error in creating an account for you. Please try again.'), 'error');
                return;
            }
        }
        else {
            \Drupal::messenger()->addMessage(t('Invalid OTP provided. Please enter the correct OTP.'), 'error');
            return;
        }
    }

}
