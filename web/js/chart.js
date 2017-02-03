$(function () {
    $.getJSON('chart/json', function (data) {

        Highcharts.setOptions({
            lang: {
                loading: 'Загрузка...',
                months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                weekdays: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                shortMonths: ['Янв', 'Фев', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сент', 'Окт', 'Нояб', 'Дек'],
                rangeSelectorFrom: "С",
                rangeSelectorTo: "По",
                rangeSelectorZoom: "Период"
            }
        });
    
        Highcharts.stockChart('chart', {

            rangeSelector: {
                selected: 1
            },

            title: {
                text: ''
            },

            series: [{
                name: portfolioName,
                data: data,
                tooltip: {
                    valueDecimals: 2
                }
            }], 
          
            exporting: { enabled: false }
            
        });
    });
});