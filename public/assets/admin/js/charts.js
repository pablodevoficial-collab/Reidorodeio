// doughnut
function piChart(element, labels, data) {

     // Mapeamentos de imagens por label (países por FlagCDN e sistemas por Wikimedia)
     const assetMap = {
          // Países (FlagCDN, códigos ISO2 lowercase)
          'Brasil': 'https://flagcdn.com/w40/br.png',
          'Argentina': 'https://flagcdn.com/w40/ar.png',
          'Uruguai': 'https://flagcdn.com/w40/uy.png',
          'Paraguai': 'https://flagcdn.com/w40/py.png',
          'Estados Unidos': 'https://flagcdn.com/w40/us.png',
          'México': 'https://flagcdn.com/w40/mx.png',

          // Sistemas/Marcas (pequenas imagens via Wikimedia commons)
          'Windows': 'https://upload.wikimedia.org/wikipedia/commons/4/48/Windows_logo_-_2012.svg',
          'iOS': 'https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg',
          'Android': 'https://upload.wikimedia.org/wikipedia/commons/d/d7/Android_robot.svg'
     };

     // Pré-carrega imagens para as labels que tiverem mapeamento
     const flags = labels.map(function(label) {
          const url = assetMap[label];
          if (!url) return null;
          const img = new Image();
          img.src = url;
          img.__ready = false;
          img.onload = function() { img.__ready = true; if (chartInstance) chartInstance.update(); };
          // Alguns SVGs de Wikimedia não chamam onload corretamente em alguns ambientes; marcar pronto mesmo assim
          setTimeout(function() { if (!img.__ready) img.__ready = true; }, 800);
          return img;
     });

     let chartInstance = null;

     chartInstance = new Chart(element, {
           type: 'doughnut',
           data: {
                 labels: labels,
                 datasets: [{
                       data: data,
                   backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                   ],
                   borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                   ],
                   borderWidth: 0,

              }]
         },
         options: {
              aspectRatio: 1,
              responsive: true,
              maintainAspectRatio: true,
              elements: {
                   line: {
                        tension: 0 // disables bezier curves
                   }
              },
              scales: {
                   xAxes: [{
                        display: false
                   }],
                   yAxes: [{
                        display: false
                   }]
              },
              legend: {
                   display: false,
              }
         },
         options: {
              aspectRatio: 1,
              responsive: true,
              maintainAspectRatio: true,
              elements: {
                   line: {
                        tension: 0 // disables bezier curves
                   }
              },
              scales: {
                   xAxes: [{
                        display: false
                   }],
                   yAxes: [{
                        display: false
                   }]
              },
              legend: {
                   display: false,
              }
         },
         plugins: [{
             // Desenha as bandeiras no centro aproximado de cada fatia
             afterDraw: function(chart) {
                 try {
                     var ctx = chart.chart.ctx;
                     var meta = chart.getDatasetMeta(0);
                     meta.data.forEach(function(arc, index) {
                         var img = flags[index];
                         if (!img || !img.__ready) return;

                         // modelo do arco (Chart.js v2.x)
                         var model = arc._model;
                         var x = model.x;
                         var y = model.y;
                         var startAngle = model.startAngle;
                         var endAngle = model.endAngle;
                         var midAngle = (startAngle + endAngle) / 2;
                         var r = (model.outerRadius + model.innerRadius) / 2;
                         var px = x + Math.cos(midAngle) * r * 0.6;
                         var py = y + Math.sin(midAngle) * r * 0.6;

                         var w = 22, h = 14; // tamanho da bandeira
                         ctx.save();
                         ctx.beginPath();
                         ctx.drawImage(img, px - w/2, py - h/2, w, h);
                         ctx.restore();
                     });
                 } catch (e) {
                     // não bloquear renderização por erros no plugin
                     console.error(e);
                 }
             }
         }]
    });

    return chartInstance;
}

function barChart(element, currency, series, categories, height = 380) {
    let colors = ['#00e396', '#d92027'];

    let options = {
         series: series,
         chart: {
              type: 'bar',
              height: height,
              toolbar: {
                   show: true,
                   offsetX: 0,
                   offsetY: 0,
                   tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true,
                        customIcons: []
                   },
                   export: {
                        csv: {
                             filename: undefined,
                             columnDelimiter: ',',
                             headerCategory: 'category',
                             headerValue: 'value',
                             dateFormatter(timestamp) {
                                  return new Date(timestamp).toDateString()
                             }
                        },
                        svg: {
                             filename: undefined,
                        },
                        png: {
                             filename: undefined,
                        }
                   },
                   autoSelected: 'zoom'
              },
         },
         plotOptions: {
              bar: {
                   horizontal: false,
                   columnWidth: '50%',
                   endingShape: 'rounded'
              },
         },
         dataLabels: {
              enabled: false
         },
         stroke: {
              show: true,
              width: 2,
              colors: ['transparent']
         },
         xaxis: {
              categories: categories,
         },

         yaxis: {
              title: {
                   text: currency,
                   style: {
                        color: '#7c97bb'
                   }
              }
         },
         grid: {
              xaxis: {
                   lines: {
                        show: false
                   }
              },
              yaxis: {
                   lines: {
                        show: false
                   }
              },
         },
         fill: {
              opacity: 1,
              colors: colors
         },
         tooltip: {
              y: {
                   formatter: function (val) {
                        return currency + " " + val + " "
                   },
              },
              marker: {
                   fillColors: colors
              },
         },
         legend: {
              show: true,
              markers: {
                   fillColors: colors
              },
              labels: {
                   colors: colors
              }
         }
    };

    let chart = new ApexCharts(element, options);
    chart.render();
    return chart
}

function lineChart(element, series, categories, height = 380) {
    let colors = ['#00e396', '#d92027'];
    var options = {
         chart: {
              height: height,
              type: "area",
              toolbar: {
                   show: true,
                   offsetX: 0,
                   offsetY: 0,
                   tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true,
                        customIcons: []
                   },
                   autoSelected: 'zoom'
              },
              dropShadow: {
                   enabled: true,
                   enabledSeries: [0],
                   top: -2,
                   left: 0,
                   blur: 10,
                   opacity: 0.08
              },
              animations: {
                   enabled: true,
                   easing: 'linear',
                   dynamicAnimation: {
                        speed: 1000
                   }
              },
         },
         colors: colors,
         dataLabels: {
              enabled: false
         },
         series: series,
         fill: {
              type: "gradient",
              gradient: {
                   shadeIntensity: 1,
                   opacityFrom: 0.7,
                   opacityTo: 0.9,
                   stops: [0, 90, 100]
              }
         },
         xaxis: {
              categories: categories
         },
         grid: {
              padding: {
                   left: 5,
                   right: 5
              },
              xaxis: {
                   lines: {
                        show: false
                   }
              },
              yaxis: {
                   lines: {
                        show: false
                   }
              },
         },
    };

    var chart = new ApexCharts(element, options);

    chart.render();

    return chart
}