<?php

namespace Drupal\oauth_login_oauth2\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;
use Drupal\oauth_login_oauth2\appData;
use Drupal\oauth_login_oauth2\Utilities;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\Markup;


class MiniorangeConfigOAuthClient extends FormBase
{
    public function getFormId() {
        return 'miniorange_oauth_client_configure_app';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        global $base_url;

        $baseUrlValue = Utilities::getOAuthBaseURL($base_url);

        $form['markup_library'] = array(
            '#attached' => array(
                'library' => array(
                    "oauth_login_oauth2/oauth_login_oauth2.admin",
                    "oauth_login_oauth2/oauth_login_oauth2.style_settings",
                    "oauth_login_oauth2/oauth_login_oauth2.mo_search_field",
                    'core/drupal.dialog.ajax',
                )
            ),
        );

        $config = \Drupal::config('oauth_login_oauth2.settings');
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');

        $status= $config->get('miniorange_oauth_login_config_status');
        $attributes_arr =  array('style' => 'width:73%;');

        $name = \Drupal::request()->query->get('app_name') !== null ? \Drupal::request()->query->get('app_name') : \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_login_config_application');
        $action = \Drupal::request()->query->get('action');

        if($status == '')
            $status = 'select_application';

        $form['markup_top'] = array(
            '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">',
        );

// Step 1/3: Callback URL
        if ($status == 'callback'){
            if(!empty($config->get('miniorange_auth_client_callback_uri')) && (strpos($config->get('miniorange_auth_client_callback_uri'), 'default') === false)){
                $callbackUrl = $config->get('miniorange_auth_client_callback_uri');
            }else{
                $request=\Drupal::request();
                $base_url=$request->getSchemeAndHttpHost().$request->getBaseUrl();
                $callbackUrl=$base_url."/mo_login";
                $configFactory->set('miniorange_auth_client_callback_uri', $callbackUrl)->save();
            }

            self::guide_links($form);

            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">',
            );

            $form['markup_callback_markup'] = array(
                '#markup' => '<h2>Step 1/3 - Setting up a Relying Party / OAuth Provider</h2><hr><br>',
            );

            $form['markup_top_callback']['miniorange_oauth_client_name_attr_title'] = array(
                '#markup' => 'Copy below-mentioned Callback/Redirect URL and configure it in your OAuth Provider.<br><br>
                     <div class="container-inline"><div class="mo_oauth_attr_mapping_label mo-callback"><b>Callback/Redirect URL: </b> </div>',
            );

            $form['markup_top_callback']['miniorange_oauth_client_callback_url'] = array(
                '#markup' => '<span id="Callback_textfield">'.$callbackUrl.'</span>',
                '#prefix' => '<div class= "mo_oauth_highlight_background_callback">',
                '#suffix' => '</div>',
            );

            $form['markup_top_callback']['test'] = array(
                '#value' => t('&#128461; Copy'),
                '#type' => 'submit',
                '#id' => 'copy_button',
                '#attributes' => ['onclick' => 'CopyToClipboard(Callback_textfield)', 'class' => ['use-ajax mo_oauth_login_left_copy']],
                '#ajax' => [
                    'event' => 'click',
                    'progress' => [
                        'type' => 'throbber',
                        'message' => $this->t('Copying'),
                    ]
                ],
                '#suffix' => '</div><br><br>',
            );

            $form['markup_top_callback']['miniorange_oauth_client_change_app'] = array(
                '#type' => 'submit',
                '#value' => t('&#11164; Change Application'),
                '#button_type' => 'danger',
                '#submit' => array('::miniorange_oauth_reset_configurations'),
            );

            $form['markup_top_callback']['miniorange_oauth_client_next'] = array(
                '#type' => 'submit',
                '#value' => t('Step 2/3 &nbsp; &#11166;'),
                '#button_type' => 'primary',
                '#attributes' => ['class' => ['mo-guides-floating'],],
                '#submit' => array('::miniorange_oauth_client_credentials'),

            );

            $form['markup_top_callback']['markup_end1'] = array(
                '#markup' => '</div>',
            );

        }

// Select Application/Provider
        else if ($status == 'select_application') {

            $form['markup_top_vt_start1'] = array(
                '#markup' => '<h3>CONFIGURE APPLICATION <a class="button mo_top_guide_button" target="_blank" href="https://www.drupal.org/docs/contributed-modules/drupal-oauth-oidc-login">&#128366; Setup Guides</a><a class="button" target="_blank" href="https://youtube.com/playlist?list=PL2vweZ-PcNpcL0xy5LGD7X8LO2dblwHHl">&#x23E9; Video Guides</a></h3><hr>',
            );

            $form['mo_text_search'] = [
                '#type' => 'textfield',
                '#prefix' => '<div class="mo-select-app mo-select-app-margin">',
                '#title' => $this->t('Search Provider/Application'),
                '#placeholder' => $this->t('Search your Provider'),
                '#attributes' => [
                    'id' => 'mo_text_search',
                    'onkeyup' => 'searchApp()',
                ],
                '#suffix' => '</div>'
            ];

            $form['mo_markup_1'] = array(
                '#markup' => '<div id="custom_oauth_oidc_apps">'
            );

            $oauth_apps = appData::app_list('oauth_apps');
            $custom_oauth = appData::app_list('custom_oauth');
            $oidc_apps = appData::app_list('oidc_apps');

            $form['mo_markup_2'] = array(
                '#markup' => '</div><div id="oauth_apps"><h4> OAuth 2.0 supported applications </h4><hr></div>'
            );

            $form['mo_application_list'] = array(
                '#prefix' => '<ul id="mo_search_ul" class="mo-wrap mo-flex-container">',
                '#markup' => implode('</li>', $oauth_apps),
                '#suffix' => '</li></ul> ',
            );

            $form['mo_markup_3'] = array(
                '#markup' => '<div id="oidc_apps"><h4>OpenID Connect supported applications <a href="licensing" class="mo-note-oidc">[Note: We support OpenID protocol in Premium and Enterprise version of the module] </a></h4><hr></div>'
            );

            $form['mo_application_list_oidc'] = array(
                '#prefix' => '<ul id="mo_search_ul_oidc" class="mo-wrap mo-flex-container"><li class="mo-flex-item disabled">',
                '#markup' => implode('</li><li class="mo-flex-item disabled">', $oidc_apps),
                '#suffix' => '</li></ul> ',
            );

            $form['mo_markup_4'] = array(
                '#markup' => '<h4>Custom OAuth/OIDC applications</h4><hr> <div id="provider_not_present" class="mo-select-app-margin mo_oauth_highlight_background_note_export"><strong>Note: If your provider is not listed, you can select custom provider to configure the module. Please send us a query using miniOrange icon<i> (bottom right corner)</i> if you need any help in the configuration.</strong></div> '
            );

            $form['mo_custom_application_list'] = array(
                '#prefix' => '<ul id="mo_search_custom_ul" class="mo-wrap mo-flex-container">',
                '#markup' => implode('</li>', $custom_oauth),
                '#suffix' => '</li></ul>',
            );

        }

// Step 2/3: Enter Client ID and Client Secret
        else if ($status == 'client_credentials'){

            self::guide_links($form);
            $name = \Drupal::request()->query->get('app_name') !== null ? \Drupal::request()->query->get('app_name') : \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_login_config_application');

            $display_link = $config->get('miniorange_auth_client_display_link');

            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">',
            );

