<?php

namespace Drupal\charts_highcharts_maps\Plugin\views\style;

use Drupal\charts\Plugin\views\style\ChartsPluginStyleChart;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\core\form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Style plugin to render view as a chart.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "chart_highmap",
 *   title = @Translation("HighMaps"),
 *   help = @Translation("Render a map of your data."),
 *   theme = "views_view_charts_highcharts_maps",
 *   display_types = { "normal" }
 * )
 */
class HighMapsPluginStyleChart extends ChartsPluginStyleChart {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $instance->setEntityFieldManager($container->get('entity_field.manager'));
    $instance->setEntityTypeManager($container->get('entity_type.manager'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['topology']['default'] = '';
    $options['map_data_source_settings']['default'] = [
      'vid' => '',
      'tid' => 0,
      'json_field_name' => '',
    ];
    $options['subtitle']['default'] = $this->t("Source map: <a href='https://code.highcharts.com/mapdata/custom/world.geo.json'>World Map</a>");
    $options['series_join_by_property_name']['default'] = 'hasc';
    $options['series_keys_mapping_options']['default'] = ['hasc', 'value'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['chart_settings']['#after_build'][] = [static::class, 'chartsSettingsAfterBuild'];

    $map_data_source_settings_wrapper_id = Html::cleanCssIdentifier($this->view->id() . '--' . $this->view->current_display . '--map-data-source-settings');
    $form['map_data_source_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Map data settings'),
      '#collapsible' => TRUE,
      '#attributes' => [
        'id' => $map_data_source_settings_wrapper_id,
      ],
      '#open' => TRUE,
      '#required' => TRUE,
      '#tree' => TRUE,
    ];

    $vid = $this->extractMapDataSourceVid($form_state);
    $form['map_data_source_settings']['vid'] = [
      '#type' => 'select',
      '#title' => $this->t('Taxonomy vocabulary'),
      '#description' => $this->t('The vocabulary of the taxonomy term that have the json field for data mapping'),
      '#options' => ['' => $this->t('- Select -')] + $this->vocabularyOptions(),
      '#default_value' => $vid,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [get_called_class(), 'refreshTermJsonFieldSelection'],
        'wrapper' => $map_data_source_settings_wrapper_id,
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Refreshing...'),
        ],
      ],
    ];

    if ($vid) {
      $tid = $this->options['map_data_source_settings']['tid'] ?? 0;
      $form['map_data_source_settings']['tid'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Taxonomy term'),
        '#description' => $this->t('The taxonomy term storing the json data.'),
        '#target_type' => 'taxonomy_term',
        '#default_value' => $tid ? Term::load($tid) : NULL,
        '#required' => TRUE,
        '#selection_handler' => 'default',
        '#selection_settings' => [
          'target_bundles' => [$vid],
        ],
      ];
      $form['map_data_source_settings']['json_field_name'] = [
        '#type' => 'select',
        '#title' => $this->t('JSON field name'),
        '#options' => ['' => $this->t('- Select -')] + $this->jsonFieldOptions($vid),
        '#default_value' => $this->options['map_data_source_settings']['json_field_name'] ?? '',
        '#required' => TRUE,
      ];
    }

