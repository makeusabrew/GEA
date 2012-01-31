{extends 'default/views/base.tpl'}
{block name='body'}
    <div id="last_week"></div>
{/block}
{block name='script'}
    <script src='/js/highcharts/highcharts.js'></script>
    <script>
        $(function() {
            var chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'last_week',
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false
                },
                title: {
                    text: 'Commits by project, last 7 days'
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.y;
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Browser share',
                    data: [
                        {foreach from=$commits_week key="name" item="value" name="loop"}
                        ['{$name}', {$value}]
                        {if $smarty.foreach.loop.last == false},{/if}
                        {/foreach}
                    ]
                }]
            });
        });
    </script>
{/block}
