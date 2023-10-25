<?php
/**
 * @file
 * Contains \Drupal\rest_api_authentication\Controller\DefaultController.
 */

namespace Drupal\rest_api_authentication\Controller;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\rest_api_authentication\Utilities;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\rest_api_authentication\MiniorangeApiAuthConstants;
use function Symfony\Component\VarDumper\Dumper\esc;
use GuzzleHttp\Client;

class rest_api_authenticationController extends ControllerBase {
  /**
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function openSupportRequestForm() {
    $response = new AjaxResponse();
    $modal_form = \Drupal::formBuilder()->getForm('\Drupal\rest_api_authentication\Form\MiniornageAPIAuthnRequestSupport');
    $response->addCommand(new OpenModalDialogCommand('Support Request/Contact Us', $modal_form, ['width' => '40%']));
    return $response;
  }

  /**
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function openTrialRequestForm() {
    $response = new AjaxResponse();
    $modal_form = \Drupal::formBuilder()->getForm('\Drupal\rest_api_authentication\Form\MiniornageAPIAuthnRequestTrial');
    $response->addCommand(new OpenModalDialogCommand('Request 7-Days Full Feature Trial License', $modal_form, ['width' => '40%']));
    return $response;
  }

  /**
   * sends feedback mail to drupalsupport
   */
  public function miniorange_API_Auth_feedback(){

     global $base_url;
     $reason="";
     if(isset($_GET['query']) && trim($_GET['query']!="")){
       $reason=$_GET['query'];
     }
     else{
      $reason = "Not Specified";
     }

     $query_feedback = $_GET['query_feedback'];

     $message = 'Reason: ' . $reason . '<br>' . 'Feedback: ' . $query_feedback;

     $config = \Drupal::config('rest_api_authentication.settings');
     if (isset($_GET['rest_feedback_submit']) || isset($_GET['rest_feedback_skip'])) {
       $module_info = \Drupal::service('extension.list.module')->getExtensionInfo('rest_api_authentication');
       $module_version = $module_info['version'];
       $_SESSION['mo_other'] = "False";
       $url = MiniorangeApiAuthConstants::BASE_URL . '/moas/api/notify/send';

       if (isset($_GET['rest_feedback_skip']) && !empty($_GET['rest_feedback_skip'])) {

         Utilities::skipped_feedback();

       } else {

         $config = \Drupal::config('rest_api_authentication.settings');
         $email = $config->get('rest_api_authentication_customer_admin_email');

         if (empty($email))
           $email = $_GET['rest_feedback_email'];

         $customerKey = $config->get('rest_api_authentication_customer_id');
         $apikey = $config->get('rest_api_authentication_customer_api_key');
         if ($customerKey == '') {
           $customerKey = "16555";
           $apikey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
         }

         $basicAuthTried = $config->get('miniorange_basic_authentication_tried');
         $apikeyAuthTried = $config->get('miniorange_api_key_authentication_tried');
         $licensePageVisited = $config->get('miniorange_rest_api_license_page_visited');
         $triedAuthMethods = ($basicAuthTried == 'Did not Try') ? 'None' : '<br>Basic Auth: ' . $basicAuthTried . '</br>';

         if (!str_contains($triedAuthMethods, 'None')) {
           $triedAuthMethods .= $apikeyAuthTried !== 'Did not Try' ? '<br>Api Key Auth: ' . $apikeyAuthTried . '</br>' : '';
         } else {
           $triedAuthMethods = $apikeyAuthTried !== 'Did not Try' ? '<br>Api Key Auth: ' . $apikeyAuthTried . '<br>' : 'None';
         }

         $skipped = isset($_GET['rest_feedback_skip']) ? TRUE : FALSE;
         $add_skip = $skipped ? "<b>Skipped: True</b><br><br>" : "";

         $users_OS = Utilities::getUsersOS();

         $installed_on = $config->get('miniorange_rest_api_installation_time_ref');
         $installed_date = date('d/m/Y H:i:s', $installed_on);
         $current_time_in_ms = Utilities::get_timestamp();
         $stringToHash = $customerKey . $current_time_in_ms . $apikey;
         $hashValue = hash("sha512", $stringToHash);

         $fromEmail = $email;
         $subject = 'Drupal ' . \DRUPAL::VERSION . ' REST API Authentication Module Feedback | ' . $module_version . ' | PHP Version ' . phpversion();
         $query = '[Drupal ' . \DRUPAL::VERSION . ' REST API Authentication | ' . $module_version . ' | PHP Version ' . phpversion() . ' ]: ' . $message;
         $content = '<div >Hello, <br><br>Company :<a href="' . $base_url . '" target="_blank" >' . $base_url . '</a><br><br>Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>Installed on: ' . $installed_date . '<br><br>Operating System:' . $users_OS . '<br><br>Payment Page Visited: ' . $licensePageVisited . '<br><br>Tried Authentication Methods: ' . $triedAuthMethods . '<br><br>' . $add_skip . 'Query: ' . $query . '</div>';
         $fields = array(
           'customerKey' => $customerKey,
           'sendEmail' => true,
           'email' => array(
             'customerKey' => $customerKey,
             'fromEmail' => $fromEmail,
             'fromName' => 'miniOrange',
             'toEmail' => 'drupalsupport@xecurify.com',
             'toName' => 'drupalsupport@xecurify.com',
             'subject' => $subject,
             'content' => $content
           ),
         );

         $field_string = json_encode($fields);


         $header = ['Content-Type' => 'application/json', 'Customer-Key' => $customerKey, 'Timestamp' => $current_time_in_ms, 'Authorization' => $hashValue];

         try {
           $response = \Drupal::httpClient()->post($url, ['headers' => $header, 'body' => $field_string, 'verify' => FALSE]);


         } catch (Exception $exception) {

         }

       }
     }

       \Drupal::configFactory()->getEditable('rest_api_authentication.settings')->clear('miniorange_rest_api_authentication_uninstall_status')->save();
       \Drupal::service('module_installer')->uninstall(['rest_api_authentication']);
       $uninstall_redirect = $base_url . '/admin/modules';
       \Drupal::messenger()->addMessage('The module has been successfully uninstalled.');
       return new RedirectResponse($uninstall_redirect);

  }
}
