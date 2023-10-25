<?php

namespace Drupal\url_restriction_by_role\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Url settings.
 */
class UrlRestrictionByRoleSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'url_restriction_by_role.settings';

  /**
   * Error Message.
   *
   * @var string
   */
  const ERROR_MESSAGE = 'error_message';

  /**
   * Use Custom error message.
   *
   * @var string
   */
  const USE_CUSTOM_ERROR_MESSAGE = 'use_custom_error_message';

  /**
   * Form table name.
   *
   * @var string
   */
  const FORM_TABLE = 'urls';

  /**
   * Column url.
   *
   * @var string
   */
  const FORM_COLUMN_URL = 'url';

  /**
   * Column enabled.
   *
   * @var string
   */
  const FORM_COLUMN_ENABLED = 'enabled';

  /**
   * Column role.
   *
   * @var string
   */
  const FORM_COLUMN_ROLE = 'role';


  /**
   * The role storage used.
   *
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $roleStorage;

  /**
   * Constructs a new UrlRestrictionByRoleSettingsForm.
   *
   * @param \Drupal\user\RoleStorageInterface $role_storage
   *   The role storage.
   */
  public function __construct(RoleStorageInterface $role_storage) {
    $this->roleStorage = $role_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('user_role')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'url_restriction_by_role_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form[self::ERROR_MESSAGE] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message'),
      '#description' => $this->t('The error message to be displayed when the user does not have access to the URL.'),
      '#default_value' => $config->get('error_message') ?: '',
    ];

    $form[self::USE_CUSTOM_ERROR_MESSAGE] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use custom error message?'),
      '#description' => $this->t('If checked, in the event of the user not having access to the page,a blank page with the previous error message will be shown. Otherwise, the default 403 error page will be used.'),
      '#default_value' => $config->get('use_custom_error_message') ?: '',
    ];

    $form[self::FORM_TABLE] = [
      '#type' => 'table',
      '#header' => [
        self::FORM_COLUMN_URL => $this->t('URL'),
        self::FORM_COLUMN_ENABLED => $this->t('Enabled'),
        self::FORM_COLUMN_ROLE => $this->t('Role'),
      ],
    ];

    $urls = $config->get('urls');
    $roles_obj = $this->roleStorage->loadMultiple();
    $roles = [];
    foreach ($roles_obj as $role_obj) {
      $roles[$role_obj->id()] = $role_obj->label();
    }

    if (!is_null($urls)) {
      foreach ($urls as $url => $options) {
        $form[self::FORM_TABLE][$url][self::FORM_COLUMN_URL] = [
          '#type' => 'textfield',
          '#size' => 30,
          '#maxlength' => 255,
          '#default_value' => $url ?: '',
        ];
        $form[self::FORM_TABLE][$url][self::FORM_COLUMN_ENABLED] = [
          '#type' => 'checkbox',
          '#default_value' => $urls[$url][self::FORM_COLUMN_ENABLED] ?: FALSE,
        ];
        $form[self::FORM_TABLE][$url][self::FORM_COLUMN_ROLE] = [
          '#type' => 'select',
          '#multiple' => TRUE,
          '#options' => $roles,
          '#default_value' => $urls[$url][self::FORM_COLUMN_ROLE] ?: 'authenticated',
        ];
      }
    }

    $form[self::FORM_TABLE][0][self::FORM_COLUMN_URL] = [
      '#type' => 'textfield',
      '#size' => 30,
      '#maxlength' => 255,
      '#default_value' => '',
      '#description' => $this->t('Example : /node/add'),
    ];
    $form[self::FORM_TABLE][0][self::FORM_COLUMN_ENABLED] = [
      '#type' => 'checkbox',
      '#default_value' => FALSE,
    ];
    $form[self::FORM_TABLE][0][self::FORM_COLUMN_ROLE] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#options' => $roles,
      '#default_value' => 'authenticated',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $table = $form_state->getValue(static::FORM_TABLE);
    $aux_table = [];
    foreach ($table as $options) {
      if ($options[self::FORM_COLUMN_URL]) {
        $aux_table[$options[self::FORM_COLUMN_URL]][self::FORM_COLUMN_ENABLED] = $options[self::FORM_COLUMN_ENABLED];
        $aux_table[$options[self::FORM_COLUMN_URL]][self::FORM_COLUMN_ROLE] = $options[self::FORM_COLUMN_ROLE];
      }
    }

    $this->configFactory->getEditable(static::SETTINGS)
      ->set(static::FORM_TABLE, $aux_table)
      ->save();
    $this->configFactory->getEditable(static::SETTINGS)->set(static::ERROR_MESSAGE, $form_state->getValue(static::ERROR_MESSAGE))->save();
    $this->configFactory->getEditable(static::SETTINGS)->set(static::USE_CUSTOM_ERROR_MESSAGE, $form_state->getValue(static::USE_CUSTOM_ERROR_MESSAGE))->save();
  }

}