            $form['mo_client_credentials'] = array(
                '#markup' => '<h2>Step 2/3 - Client Credentials Setup</h2><hr><br>',
            );

            $form['markup_top_client_credentials']['miniorange_oauth_client_display_name'] = array(
                '#type' => 'textfield',
                '#default_value' => !empty($display_link) ? $display_link : 'Login using '.$name,
                '#title' => t('Text of the SSO login link on the login page: '),
                '#required' => TRUE,
                '#description' => t('<b>Note:</b> By default the login link will appear on the user login page in this manner.'),
                '#attributes' => array('placeholder' => 'Login using ##app_name##', 'style' => 'width:73%;'),
            );

            $form['markup_top_client_credentials']['miniorange_oauth_client_id'] = array(
                '#type' => 'textfield',
                '#default_value' => $config->get('miniorange_auth_client_client_id'),
                '#title' => t('Client ID: '),
                '#required' => TRUE,
                '#maxlength' => 1048,
                '#description' => "You will get this value from your OAuth Server",
                '#attributes' => $attributes_arr,
            );

            $form['markup_top_client_credentials']['miniorange_oauth_client_secret'] = array(
                '#type' => 'textfield',
                '#default_value' => $config->get('miniorange_auth_client_client_secret'),
                '#description' => "You will get this value from your OAuth Server",
                '#title' => t('Client Secret: '),
                '#required' => TRUE,
                '#maxlength' => 1048,
                '#attributes' => $attributes_arr,
                '#suffix' => '<br>',
            );

