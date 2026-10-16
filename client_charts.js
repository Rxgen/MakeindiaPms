(function () {
  var data = window.PMS_CHARTS;
  if (!data || typeof Chart === 'undefined') {
    return;
  }

  var colors = ['#CC0000', '#111111', '#8a6a32', '#444444', '#b33b3b', '#6b6b6b'];

  function rupee(value) {
    return '₹' + Number(value || 0).toLocaleString('en-IN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function doughnut(id, labels, values) {
    var canvas = document.getElementById(id);
    if (!canvas || !labels || !labels.length) {
      return;
    }
    new Chart(canvas, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors.slice(0, labels.length),
          borderColor: '#ffffff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { boxWidth: 12, font: { size: 12 } }
          },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                var total = ctx.dataset.data.reduce(function (sum, n) { return sum + Number(n); }, 0);
                var pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : '0.0';
                return ' ' + ctx.label + ': ' + rupee(ctx.parsed) + ' (' + pct + '%)';
              }
            }
          }
        },
        cutout: '58%'
      }
    });
  }

  doughnut('holdingsChart', data.holdings.labels, data.holdings.values);
  doughnut('capitalChart', data.capital.labels, data.capital.values);
})();
