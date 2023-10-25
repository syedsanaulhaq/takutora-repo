<?php

namespace Drupal\oauth_login_oauth2\Form;

use Drupal;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\oauth_login_oauth2\Utilities;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\oauth_login_oauth2\MiniorangeOAuthClientSupport;

class MoOAuthCustomerRequest extends FormBase
{
    private ImmutableConfig $config;
    protected $messenger;

    public function __construct()
    {
        $this->config = Drupal::config('oauth_login_oauth2.settings');
        $this->messenger = Drupal::messenger();
    }

    /**
     * @inheritDoc
     */
    public function getFormId()
    {
        return 'mo_client_request_customer_support';
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['#prefix'] = '<div id="modal_example_form">';
        $form['#suffix'] = '</div>';
        $form['status_messages'] = [
            '#type' => 'status_messages',
            '#weight' => -10,
        ];

        $user_email = $this->config->get('miniorange_oauth_client_customer_admin_email');
        $form['mo_oauth_login_customer_support_email_address'] = [
            '#type' => 'email',
            '#title' => t('Email'),
            '#default_value' => $user_email,
            '#required' => true,
            '#attributes' => array('placeholder' => t('Enter valid email'), 'style' => 'width:99%;margin-bottom:1%;'),
        ];

        $form['mo_oauth_login_customer_support_method'] = [
            '#type' => 'select',
            '#title' => t('What are you looking for'),
            '#attributes' => array('style' => 'width:99%;height:30px;margin-bottom:1%;'),
            '#options' => [
                'I need Technical Support' => t('I need Technical Support'),
                'I want to Schedule a Setup Call/Demo' => t('I want to Schedule a Setup Call/Demo'),
                'I have Sales enquiry' => t('I have Sales enquiry'),
                'I have a custom requirement' => t('I have a custom requirement'),
                'My reason is not listed here' => t('My reason is not listed here'),
            ],
        ];

        $timezone = array();

        foreach (Utilities::$zones as $key => $value){
          $timezone[$value] = $key;
        }


        $form['date_and_time'] = array(
            '#type' => 'container',
            '#states' => array(
                'visible' => array(
                    ':input[name = "mo_oauth_login_customer_support_method"]' => array('value' => 'I want to Schedule a Setup Call/Demo')),
                )
        );

        $form['date_and_time']['miniorange_oauth_client_timezone'] = array(
            '#type' => 'select',
            '#title' => t('Select Timezone'),
            '#options' => $timezone,
            '#default_value' => 'Etc/GMT',
        );

        $form['date_and_time']['miniorange_oauth_client_meeting_time'] = array (
            '#type' => 'datetime',
            '#title' => 'Date and Time',
            '#format' => '',
            '#default_value' => DrupalDateTime::createFromTimestamp(time()),
        );

        $form['mo_oauth_login_customer_support_query'] = array(
            '#type' => 'textarea',
            '#required' => true,
            '#title' => t('How can we help you?'),
            '#attributes' => array('placeholder' => t('Describe your query here!'), 'style' => 'width:99%'),
        );

        $form['actions'] = array('#type' => 'actions');
        $form['actions']['send'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
            '#attributes' => [
                'class' => [
                    'use-ajax',
                    'button--primary'
                ],
            ],
            '#ajax' => [
                'callback' => [$this, 'submitModalFormAjax'],
                'event' => 'click',
            ],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;
    }

    public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
        $form_values = $form_state->getValues();
        $response = new AjaxResponse();
        // If there are any form errors, AJAX replace the form.
        if ( $form_state->hasAnyErrors() ) {
            $response->addCommand(new ReplaceCommand('#modal_support_form', $form));
        } else {
            $email = $form_values['mo_oauth_login_customer_support_email_address'];
            $support_for = $form_values['mo_oauth_login_customer_support_method'];
            $query = $form_values['mo_oauth_login_customer_support_query'];
            $query_type = 'Contact Support';

            if($support_for == 'I want to Schedule a Setup Call/Demo'){
                $timezone = $form_values['miniorange_oauth_client_timezone'];
                $mo_date = $form['date_and_time']['miniorange_oauth_client_meeting_time']['#value']['date'];
                $mo_time = $form['date_and_time']['miniorange_oauth_client_meeting_time']['#value']['time'];
                $query_type = 'Call Request';
            }
            
            $timezone = !empty($timezone) ? $timezone : null;
            $mo_date  = !empty($mo_date) ? $mo_date : null;
            $mo_time = !empty($mo_time) ? $mo_time : null;

            $support = new MiniorangeOAuthClientSupport($email,$support_for,$query,$query_type,$timezone,$mo_date,$mo_time);
                                                        
            $support_response = json_decode($support->sendSupportQuery(), true);
          
            if(isset($support_response['status']) && $support_response['status'] == "SUCCESS"){
                $this->messenger->addStatus(t('Support query successfully sent. We will get back to you shortly.'));
            }else{
                $this->messenger->addError(t('Error sending Support query. Please reach out to drupalsupport@xecurify.com'));
            }

            $response->addCommand(new RedirectCommand(Url::fromRoute('oauth_login_oauth2.config_clc')->toString()));
        }
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // TODO: Implement submitForm() method.
    }
}