    $form['subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Map Subtitle'),
      '#size' => 70,
      '#maxlength' => 250,
      '#description' => $this->t('Leave blank for no subtitle.'),
      '#default_value' => $this->options['subtitle'] ?? '',
    ];

    $description = $this->t('What property to join the "mapData" to the value data. For example, if joinBy is "code", the mapData items with a specific code is merged into the data with the same code. For maps loaded from GeoJSON, the keys may be held in each point\'s properties object.
The joinBy option can also be an array of two values, where the first points to a key in the mapData, and the second points to another key in the data. See <a href="@link">documentation</a> for more information.<br><b>If you have two values separate them with a comma.</b>', [
      '@link' => 'https://api.highcharts.com/highmaps/series.map.joinBy',
    ]);
    $form['series_join_by_property_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Series JoinBy'),
      '#description' => $description,
      '#default_value' => $this->options['series_join_by_property_name'],
    ];

    $form['series_keys_mapping_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Series keys'),
      '#description' => $this->t('An array specifying which option maps to which key in the data point array. This makes it convenient to work with unstructured data arrays from different sources. See <a href="@link">documentation</a> for more information.', [
        '@link' => 'https://api.highcharts.com/highmaps/series.map.keys',
      ]),
      '#collapsible' => TRUE,
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['series_keys_mapping_options'][0] = [
      '#type' => 'textfield',
      '#title' => $this->t('First key'),
      '#default_value' => $this->options['series_keys_mapping_options'][0],
    ];
    $form['series_keys_mapping_options'][1] = [
      '#type' => 'textfield',
      '#title' => $this->t('Second key'),
      '#default_value' => $this->options['series_keys_mapping_options'][1],
    ];

    $form_state->set('default_options', $this->options);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $chart = parent::render();

    if (empty($chart['#legend_title']) && !empty($this->options['chart_settings']['yaxis']['title'])) {
      $chart['#legend_title'] = $this->options['chart_settings']['yaxis']['title'];
    }
    $chart['#map_data_source_settings'] = $this->options['map_data_source_settings'];
    $chart['#subtitle'] = $this->options['subtitle'];
    $chart['#series_data_settings'] = [
      'join_by_property_name' => $this->options['series_join_by_property_name'],
      'keys_mapping_options' => $this->options['series_keys_mapping_options'],
      'tooltip' => [
        'value_prefix' => $this->options['chart_settings']['yaxis']['prefix'] ?? '',
        'value_suffix' => $this->options['chart_settings']['yaxis']['suffix'] ?? '',
        'value_decimals' => $this->options['chart_settings']['yaxis']['decimal_count'] ?? NULL,
      ],
    ];

    return $chart;
  }

  /**
   * Ajax callback to refresh the term and JSON field selection.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The map data source settings.
   */
  public static function refreshTermJsonFieldSelection(array $form, FormStateInterface $form_state): array {
    $triggering_element = $form_state->getTriggeringElement();
    $array_parents = array_slice($triggering_element['#array_parents'], 0, -1);
    return NestedArray::getValue($form, $array_parents);
  }

  public static function chartsSettingsAfterBuild(array $element, FormStateInterface $form_state) {
    // Hiding the xAxis settings because it's not needed for the highmap.
    $element['xaxis']['#access'] = FALSE;

    // Renaming the yAxis title to set it to map display settings.
    $element['yaxis']['#title'] = new TranslatableMarkup('Highmap display settings');

    $element['yaxis']['title']['#title'] = new TranslatableMarkup('Legend title');

    $element['yaxis']['prefix']['#title'] = new TranslatableMarkup('Value prefix');
    $element['yaxis']['prefix']['#description'] = new TranslatableMarkup('A string to prepend to each series\' y value.');
    $element['yaxis']['suffix']['#title'] = new TranslatableMarkup('Value suffix');
    $element['yaxis']['suffix']['#description'] = new TranslatableMarkup('A string to append to each series\' y value.');

    $element['yaxis']['decimal_count']['#title'] = new TranslatableMarkup('Value decimals');
    $element['yaxis']['decimal_count']['#description'] = new TranslatableMarkup('How many decimals to show in each series\' y value.');

    // Hiding unused elements.
    $element['yaxis']['min_max_label']['#access'] = FALSE;
    $element['yaxis']['min']['#access'] = FALSE;
    $element['yaxis']['max']['#access'] = FALSE;
    $element['yaxis']['labels_rotation']['#access'] = FALSE;

    return $element;
  }

  /**
   * Extract the map data source Vocabulary ID.
   *
   * @param \Drupal\core\form\FormStateInterface $form_state
   *   The form_state.
   *
   * @return mixed
   *   It will return mixed values.
   */
  protected function extractMapDataSourceVid(FormStateInterface $form_state) {
    $vid = $form_state->getValue([
      'style_options',
      'map_data_source_settings',
      'vid',
    ]);
    return $vid ? (string) $vid : ($this->options['map_data_source_settings']['vid'] ?? '');
  }

  /**
   * Get the vocabulary options.
   *
   * @return array
   *   It will return an array of options.
   */
  protected function vocabularyOptions(): array {
    /** @var \Drupal\taxonomy\VocabularyStorage $vocabulary_storage */
    $vocabulary_storage = $this->entityTypeManager->getStorage('taxonomy_vocabulary');
    $options = [];
    foreach($vocabulary_storage->loadMultiple() as $vid => $vocabulary) {
      $options[$vid] = $vocabulary->label();
    }
    return $options;
  }

  /**
   * Get JSON field options.
   *
   * @param string $bundle
   *   The bundle.
   *
   * @return array
   *   It will return an array of options.
   */
  protected function jsonFieldOptions(string $bundle): array {
    $options = [];
    $entity_type_id = 'taxonomy_term';
    $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type_id);
    $excluded_fields = [
      $entity_type_definition->getKey('id'),
      $entity_type_definition->getKey('uuid'),
      $entity_type_definition->getKey('label'),
      $entity_type_definition->getKey('langcode'),
      $entity_type_definition->getKey('bundle'),
      $entity_type_definition->getKey('published'),
      'created',
      'changed',
      'default_langcode',
      'metatag',
      'parent',
      'path',
      'weight',
      'description',
    ];
    foreach ($fields as $field_name => $field) {
      if (in_array($field_name, $excluded_fields, TRUE) || str_starts_with($field_name, 'revision_') || str_starts_with($field_name, 'content_translation_')) {
        continue;
      }

      $options[$field_name] = $this->t('@label (Property: @name - Type: @type)', [
        '@label' => $field->getLabel(),
        '@name' => $field_name,
        '@type' => $field->getType(),
      ]);
    }
    return $options;
  }

  /**
   * Sets the entity field manager for this handler.
   *
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The new entity field manager.
   *
   * @return $this
   */
  protected function setEntityFieldManager(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
    return $this;
  }

  /**
   * Sets the entity type manager for this handler.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @return $this
   */
  protected function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    return $this;
  }

}
