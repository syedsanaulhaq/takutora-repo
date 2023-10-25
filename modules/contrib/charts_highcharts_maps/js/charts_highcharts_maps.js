/**
 * @file
 * JavaScript's integration between Highmap and Drupal.
 */

(function (Drupal, once, drupalSettings) {

  'use strict';

  async function fetchMapData(mapDataSourceSettings) {
    if (!mapDataSourceSettings.tid || !mapDataSourceSettings.json_field_name) {
      return {};
    }

    const url = `${drupalSettings.path.baseUrl}${drupalSettings.path.pathPrefix}charts-highmap/map-data/${mapDataSourceSettings.json_field_name}/${mapDataSourceSettings.tid}`;
    const response = await fetch(url).catch(e => console.error(e));
    return response.json();
  }

  Drupal.behaviors.chartsHighmap = {
    attach: function (context) {
      const contents = new Drupal.Charts.Contents();
      once('charts-highmap', '.charts-highmap', context).forEach(async function (element) {
        const id = element.id;
        const config = contents.getData(id);
        if (!config) {
          return;
        }

        config.chart.renderTo = id;
        const mapData = await fetchMapData(config.mapDataSourceSettings);
        if (mapData) {
          config.chart.map = mapData;
        }
        new Highcharts.mapChart(config);
        if (element.nextElementSibling && element.nextElementSibling.hasAttribute('data-charts-debug-container')) {
          element.nextElementSibling.querySelector('code').innerText = JSON.stringify(config, null, ' ');
        }
      });
    },
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        once('charts-highmap-detach', '.charts-highmap', context).forEach(function (element) {
          if (!element.dataset.hasOwnProperty('highchartsChart')) {
            return;
          }
          Highcharts.charts[element.dataset.highchartsChart].destroy();
        });
      }
    }
  };
}(Drupal, once, drupalSettings));
