<?php

namespace Drupal\camunda\Plugin\ECA\Modeller;

use Drupal\eca_modeller_bpmn\ModellerBpmnBase;
use Drupal\eca\Plugin\ECA\Modeller\ModellerInterface;

/**
 * Plugin implementation of the ECA Modeller.
 *
 * @EcaModeller(
 *   id = "camunda",
 * )
 */
class Camunda extends ModellerBpmnBase {

  /**
   * {@inheritdoc}
   */
  protected function xmlNsPrefix(): string {
    return 'bpmn:';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \JsonException
   */
  public function exportTemplates(): ModellerInterface {
    file_put_contents('private://camunda.template.json', json_encode($this->getTemplates(), JSON_THROW_ON_ERROR));
    return $this;
  }

}
