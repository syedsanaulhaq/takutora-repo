<?php

namespace Drupal\charts_highcharts_maps\Plugin\chart\Library;

use Drupal\charts\Element\Chart as ChartElement;
use Drupal\charts\Plugin\chart\Library\ChartBase;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;

/**
 * Defines a concrete class for a Highmap.
 *
 * @Chart(
 *   id = "highmap",
 *   name = @Translation("Highmap")
 * )
 */
class Highmap extends ChartBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configurations = [
      'legend' => [
        'layout' => NULL,
        'background_color' => '',
        'border_width' => 0,
        'shadow' => FALSE,
        'item_style' => [
          'color' => '',
          'overflow' => '',
        ],
      ],
      'exporting_library' => TRUE,
    ] + parent::defaultConfiguration();

    return $configurations;
  }

  /**
   * Build configurations.
   *
   * @param array $form
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   Return the from.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['intro_text'] = [
      '#markup' => $this->t('This is a placeholder for Highcharts Maps specific library options. If you would like to help build this out, please work from <a href="@issue_link">this issue</a>.', [
        '@issue_link' => Url::fromUri('https://www.drupal.org/project/charts/issues/3046981')->toString(),
      ]),
    ];

    $legend_configuration = $this->configuration['legend'] ?? [];
    $form['legend'] = [
      '#title' => $this->t('Legend Settings'),
      '#type' => 'fieldset',
    ];

    $form['legend']['layout'] = [
      '#title' => $this->t('Legend layout'),
      '#type' => 'select',
      '#options' => [
        'vertical' => $this->t('Vertical'),
        'horizontal' => $this->t('Horizontal'),
      ],
      '#default_value' => $legend_configuration['layout'] ?? NULL,
    ];
    $form['legend']['background_color'] = [
      '#title' => $this->t('Legend background color'),
      '#type' => 'textfield',
      '#size' => 10,
      '#maxlength' => 7,
      '#attributes' => ['placeholder' => $this->t('transparent')],
      '#description' => $this->t('Leave blank for a transparent background...'),
      '#default_value' => $legend_configuration['background_color'] ?? '',
    ];
    $form['legend']['border_width'] = [
      '#title' => $this->t('Legend border width'),
      '#type' => 'select',
      '#options' => [
        0 => $this->t('None'),
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
      ],
      '#default_value' => $legend_configuration['border_width'] ?? 0,
    ];
    $form['legend']['shadow'] = [
      '#title' => $this->t('Enable legend shadow'),
      '#type' => 'checkbox',
      '#default_value' => !empty($legend_configuration['shadow']),
    ];

    $form['legend']['item_style'] = [
      '#title' => $this->t('Item Style'),
      '#type' => 'fieldset',
    ];
    $form['legend']['item_style']['color'] = [
      '#title' => $this->t('Item style color'),
      '#type' => 'textfield',
      '#size' => 10,
      '#maxlength' => 7,
      '#attributes' => ['placeholder' => '#333333'],
      '#description' => $this->t('Leave blank for a dark gray font.'),
      '#default_value' => $legend_configuration['item_style']['color'] ?? '',
    ];
    $form['legend']['item_style']['overflow'] = [
      '#title' => $this->t('Text overflow'),
      '#type' => 'select',
      '#options' => [
        '' => $this->t('No'),
        'ellipsis' => $this->t('Ellipsis'),
      ],
      '#default_value' => $legend_configuration['item_style']['overflow'] ?? '',
    ];

    $form['exporting_library'] = [
      '#title' => $this->t('Enable Highstock\' "Exporting" library'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->configuration['exporting_library']),
    ];

    return $form;
  }

  /**
   * Build configurations.
   *
   * @param array $form
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['legend'] = $values['legend'];
      $this->configuration['exporting_library'] = $values['exporting_library'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(array $element) {
    // Populate chart settings.
    $chart_definition = [];

    $chart_definition = $this->populateOptions($element, $chart_definition);
    $chart_definition = $this->populateAxes($element, $chart_definition);
    $chart_definition = $this->populateData($element, $chart_definition);

    // Remove machine names from series. Highstock series must be an array.
    $series = array_values($chart_definition['series']);
    unset($chart_definition['series']);

    // Trim out empty options (excluding "series" for efficiency).
    ChartElement::trimArray($chart_definition);

    // Put back the data.
    $chart_definition['series'] = $series;

    if (!isset($element['#id'])) {
      $element['#id'] = Html::getUniqueId('highmap-render');
    }

    $element['#attached']['library'][] = 'charts_highcharts_maps/highmap';
    $element['#attached']['library'][] = 'charts_highcharts_maps/charts_highcharts_maps';

    $element['#attributes']['class'][] = 'charts-highmap';
    $element['#chart_definition'] = $chart_definition;

    return $element;
  }

  /**
   * Populate options.
   *
   * @param array $element
   *   The element.
   * @param array $chart_definition
   *   The chart definition.
   *
   * @return array
   *   Return the chart definition.
   */
  private function populateOptions(array $element, array $chart_definition) {
    $chart_type = $element['#chart_type'];
    $chart_definition['mapDataSourceSettings'] = $element['#map_data_source_settings'] ?? [];
    $chart_definition['mapDataSourceSettings'] += [
      'vid' => '',
      'tid' => 0,
      'json_field_name' => '',
    ];
    $chart_definition['chart']['width'] = $element['#width'] ?? NULL;
    $chart_definition['chart']['height'] = $element['#height'] ?? NULL;
    $chart_definition['chart']['type'] = $element['#chart_type'];
    $chart_definition['chart']['backgroundColor'] = $element['#background'];

    $chart_definition['credits']['enabled'] = FALSE;
    $chart_definition['title']['text'] = $element['#title'] ?? '';
    $chart_definition['title']['style']['color'] = $element['#title_color'];
    $chart_definition['title']['verticalAlign'] = $element['#title_position'] === 'in' ? 'top' : NULL;

    $chart_definition['chart']['map'] = [];
    $chart_definition['subtitle']['text'] = $element['#subtitle'] ?? 'Source map: <a href="https://code.highcharts.com/mapdata/countries/">World Map</a>';

    $chart_definition['mapNavigation']['enabled'] = TRUE;
    $chart_definition['mapNavigation']['buttonOptions']['verticalAlign'] = 'top';
    $chart_definition['mapNavigation']['buttonOptions']['alignTo'] = 'spacingBox';
    $chart_definition['mapNavigation']['buttonOptions']['x'] = 10;
    $chart_definition['colorAxis']['tickPixelInterval'] = 100;

    if ($element['#legend'] === TRUE) {
      $chart_definition['legend']['enabled'] = $element['#legend'];
      if (!empty($element['#legend_title'])) {
        $chart_definition['legend']['title']['text'] = $element['#legend_title'];
      }

      if ($element['#legend_position'] === 'bottom') {
        $chart_definition['legend']['verticalAlign'] = 'bottom';
        $chart_definition['legend']['layout'] = 'horizontal';
      }
      elseif ($element['#legend_position'] === 'top') {
        $chart_definition['legend']['verticalAlign'] = 'top';
        $chart_definition['legend']['layout'] = 'horizontal';
      }
      elseif ($element['#legend_position'] === 'left') {
        $chart_definition['legend']['verticalAlign'] = 'bottom';
        $chart_definition['legend']['layout'] = 'vertical';
        $chart_definition['legend']['align'] = 'left';
      }
      else {
        $chart_definition['legend']['align'] = $element['#legend_position'];
        $chart_definition['legend']['verticalAlign'] = 'middle';
        $chart_definition['legend']['layout'] = 'vertical';
      }

      // Setting more legend configuration based on the plugin form entry.
      $legend_configuration = $this->configuration['legend'] ?? [];
      if (!empty($legend_configuration['background_color'])) {
        $chart_definition['legend']['backgroundColor'] = $legend_configuration['background_color'];
      }
      if (!empty($legend_configuration['border_width'])) {
        $chart_definition['legend']['borderWidth'] = $legend_configuration['border_width'];
      }
      if (!empty($legend_configuration['shadow'])) {
        $chart_definition['legend']['shadow'] = TRUE;
      }
      if (!empty($legend_configuration['item_style']['color'])) {
        $chart_definition['legend']['itemStyle']['color'] = $legend_configuration['item_style']['color'];
      }
      if (!empty($legend_configuration['item_style']['overflow'])) {
        $chart_definition['legend']['itemStyle']['overflow'] = $legend_configuration['item_style']['overflow'];
      }
    }
    else {
      $chart_definition['legend']['enabled'] = FALSE;
    }

    // Merge in chart raw options.
    if (!empty($element['#raw_options'])) {
      $chart_definition = NestedArray::mergeDeepArray([
        $element['#raw_options'],
        $chart_definition,
      ]);
    }

    return $chart_definition;
  }

  /**
   * Utility to populate data.
   *
   * @param array $element
   *   The element.
   * @param array $chart_definition
   *   The chart definition.
   *
   * @return array
   *   Return the chart definition.
   */
  private function populateData(array &$element, array $chart_definition) {
    /** @var \Drupal\Core\Render\ElementInfoManagerInterface $element_info */
    $element_info = \Drupal::service('element_info');

    $series_keys = $this->processSeriesKeys($element);
    $series_join_by = $this->processSeriesJoinBy($element);
    $tooltip = $this->processSeriesTooltip($element['#series_data_settings']['tooltip'] ?? []);
    foreach (Element::children($element) as $key) {
      if ($element[$key]['#type'] === 'chart_data') {
        $series = [];
        $series_data = [];

        // Make sure defaults are loaded.
        if (empty($element[$key]['#defaults_loaded'])) {
          $element[$key] += $element_info->getInfo($element[$key]['#type']);
        }

        // Convert target named axis keys to integers.
        if (isset($element[$key]['#target_axis'])) {
          $axis_name = $element[$key]['#target_axis'];
          $axis_index = 0;
          foreach (Element::children($element) as $axis_key) {
            if ($element[$axis_key]['#type'] === 'chart_yaxis') {
              if ($axis_key === $axis_name) {
                break;
              }
              $axis_index++;
            }
          }
          $series['yAxis'] = $axis_index;
        }

        // Populate the data.
        foreach ($element[$key]['#data'] as $data_index => $data) {
          // Clean-up data keys.
          $data[0] = str_replace(["\r", "\n"], '', $data[0]);
          if (isset($series_data[$data_index])) {
            $series_data[$data_index][] = $data;
          }
          else {
            $series_data[$data_index] = $data;
          }
        }

        $series['type'] = $element[$key]['#chart_type'];
        $series['name'] = $element[$key]['#title'];
        $series['states']['hover']['color'] = $element[$key]['#color'];
        $series['dataLabels']['enabled'] = TRUE;
        $series['dataLabels']['format'] = '{point.properties.woe-name}';

        // joining keys.
        $series['keys'] = $series_keys;
        $series['joinBy'] = $series_join_by;
        if ($tooltip) {
          $series['tooltip'] = $tooltip;
        }

        // Remove unnecessary keys to trim down the resulting JS settings.
        ChartElement::trimArray($series);

        // If you want a different type of scatter.
        if (!empty($element['#alternative_scatter'])) {
          $series = $series_data;
        }
        else {
          $series['data'] = $series_data;
        }

        // Merge in series raw options.
        if (!empty($element[$key]['#raw_options'])) {
          $series = NestedArray::mergeDeepArray([
            $element[$key]['#raw_options'],
            $series,
          ]);
        }

        // Add the series to the main chart definition.
        // Scatter colors adjustment.
        if (!empty($element['#alternative_scatter'])) {
          $chart_definition['series'] = $series;
        }
        else {
          $chart_definition['series'][$key] = $series;
        }

        // Merge in any point-specific data points.
        foreach (Element::children($element[$key]) as $sub_key) {
          if ($element[$key][$sub_key]['#type'] === 'chart_data_item') {
            // Make sure defaults are loaded.
            if (empty($element[$key][$sub_key]['#defaults_loaded'])) {
              $element[$key][$sub_key] += $element_info->getInfo($element[$key][$sub_key]['#type']);
            }

            $data_item = $element[$key][$sub_key];
            $series_point = &$chart_definition['series'][$key]['data'][$sub_key];

            // Convert the point from a simple data value to a complex point.
            if (!isset($series_point['data'])) {
              $data = $series_point;
              $series_point = [];
              if (is_array($data)) {
                $series_point['name'] = $data[0];
                $series_point['y'] = $data[1];
              }
              else {
                $series_point['y'] = $data;
              }
            }
            if (isset($data_item['#data'])) {
              if (is_array($data_item['#data'])) {
                $series_point['x'] = $data_item['#data'][0];
                $series_point['y'] = $data_item['#data'][1];
              }
              else {
                $series_point['y'] = $data_item['#data'];
              }
            }
            if ($data_item['#title']) {
              $series_point['name'] = $data_item['#title'];
            }

            // Setting the color requires several properties for consistency.
            $series_point['color'] = $data_item['#color'];
            $series_point['fillColor'] = $data_item['#color'];
            $series_point['states']['hover']['fillColor'] = $data_item['#color'];
            $series_point['states']['select']['fillColor'] = $data_item['#color'];
            ChartElement::trimArray($series_point);

            // Merge in point raw options.
            if (!empty($data_item['#raw_options'])) {
              $series_point = NestedArray::mergeDeepArray(
                [
                  $data_item['#raw_options'],
                  $series_point
                ]
              );
            }
          }
        }
      }
    }

    return $chart_definition;
  }

  /**
   * Populate axes.
   *
   * @param array $element
   *   The element.
   * @param array $chart_definition
   *   The chart definition.
   *
   * @return array
   *   Return the chart definition.
   */
  private function populateAxes(array $element, array $chart_definition) {
    /** @var \Drupal\Core\Render\ElementInfoManagerInterface $element_info */
    $element_info = \Drupal::service('element_info');
    /** @var \Drupal\charts\TypeManager $chart_type_plugin_manager */
    $chart_type_plugin_manager = \Drupal::service('plugin.manager.charts_type');

    foreach (Element::children($element) as $key) {
      if ($element[$key]['#type'] === 'chart_xaxis' || $element[$key]['#type'] === 'chart_yaxis') {
        // Make sure defaults are loaded.
        if (empty($element[$key]['#defaults_loaded'])) {
          $element[$key] += $element_info->getInfo($element[$key]['#type']);
        }

        // Populate the chart data.
        $axis_type = $element[$key]['#type'] === 'chart_xaxis' ? 'xAxis' : 'yAxis';
        $axis = [];
        $axis['type'] = $element[$key]['#axis_type'];
        $axis['title']['text'] = $element[$key]['#title'];
        $axis['title']['style']['color'] = $element[$key]['#title_color'];
        $axis['categories'] = $element[$key]['#labels'];
        $axis['labels']['style']['color'] = $element[$key]['#labels_color'];
        $axis['labels']['rotation'] = $element[$key]['#labels_rotation'];
        $axis['gridLineColor'] = $element[$key]['#grid_line_color'];
        $axis['lineColor'] = $element[$key]['#base_line_color'];
        $axis['minorGridLineColor'] = $element[$key]['#minor_grid_line_color'];
        $axis['endOnTick'] = isset($element[$key]['#max']) ? FALSE : NULL;
        $axis['max'] = $element[$key]['#max'];
        $axis['min'] = $element[$key]['#min'];
        $axis['opposite'] = $element[$key]['#opposite'];

        if ($axis['labels']['rotation']) {
          $chart_type = $chart_type_plugin_manager->getDefinition($element['#chart_type']);
          if ($axis_type === 'xAxis' && !$chart_type['axis_inverted']) {
            $axis['labels']['align'] = 'left';
          }
          elseif ($axis_type === 'yAxis' && $chart_type['axis_inverted']) {
            $axis['labels']['align'] = 'left';
          }
        }

        // Merge in axis raw options.
        if (!empty($element[$key]['#raw_options'])) {
          $axis = NestedArray::mergeDeepArray(
            [
              $element[$key]['#raw_options'],
              $axis
            ]
          );
        }

        $chart_definition[$axis_type][] = $axis;
      }
    }

    return $chart_definition;
  }

  private function processSeriesKeys(array $element): ?array {
    if (empty($element['#series_data_settings']['keys_mapping_options'])) {
      return NULL;
    }
    // Validate provided series keys option and return NULL if any of the
    // series keys fail.
    foreach ($element['#series_data_settings']['keys_mapping_options'] as $keys_mapping_option) {
      if (empty($keys_mapping_option) || !is_string($keys_mapping_option)) {
        return NULL;
      }
    }
    return array_values($element['#series_data_settings']['keys_mapping_options']);
  }

  private function processSeriesJoinBy(array $element): array|string {
    $join_by_property_name = $element['#series_data_settings']['join_by_property_name'] ?? '';
    if (!$join_by_property_name || !is_string($join_by_property_name)) {
      return '';
    }
    $property_names = explode(',', $join_by_property_name);
    if (count($property_names) > 1) {
      return $property_names;
    }
    return $property_names[0];
  }

  private function processSeriesTooltip(array $options): array {
    $processed_tooltip = [];
    foreach ($options as $key => $value) {
      if ($value !== NULL) {
        $transformed_key = $this->transformSnakeCaseToCamelCase($key);
        $processed_tooltip[$transformed_key] = $value;
      }
    }
    return $processed_tooltip;
  }

  private function transformSnakeCaseToCamelCase(string $input) {
    $separator = '_';
    $input = strtolower($input);
    if (strpos($input, $separator) === FALSE) {
      return $input;
    }
    return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
  }

}
