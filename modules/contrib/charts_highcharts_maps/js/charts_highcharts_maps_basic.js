(async () => {

  const geoJsonKgz = await fetch(
    'https://code.highcharts.com/mapdata/countries/kg/kg-all.geo.json'
  ).then(response => response.json());

// Prepare random data
  let data = [
    ['KG.GB', 290],
    ['KG.BA', 390],
    ['KG.YK', 490],
    ['KG.NA', 590],
    ['KG.TL', 690],
    ['KG.OS', 790],
    ['KG.DA', 890],
    ['KG.', 590]
  ];

  // Initialize the chart
  Highcharts.mapChart('chart-map-drug-resistant-tb-cases-notified-page-11', {
    chart: {
      map: geoJsonKgz
    },

    title: {
      text: 'GeoJSON in Highmaps'
    },

    accessibility: {
      typeDescription: 'Map of Germany.'
    },

    mapNavigation: {
      enabled: true,
      buttonOptions: {
        verticalAlign: 'bottom'
      }
    },

    colorAxis: {
      tickPixelInterval: 100
    },

    series: [{
      data: data,
      keys: ['hasc', 'value'],
      joinBy: 'hasc',
      name: 'Random data',
      states: {
        hover: {
          color: '#a4edba'
        }
      },
      dataLabels: {
        enabled: true,
        format: '{point.properties.woe-name}'
      }
    }]
  });
})();