            $form['markup_top_client_credentials']['miniorange_oauth_client_callback'] = array(
                '#type' => 'submit',
                '#value' => t('&#11164; Back'),
                '#button_type' => 'danger',
                '#limit_validation_errors' => array(),
                '#submit' => array('::miniorange_oauth_back_to_callback'),
            );

            $form['markup_top_client_credentials']['miniorange_oauth_client_next'] = array(
                '#type' => 'submit',
                '#value' => t('Step 3/3 &nbsp; &#11166;'),
                '#button_type' => 'primary',
                '#attributes' => ['class' => ['mo-guides-floating'],],
                '#submit' => array('::miniorange_oauth_endpoints'),
            );

            $form['markup_top_client_credentials']['markup_end1'] = array(
                '#markup' => '</div>',
            );
        }

// Step 3/3: Endpoints
        else if ($status == 'endpoints'){
            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">',
            );

            self::guide_links($form);
            $config = \Drupal::config('oauth_login_oauth2.settings');
            $name = \Drupal::request()->query->get('app_name') !== null ? \Drupal::request()->query->get('app_name') : $config->get('miniorange_oauth_login_config_application');

            $endpoints = appData::endpoints($name);

            $form['mo_endpoints'] = array(
                '#markup' => '<h2>Step 3/3 - Scope and Endpoints</h2><hr><br>',
            );

            $form['markup_top_endpoints']['markup_top_discovery_url'] = array(
                '#type' => 'details',
                '#title' => t('Discovery / Well-Known URL'),
                '#attributes' => ['style' => 'width: 80%']
            );

            $form['markup_top_endpoints']['markup_top_discovery_url']['miniorange_oauth_discovery_url'] = array(
                '#type' => 'url',
                '#default_value' => $config->get('miniorange_oauth_client_discovery_url'),
                '#title' => t('OAuth Provider\'s Metadata URL / Well-Known Endpoint: '),
                '#description' => t('You can find this URL in your OAuth Provider\'s Endpoints section'),
                '#maxlength' => 1048,
                '#attributes' => ['style' => 'width: 80%']
            );

            $form['markup_top_endpoints']['markup_top_discovery_url']['miniorange_oauth_discovery_url_fetch_button'] = array(
                '#type' => 'submit',
                '#limit_validation_errors' => array(),
                '#value' => t('Fetch'),
                '#button_type' => 'primary',
                '#submit' => array('::fetch_oauth_endpoints'),
                '#attributes' => array('style' => 'float: right; margin-top: -93px;',),
                '#states' => [
                    'disabled' => [
                        ':input[name="miniorange_oauth_discovery_url"]' => ['value' =>''],
                    ],
                ],
            );

            $form['markup_top_endpoints']['miniorange_oauth_client_or'] = array(
                '#markup' => '<div class="mo-table-button-center"><h2>OR</h2></div>',
            );


            $form['markup_top_endpoints']['miniorange_oauth_scope'] = array(
                '#type' => 'textfield',
                '#default_value' => !empty($config->get('miniorange_auth_client_scope')) ? $config->get('miniorange_auth_client_scope') : $endpoints['Scope: '],
                '#title' => t('Scope: '),
                '#required' => false,
                '#maxlength' => 1048,
                '#states' => [
                    'required' => [
                        ':input[name="miniorange_oauth_discovery_url"]' => ['value' =>''],
                    ],
                ],
            );

            $form['markup_top_endpoints']['miniorange_oauth_authorize_endpoint'] = array(
                '#type' => 'url',
                '#default_value' => !empty($config->get('miniorange_auth_client_authorize_endpoint')) ? $config->get('miniorange_auth_client_authorize_endpoint') : $endpoints['Authorization Endpoint: '],
                '#title' => t('Authorize Endpoint: '),
                '#maxlength' => 1048,
                '#attributes' => ['class' => ['mo-endpoints-fiels-width'],],
                '#states' => [
                    'required' => [
                        ':input[name="miniorange_oauth_discovery_url"]' => ['value' =>''],
                    ],
                ],
            );

            $form['markup_top_endpoints']['miniorange_oauth_access_token_endpoint'] = array(
                '#type' => 'url',
                '#default_value' => !empty($config->get('miniorange_auth_client_access_token_ep')) ? $config->get('miniorange_auth_client_access_token_ep') : $endpoints['Access Token Endpoint: '],
                '#title' => t('Access Token Endpoint: '),
                '#maxlength' => 1048,
                '#attributes' => ['class' => ['mo-endpoints-fiels-width'],],
                '#states' => [
                    'required' => [
                        ':input[name="miniorange_oauth_discovery_url"]' => ['value' =>''],
                    ],
                ],
            );

            $form['markup_top_endpoints']['miniorange_oauth_userinfo_endpoint'] = array(
                '#type' => 'url',
                '#default_value' => !empty($config->get('miniorange_auth_client_user_info_ep')) ? $config->get('miniorange_auth_client_user_info_ep') : $endpoints['Userinfo Endpoint: '],
                '#title' => t('Userinfo Endpoint: '),
                '#maxlength' => 1048,
                '#attributes' => ['class' => ['mo-endpoints-fiels-width'],],
                '#states' => [
                    'required' => [
                        ':input[name="miniorange_oauth_discovery_url"]' => ['value' =>''],
                    ],
                ],
            );

            $form['markup_top_endpoints']['miniorange_oauth_client_client_creds'] = array(
                '#type' => 'submit',
                '#prefix' => '<br>',
                '#value' => t('&#11164; Back'),
                '#button_type' => 'danger',
                '#limit_validation_errors' => array(),
                '#submit' => array('::miniorange_oauth_back_to_client_credentials'),
            );

            $form['markup_top_endpoints']['miniorange_oauth_client_summary'] = array(
                '#type' => 'submit',
                '#value' => t('All Done! &#11166;'),
                '#button_type' => 'primary',
                '#attributes' => ['class' => ['mo-guides-floating'],],
                '#submit' => array('::miniorange_oauth_final_summary'),
            );

            $form['markup_top_endpoints']['markup_end1'] = array(
                '#markup' => '</div>',
            );
        }

        else if ($status == 'final' && $action == 'update') {

            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">',
            );

            self::guide_links($form);
            $name = \Drupal::request()->query->get('app_name') !== null ? \Drupal::request()->query->get('app_name') : \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_login_config_application');
            $endpoints = appData::endpoints($name);

            $form['mo_final_summary'] = array(
                '#markup' => '<h2>Summary</h2><hr>',
            );

            $form['miniorange_oauth_client_summary'] = array(
                '#type' => 'table',
                '#responsive' => TRUE ,
                '#attributes' => ['style' => 'border-collapse: separate;'],
            );

            $data = self::mo_data_configurations();

            foreach ($data as $key => $value) {
                $row = self::miniorange_oauth_client_table_data($key, $value, $endpoints);
                $form['miniorange_oauth_client_summary'][$key] = $row;
            }

            $form['miniorange_oauth_client_next'] = array(
                '#type' => 'submit',
                '#value' => t('Save Configuration'),
                '#button_type' => 'primary',
                '#prefix' => '<br> <div class="mo-table-button-center">',
                '#submit' => array('::miniorange_oauth_complete_configuration'),
            );

            $form['miniorange_oauth_client_test_config'] = array(
                '#type' => 'button',
                '#value' => t('Test Configuration'),
                '#button_type' => 'primary',
                '#attributes' => ['onclick' => 'test_configuration_window()', 'class' => ['use-ajax']],
                '#ajax' => [
                    'event' => 'click',
                    'progress' => 'none'
                ],
            );

            $form['miniorange_oauth_client_reset'] = array(
                '#type' => 'submit',
                '#value' => t('Reset Configurations'),
                '#suffix' => '</div>',
                '#limit_validation_errors' => array(),
                '#button_type' => 'danger',
                '#submit' => array('::miniorange_oauth_reset_configurations'),
            );

            $form['mo_attribute_mapping'] = array(
                '#markup' => '<br><div id="attribute_mapping"><h2>Attribute Mapping</h2></div><hr><br>',
            );

            $form['mo_vt_id_start1'] = array(
                '#markup' => '<div class="mo_oauth_client_highlight_background_note_1">Attributes are the user details that are stored by your OAuth server(s). Attribute Mapping helps you get user attributes/fields from your OAuth server and map them to your Drupal site user attributes.</div>
            <br><div id = "mo_oauth_vt_attrn" class="container-inline"> <b>Note: </b>Please select the attribute name with <b>email</b> from the dropdown of <b>Received Attribute List</b> for successful SSO. If your desired attribute is not listed in the dropdown, please select <b>Other</b> in the dropdown and then enter the desired attribute in the text-field.<br>',
            );
            $config = \Drupal::config('oauth_login_oauth2.settings');

            $email_attr = $config->get('miniorange_oauth_client_email_attr_val');

            $attrs = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_attr_list_from_server');
            $attrs = isset($attrs) && !empty($attrs) ? json_decode($attrs, TRUE) : '';

            $options = array();
            if (is_array($attrs)) {
                foreach ($attrs as $key => $value) {
                    if (is_array($value)){
                        foreach ($value as $key1 => $value1) {
                            $options[$key1] = $key1;
                        }
                        continue;
                    }
                    $options[$key] = $key;
                }
            }
            $options['other'] = 'Other';
            $other_email_attr = $email_attr == 'other' ? $config->get('miniorange_oauth_client_other_field_for_email') : '';
            $data = ['email_attr' => 'miniorange_oauth_client_email_attr_val'];

            $form['miniorange_oauth_login_mapping'] = array(
                '#type' => 'table',
                '#responsive' => TRUE,
                '#header' => [
                    t('Attributes'),
                    t('Received Attribute List'),
                    t('If Other selected'),
                ],
                '#attributes' => ['style' => 'border-collapse: separate;'],
            );

            foreach ($data as $key => $value) {
                $row = self::miniorange_oauth_client_table_data_mapping($key, $value, $options, $config, $other_email_attr);
                $form['miniorange_oauth_login_mapping'][$key] = $row;
            }

            $form['miniorange_oauth_client_attr_setup_button_2'] = array(
                '#type' => 'submit',
                '#value' => t('Save Attribute Mapping'),
                '#submit' => array('::miniorange_oauth_client_attr_setup_submit'),
                '#button_type' => 'primary',
                '#prefix' => '<br>',
                '#attributes' => array('style' => 'margin: auto; display:block; '),
            );

            $form['markup_end1'] = array(
                '#markup' => '</div></div>',
            );
        }

        else{
            $form['markup_top'] = array(
                '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">',
            );

            $config = \Drupal::config('oauth_login_oauth2.settings');
            $name = \Drupal::request()->query->get('app_name') !== null ? \Drupal::request()->query->get('app_name') : $config->get('miniorange_oauth_login_config_application');

            $form['miniorange_oauth_client_msgs'] = array(
                '#markup' => "<div class='mo_oauth_highlight_background_note_2'>
                           <b class='mo_note_css'>Please Note:</b> Attribute Mapping is mandatory for login. Select the Email Attribute from Test Configuration and click on the <b>Done</b> button.</div><br>",
            );

            $form['miniorange_oauth_client'] = array(
                '#markup' => '<a data-dialog-type="modal" href="add_new_provider" class="use-ajax button button--primary add_new_provider">+ Add New Provider</a>'
            );

            $client_app = $config->get('miniorange_oauth_login_config_application');
            $client_id = $config->get('miniorange_auth_client_client_id');
            $client_id = strlen($client_id) > 25 ? substr($client_id,0,24).'...' : $client_id;

            $form['mo_oauth_client_idplist_table'] = array(
                '#type' => 'table',
                '#header' => ['Provider Name', 'Client ID', 'Test', 'Action', 'Mapping'],
                '#empty' => t('<b>You have not configured any provider yet, Please Add provider by clicking above "Add New SP" button</b>'),
                '#prefix' => '<br>',
                '#suffix' => '</div>',
                '#attributes' => ['class' => ['tableborder']],
            );

            $form['mo_oauth_client_idplist_table']['final_summary'] = self::miniOauthLoginFinalTable($client_app, $client_id, $base_url);

            $form['div2_close'] =array(
                '#markup' => '</div></div>'
            );
        }

        $form['markup_end'] = array(
            '#markup' => '</div></div>',
        );

        Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {}

    function clear_attr_list(&$form,$form_state){
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->clear('miniorange_oauth_client_show_attr_list_from_server')->save();
        Utilities::show_attr_list_from_idp($form, $form_state);
    }

    public function fetch_oauth_endpoints(array &$form, FormStateInterface $form_state){
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');

        $discovery_url = trim($form['markup_top_endpoints']['markup_top_discovery_url']['miniorange_oauth_discovery_url']['#value']);

        if (isset($discovery_url) && !empty($discovery_url)){
            $endpoints = self::discovery_endpoint($discovery_url);
            $scope = trim($endpoints['scopes']);
            $authorize_endpoint = trim($endpoints['authorization_endpoint']);
            $access_token_endpoint = trim($endpoints['token_endpoint']);
            $userinfo_endpoint = trim($endpoints['userinfo_endpoint']);
        }

        $configFactory->set('miniorange_auth_client_scope', $scope)->save();
        $configFactory->set('miniorange_oauth_client_discovery_url', $discovery_url)->save();
        $configFactory->set('miniorange_auth_client_authorize_endpoint', $authorize_endpoint)->save();
        $configFactory->set('miniorange_auth_client_access_token_ep', $access_token_endpoint)->save();
        $configFactory->set('miniorange_auth_client_user_info_ep', $userinfo_endpoint)->save();
        \Drupal::messenger()->addstatus(t('Endpoints fetched successfully. Please click on the <b>All Done!</b> button.'));

    }

    function miniorange_oauth_client_attr_setup_submit(array &$form, FormStateInterface $form_state)
    {
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');
        $form_values = $form_state->getValues();

        $email_attr = trim($form_values['miniorange_oauth_login_mapping']['email_attr']['miniorange_oauth_client_email_select']);
        if ($email_attr == 'other') {
            $other_email_attr = trim($form_values['miniorange_oauth_login_mapping']['email_attr']['miniorange_oauth_client_email_attr']);
            $configFactory->set('miniorange_oauth_client_other_field_for_email', $other_email_attr)->save();
        }

        $configFactory->set('miniorange_oauth_client_email_attr_val', $email_attr)->save();

        \Drupal::messenger()->addStatus(t('Attribute Mapping saved successfully. Please open an incognito window and go to your Drupal site’s login page, you will automatically find a <b>Login with Your OAuth Provider</b> link there.'));
    }

    public function miniorange_oauth_client_table_data_mapping($key, $value, $options, $config, $other_email_attr){

        if ($key == 'email_attr') {
            $row[$key] = [
                '#markup' => '<div class="mo-mapping-floating"><strong>Email Attribute: </strong></div>',
            ];

            $row['miniorange_oauth_client_email_select'] = [
                '#type' => 'select',
                '#id' => 'miniorange_oauth_client_email_select',
                '#default_value' => $config->get($value),
                '#options' => $options,
            ];

            $row['miniorange_oauth_client_email_attr'] = [
                '#type' => 'textfield',
                '#default_value' => $other_email_attr,
                '#id' => 'miniorange_oauth_client_other_field_for_email',
                '#attributes' => array('placeholder' => 'Enter Email Attribute'),
                '#states' => [
                    'visible' => [
                        ':input[id="miniorange_oauth_client_email_select"]' => ['value' => 'other'],
                    ],
                ],
                '#prefix' => '<div class="mo_oauth_attr_mapping_select_element">',
                '#suffix' => '</div>',
            ];
        }
        return $row;
    }

    public function miniorange_oauth_change_app(){

        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_login_config_status','select_application')->save();
        $path = Url::fromRoute('oauth_login_oauth2.config_clc')->toString();
        $response = new RedirectResponse($path);
        $response->send();
    }

    public function miniorange_oauth_back_to_callback(){
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_login_config_status','callback')->save();
    }

    public function miniorange_oauth_back_to_client_credentials(){
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');
        $configFactory->set('miniorange_oauth_login_config_status','client_credentials')->save();
    }

    public function miniorange_oauth_client_credentials(){
        \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_login_config_status','client_credentials')->save();
    }

    public function miniorange_oauth_reset_configurations(){
        $path = Url::fromRoute('oauth_login_oauth2.resetConfig')->toString();
        $response = new RedirectResponse($path);
        $response->send();
    }

    public function miniOauthLoginFinalTable($client_app, $client_id, $base_url){
        $row['server_name'] = [
            '#markup' => Markup::create($this->t(ucfirst($client_app)))
        ];

        $row['client_id'] = [
            '#markup' => Markup::create($client_id)
        ];

        $row['test_config'] = [
            '#type' => 'button',
            '#value' => t('Perform Test Configuration'),
            '#button_type' => 'primary',
            '#attributes' => ['onclick' => 'test_configuration_window()', 'class' => ['use-ajax', 'button--small']],
            '#ajax' => [
                'event' => 'click',
                'progress' => 'none'
            ],
        ];

        $row['drop_button'] = [
            '#type' => 'dropbutton',
            '#dropbutton_type' => 'small',
            '#links' => [
                'edit' => [
                    'title' => $this->t('Edit'),
                    'url' => Url::fromUri($base_url . '/admin/config/people/oauth_login_oauth2/config_clc?action=update&app=' . $client_app),
                ],

                'delete' => [
                    'title' => $this->t('Delete'),
                    'url' => Url::fromUri($base_url . '/admin/config/people/oauth_login_oauth2/reset_config'),
                ],
            ]
        ];

        $row['mapping'] = [
            '#markup' => Markup::create($this->t('<a class="button button--small" href="config_clc?action=update&app=' . $client_app.'#attribute_mapping">Configure</a>'))
        ];

        return $row;
    }

    public function miniorange_oauth_client_table_data($key, $value, $endpoints){

        global $base_url;
        $config = \Drupal::config('oauth_login_oauth2.settings');
        $row[$key.$key] = [
            '#markup' => '<div class="container-inline mo-table_app1"><strong>'. $key . '</strong>',
        ];

        if ($key == 'Callback URL: '){
            $row[$value] = [
                '#type' => 'item',
                '#plain_text' => $value,
            ];
        }else if ($key == 'Enable OAuth SSO login: '){
            $row[$value] = [
                '#type' => 'checkbox',
                '#default_value' => $config->get($value),
                '#title' => t('<i>( Note: Check this option to show SSO link on the Login page) </i>'),
            ];
        }else if ($key == 'Client Credentials in Header: '){
            $row[$value] = [
                '#type' => 'checkbox',
                '#default_value' => $config->get($value),
                '#title' => t('<i>( Note: Check this option if you want to send Client ID and Secret in Headers) </i>'),
            ];
        }else if ($key == 'Client Credentials in Body: '){
            $row[$value] = [
                '#type' => 'checkbox',
                '#default_value' => $config->get($value),
                '#title' => t('<i>( Note: Check this option if you want to send Client ID and Secret in Body) </i>'),
            ];
        }else if ($key == 'Enable PKCE Flow: '){
            $row[$value] = [
                '#type' => 'checkbox',
                '#disabled' => true,
                '#title' => t(' &nbsp;&nbsp;&nbsp;<a href="' . $base_url . '/admin/config/people/oauth_login_oauth2/licensing"> [Available in the Enterprise version]</a>'),
            ];
        }else if ($key == 'Authorization Endpoint: ' || $key == 'Access Token Endpoint: ' || $key == 'Userinfo Endpoint: '){
            $row[$value] = [
                '#type' => 'url',
                '#required' => TRUE,
                '#default_value' => !empty($config->get($value)) ? $config->get($value) : $endpoints[$key],
                '#suffix' => '</div>'
            ];
        }else if ($key == 'Client credentials in Header: '){
            $row[$value] = [
                '#type' => 'checkbox',
                '#default_value' => $config->get($value),
                '#title' => t('Enable to send credentials in Header'),
            ];
        }else if ($key == 'Client credentials in Body: '){
            $row[$value] = [
                '#type' => 'checkbox',
                '#default_value' => $config->get($value),
                '#title' => t('Enable to send credentials in Body'),
            ];
        }
        else {
            $row[$value] = [
                '#type' => 'textfield',
                '#required' => TRUE,
                '#default_value' =>$config->get($value),
                '#maxlength' => 1048,
                '#suffix' => '</div>'
            ];
        }

        return $row;
    }

    public function miniorange_oauth_endpoints(array &$form, FormStateInterface $form_state){
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');
        $form_values = $form_state->getValues();
        $display_link = trim($form_values['miniorange_oauth_client_display_name']);
        $client_id = trim($form_values['miniorange_oauth_client_id']);
        $client_secret = trim($form_values['miniorange_oauth_client_secret']);

        $configFactory->set('miniorange_auth_client_display_link', $display_link)->save();
        $configFactory->set('miniorange_auth_client_client_id', $client_id)->save();
        $configFactory->set('miniorange_auth_client_client_secret', $client_secret)->save();
        $configFactory->set('miniorange_oauth_login_config_status','endpoints')->save();
    }

    public function miniorange_oauth_final_summary(array &$form, FormStateInterface $form_state){
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');
        $form_values = $form_state->getValues();

        $scope = trim($form_values['miniorange_oauth_scope']);
        $authorize_endpoint = trim($form_values['miniorange_oauth_authorize_endpoint']);
        $access_token_endpoint = trim($form_values['miniorange_oauth_access_token_endpoint']);
        $userinfo_endpoint = trim($form_values['miniorange_oauth_userinfo_endpoint']);

        $configFactory->set('miniorange_auth_client_scope', $scope)->save();
        $configFactory->set('miniorange_auth_client_authorize_endpoint', $authorize_endpoint)->save();
        $configFactory->set('miniorange_auth_client_access_token_ep', $access_token_endpoint)->save();
        $configFactory->set('miniorange_auth_client_user_info_ep', $userinfo_endpoint)->save();
//        $configFactory->set('miniorange_oauth_login_config_status','final_summary')->save();
        $configFactory->set('miniorange_oauth_login_config_status','final')->save();

        \Drupal::messenger()->addstatus(t('Configurations saved successfully. Please click on the <b>Perform Test Configuration</b> button to test the connection.'));
    }

    public function miniorange_oauth_complete_configuration(array &$form, FormStateInterface $form_state){
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');
        $form_values = ($form_state->getValues())['miniorange_oauth_client_summary'];

        $data = self::mo_data_configurations();

        foreach ($data as $key => $value){
            $configFactory->set($value, $form_values[$key][$value])->save();
        }

        \Drupal::messenger()->addstatus(t('Configurations saved successfully. Please click on the <b>Perform Test Configuration</b> button to test the connection.'));

        $path = Url::fromRoute('oauth_login_oauth2.config_clc')->toString();
        $response = new RedirectResponse($path);
        $response->send();
    }

    public static function setup_call(array &$form, FormStateInterface $form_state){
        Utilities::schedule_a_call($form, $form_state);
    }

    public static function discovery_endpoint($discovery_url){

        $endpoints = [];
        $response = Utilities::callService($discovery_url, NULL, array(), 'GET');

        $content = json_decode($response,true);
        $endpoints['authorization_endpoint'] = isset($content['authorization_endpoint']) && !empty($content['authorization_endpoint']) ? $content['authorization_endpoint'] : '';
        $endpoints['token_endpoint'] = isset($content['token_endpoint']) && !empty($content['token_endpoint']) ? $content['token_endpoint'] : '';
        $endpoints['userinfo_endpoint'] = isset($content['userinfo_endpoint']) && !empty($content['userinfo_endpoint']) ? $content['userinfo_endpoint'] : '';
        $endpoints['scopes'] = isset($content['scopes_supported']) && !empty($content['scopes_supported']) ? implode(' ',$content['scopes_supported']) : '';

        return $endpoints;
    }

    public function mo_data_configurations(){
        global $base_url;
        $baseUrlValue = Utilities::getOAuthBaseURL($base_url);
        $config = \Drupal::config('oauth_login_oauth2.settings');
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');
        if(!empty($config->get('miniorange_auth_client_callback_uri')))
        {
            $callbackUrl = $config->get('miniorange_auth_client_callback_uri');
        }
        else{
            $callbackUrl = $baseUrlValue."/mo_login";
            $configFactory->set('miniorange_auth_client_callback_uri',$callbackUrl)->save();
        }

        $data = [
            'Display link on the login page: ' => 'miniorange_auth_client_display_link',
            'Callback URL: ' => $callbackUrl,
            'Client ID: ' => 'miniorange_auth_client_client_id',
            'Client Secret: ' => 'miniorange_auth_client_client_secret',
            'Scope: ' => 'miniorange_auth_client_scope',
            'Authorization Endpoint: ' => 'miniorange_auth_client_authorize_endpoint',
            'Access Token Endpoint: ' => 'miniorange_auth_client_access_token_ep',
            'Userinfo Endpoint: ' => 'miniorange_auth_client_user_info_ep',
            'Enable OAuth SSO login: ' => 'miniorange_oauth_enable_login_with_oauth',
            'Client credentials in Header: ' => 'miniorange_oauth_send_with_header_oauth',
            'Client credentials in Body: ' => 'miniorange_oauth_send_with_body_oauth',
            'Enable PKCE Flow: ' => '',
        ];
        return $data;
    }

    public function guide_links(&$form){
        $name = \Drupal::request()->query->get('app_name') !== null ? \Drupal::request()->query->get('app_name') : \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_login_config_application');
        $guides = appData::app_guides($name);

        $appname = strtoupper(substr($name,0,1)).substr($name,1);

        if($name == 'oauth2'){
            $appname = 'Custom';
        }
;
        $form['markup_setup_guide_start'] = array(
            '#markup' => '<div class="container-inline">'
        );

        $guides['video'] = isset($guides['video']) && !empty($guides['video']) ? $guides['video'] : 'https://www.youtube.com/watch?v=ipjiRfzpH8Y';
        $guides['setup'] = isset($guides['setup']) && !empty($guides['setup']) ? $guides['setup'] : 'https://www.drupal.org/docs/contributed-modules/drupal-oauth-oidc-login/drupal-oauth-login-setup-guide';

        $form['markup_video_guide'] = array(
            '#markup' => '<a class="button mo-guides-floating" target="_blank" href="'.$guides['video'].'">&#128366;&nbsp;'.$appname.' Setup Video</a>'
        );

        $form['markup_setup_guide'] = array(
            '#markup' => '<a class="button mo-guides-floating" target="_blank" href="'.$guides['setup'].'">&#128366;&nbsp;'.$appname.' Setup Guide</a>'
        );

        $form['markup_setup_guides_ends'] = array(
            '#markup' => '</div>'
        );
    }

}
