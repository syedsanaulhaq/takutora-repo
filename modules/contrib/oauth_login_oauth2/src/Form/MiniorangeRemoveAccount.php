<?php

namespace Drupal\oauth_login_oauth2\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;


class MiniorangeRemoveAccount extends FormBase{

    public function getFormId(){
        return 'miniorange_customer_remove_account';
    }

    public function buildForm(array $form, FormStateInterface $form_state){
        
        $form['#prefix'] = '<div id="modal_example_form">';
        $form['#suffix'] = '</div>';
        $form['status_messages'] = [
            '#type' => 'status_messages',
            '#weight' => -10,
        ];

        $form['miniorange_oauth_content'] = array(
            '#markup' => t('<strong>Are you sure you want to remove account? The configurations saved will not be lost.</strong>')
        );

        $form['actions'] = array('#type' => 'actions');
        $form['actions']['send'] = [
            '#type' => 'submit',
            '#value' => $this->t('Confirm'),
            '#attributes' => [
                'class' => [
                    'use-ajax',
                ],
            ],
            '#ajax' => [
                'callback' => [$this, 'RemoveAccountForm'],
                'event' => 'click',
            ],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
        return $form;
    }

    public function RemoveAccountForm(array $form, FormStateInterface $form_state){
        $configFactory = \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings');
        $response = new AjaxResponse();

        $configFactory->clear('miniorange_oauth_client_customer_admin_email')
                      ->clear('miniorange_oauth_client_customer_admin_phone')
                      ->clear('miniorange_oauth_client_customer_id')
                      ->clear('miniorange_oauth_client_customer_admin_token')
                      ->clear('miniorange_oauth_client_customer_api_key')
                      ->set('miniorange_oauth_client_status', '')
                      ->save();
        
        \Drupal::messenger()->addMessage(t('Your Account Has Been Removed Successfully!'), 'status');
        $response->addCommand(new RedirectCommand(Url::fromRoute('oauth_login_oauth2.customer_setup')->toString()));

        return $response;
    }

    public function submitForm(array &$form, FormStateInterface $form_state){ }

    public function validateForm(array &$form, FormStateInterface $form_state) { }
}