<?php
namespace Drupal\oauth_login_oauth2;
class UserResource
{
    public static function getResourceOwner($resource_owner_details_url, $access_token)
    {
        Utilities::addLogger(basename(__FILE__), __FUNCTION__, __LINE__, 'Userinfo flow initiated.');

        $response = Utilities::callService($resource_owner_details_url,
            NULL,
            array('Authorization' => 'Bearer ' . $access_token),
            'GET'
        );

        if (isset($response) && !empty($response)) {
            $content = json_decode($response, true);
            Utilities::addLogger(basename(__FILE__), __FUNCTION__, __LINE__, 'Userinfo Content: <pre><code>' . print_r($content, true) . '</code></pre>');

            if (!isset($_COOKIE['Drupal_visitor_mo_oauth_test']))
                Utilities::set_sso_status('Tried and failed - Userinfo Endpoint');
            if (isset($content["error"]) || isset($content["error_description"])) {
                if (isset($content["error"]) && is_array($content["error"])) {
                    $content["error"] = $content["error"]["message"];
                }
                \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_auth_client_userinfo_status', $content)->save();
                Utilities::show_error_message($content);
            }
            \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_auth_client_userinfo_status', 'Userinfo received successfully.')->save();

            return $content;
        }
        return null;
    }

}
