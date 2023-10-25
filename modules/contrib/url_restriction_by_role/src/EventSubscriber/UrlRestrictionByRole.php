<?php

namespace Drupal\url_restriction_by_role\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Event Subscriber for Url Restrictions.
 */
class UrlRestrictionByRole implements EventSubscriberInterface {

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
 * Use Custom Error Message.
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
   * Request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * User object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Language object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(AccountProxyInterface $currentUser,
                              ConfigFactoryInterface $config,
                              RequestStack $request,
                              LanguageManagerInterface $language_manager) {
    $this->currentUser = $currentUser;
    $this->config = $config;
    $this->request = $request;
    $this->languageManager = $language_manager;
  }

  /**
   * Restrict access to URLs defined.
   */
  public function onRequest(RequestEvent $event) {
    $error_message = $this->config->get(static::SETTINGS)->get(static::ERROR_MESSAGE);
    $use_custom_error_message = $this->config->get(static::SETTINGS)->get(static::USE_CUSTOM_ERROR_MESSAGE);
    $roles = $this->currentUser->getRoles();
    $basic_site_information_page_403 = $this->config->get('system.site')->get('page.403');

    $request_uri = $this->request->getCurrentRequest()->server->get('REQUEST_URI');
    $request_uri = $this->getCurrentUrl($request_uri);

    $options = $this->config->get(static::SETTINGS)->get(static::FORM_TABLE . '.' . $request_uri);

    if (!empty($options) && $options[static::FORM_COLUMN_ENABLED]) {
      $role = $options[static::FORM_COLUMN_ROLE];
      if ((is_string($role) && in_array($role, $roles)) || (is_array($role) && count(array_intersect($role, $roles)) == 0)) {
        if (!$use_custom_error_message) {
          if (empty($basic_site_information_page_403)) {
            $response = new RedirectResponse('/system/403');
            $event->setResponse($response);
          }
          else {
            $response = new RedirectResponse($basic_site_information_page_403);
            $event->setResponse($response);
          }
        }
        else {
          $response = new Response(empty($error_message) ? 'You do not have access to this page' : $error_message, 403);
          $event->setResponse($response);
        }

      }
    }
  }

  /**
   * Exclude query string and language code.
   */
  public function getCurrentUrl($url) {
    $languagecode = $this->languageManager->getCurrentLanguage()->getId();
    $url = preg_replace('/\?.*/', '', $url);
    $path = explode("/", $url);
    if (isset($path[1]) && $path[1] == $languagecode) {
      array_splice($path, 1, 1);
      $url = implode("/", $path);
    }
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest'];
    return $events;
  }

}
