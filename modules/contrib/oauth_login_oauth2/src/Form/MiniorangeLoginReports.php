<?php
namespace Drupal\oauth_login_oauth2\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\oauth_login_oauth2\Utilities;

class MiniorangeLoginReports extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'miniorange_reports';
    }
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        global $base_url;
        $url_path = $base_url . '/' . \Drupal::service('extension.list.module')->getPath('oauth_login_oauth2'). '/includes/Providers';

        $form['markup_library'] = array(
            '#attached' => array('library' => array(
                "oauth_login_oauth2/oauth_login_oauth2.admin",
                "oauth_login_oauth2/oauth_login_oauth2.style_settings",
            )),
        );

        $form['header_top_style_1'] = ['#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">'];

        $form['markup_login_reports'] = array(
            '#type' => 'fieldset',
            '#title' => t('LOGIN REPORTS <a href="licensing"><img class="mo_oauth_pro_icon1" src="' . $url_path . '/pro.png" alt="Enterprise"><span class="mo_pro_tooltip">Available in the Enterprise version</span></a>'),
        );

        $form['markup_login_reports']['miniorange_oauth_client_report'] = array(
            '#type' => 'table',
            '#header' => array('Username','Status','Application','Date and Time','Email','IP Address','Navigation URL'),
            '#empty' => t('This feature is available in the <a href="' . $base_url . '/admin/config/people/oauth_login_oauth2/licensing">Enterprise</a> version.'),
            '#prefix' => '<hr>',
            '#suffix' => '</div>',
        );

        Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {}
}
