{extends 'default/views/base.tpl'}
{block name='body'}
    <div class=row>
        <div class=span3>
            <!-- -->
            <div class=well style="padding: 8px 0;">
                <ul class="nav nav-list">
                    <li class="nav-header">Home</li>
                    <li class=active><a href="#"><i class="icon-picture"></i> Dashboard</a></li>
                    <li class="nav-header">Aggregated (All Projects)</li>
                    <li><a href="#"><i class="icon-signal"></i> Stacked Commits</a></li>
                    <li><a href="#"><i class="icon-list"></i> Commit Log</a></li>
                    <li><a href="#"><i class="icon-adjust"></i> Project Percentage</a></li>
                    <li class="nav-header">Per Project</li>
                    <li><a href="#"><i class="icon-cog"></i> My Contribution</a></li>
                    <li><a href="#"><i class="icon-time"></i> Working Hours</a></li>
                </ul>
            </div>
        </div>
        <div class=span9>
            <form action="" method="get" class="form-inline" style="float:right">
                <input type="text" class="input-small" placeholder="from date" />
                <input type="text" class="input-small" placeholder="to date" />
                <input type="submit" class="btn" value="Go" />
            </form>
            <div id="last_week_stacked"></div>
            <div id="last_year_stacked"></div>
            <div id="last_week"></div>
        </div>
    </div>
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

            var stacked = new Highcharts.Chart({
                chart: {
                    renderTo: 'last_week_stacked',
                    defaultSeriesType: 'column'
                },
                title: {
                    text: "Commits by day, last 30 days"
                },
                xAxis: {
                    categories: [{foreach from=$stacked_labels item="label" name="loop"}'{$label}'{if $smarty.foreach.loop.last == false},{/if}{/foreach}]
                },
                yAxis: {
                    title: {
                        text: "Total commits"
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: "bold",
                            color: "gray"
                        }
                    }
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.x +'</b><br/>'+
                        this.series.name +': '+ this.y +'<br/>'+
                        'Total: '+ this.point.stackTotal;
                    }
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: false
                        }
                    }
                },
                series: [
                    {foreach from=$commits_stacked item="data" key="repo" name="loop"}
                    {
                    name: '{$repo}',
                    data: [{foreach from=$data item="day" name="inner"}{if $day == 0}null{else}{$day}{/if}{if $smarty.foreach.inner.last == false},{/if}{/foreach}]
                    }{if $smarty.foreach.loop.last == false},{/if}
                    {/foreach}
                ]
            });

            var year_stacked = new Highcharts.Chart({
                chart: {
                    renderTo: 'last_year_stacked',
                    defaultSeriesType: 'column'
                },
                title: {
                    text: "Commits by month, last year"
                },
                xAxis: {
                    categories: [{foreach from=$year_stacked_labels item="label" name="loop"}'{$label}'{if $smarty.foreach.loop.last == false},{/if}{/foreach}]
                },
                yAxis: {
                    title: {
                        text: "Total commits"
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: "bold",
                            color: "gray"
                        }
                    }
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.x +'</b><br/>'+
                        this.series.name +': '+ this.y +'<br/>'+
                        'Total: '+ this.point.stackTotal;
                    }
                },
                plotOptions: {
                    column: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: false
                        }
                    }
                },
                series: [
                    {foreach from=$year_commits_stacked item="data" key="repo" name="loop"}
                    {
                    name: '{$repo}',
                    data: [{foreach from=$data item="day" name="inner"}{if $day == 0}null{else}{$day}{/if}{if $smarty.foreach.inner.last == false},{/if}{/foreach}]
                    }{if $smarty.foreach.loop.last == false},{/if}
                    {/foreach}
                ]
            });
        });
    </script>
{/block}
