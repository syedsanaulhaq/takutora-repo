<?php
namespace Drupal\oauth_login_oauth2;

class AccessToken
{
    /**
     * This function gets the access token from the server
     */
    public static function getAccessToken($tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body)
    {
        Utilities::addLogger(basename(__FILE__),__FUNCTION__,__LINE__,'Access Token flow initiated.');

        if ($send_headers && !$send_body) {
            $response = Utilities::callService($tokenendpoint,
                'redirect_uri=' . urlencode($redirect_url) . '&grant_type=' . $grant_type . '&code=' . $code,
                array('Authorization' => 'Basic ' . base64_encode($clientid . ":" . $clientsecret),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded')
            );
        } elseif (!$send_headers && $send_body) {
            $response = Utilities::callService($tokenendpoint,
                'redirect_uri=' . urlencode($redirect_url) . '&grant_type=' . $grant_type . '&client_id=' . urlencode($clientid) . '&client_secret=' . urlencode($clientsecret) . '&code=' . $code,
                array('Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded')
            );
        } else {
            $response = Utilities::callService($tokenendpoint,
                'redirect_uri=' . urlencode($redirect_url) . '&grant_type=' . $grant_type . '&client_id=' . urlencode($clientid) . '&client_secret=' . urlencode($clientsecret) . '&code=' . $code,
                array('Authorization' => 'Basic ' . base64_encode($clientid . ":" . $clientsecret),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded')
            );
        }

        $content = json_decode($response,true);
        Utilities::addLogger(basename(__FILE__),__FUNCTION__,__LINE__,'Access Token Content: <pre><code>'. print_r($content, true) .'</code></pre>');

        if (!isset($_COOKIE['Drupal_visitor_mo_oauth_test']))
            Utilities::set_sso_status('Tried and failed - Token endpoint');

        if (isset($content["error"]) || isset($content["error_description"])) {
            if (isset($content["error"]) && is_array($content["error"])) {
                $content["error"] = $content["error"]["message"];
            }
            
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_auth_client_access_token_status', json_encode($content))->save();
            Utilities::show_error_message($content);
        } else if (isset($content["access_token"])) {
            $access_token = $content["access_token"];
        } else {
            exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
        }
        $message = 'Access Token received: ' . (isset($content['access_token']) && !empty($content['access_token'])) . ' And ID Token received: ' . (isset($content['id_token']) && !empty($content['id_token']));
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_auth_client_access_token_status', $message)->save();

        return $access_token;
    }
}
