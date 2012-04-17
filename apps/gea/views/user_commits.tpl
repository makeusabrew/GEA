{extends 'default/views/base.tpl'}
{block name='body'}
    <div class=row>
        <div class=span3>
            <!-- -->
            <div class=well style="padding: 8px 0;">
                <ul class="nav nav-list">
                    <li class="nav-header">Home</li>
                    <li><a href="/stats"><i class="icon-picture"></i> Dashboard</a></li>
                    <li class="nav-header">Aggregated (All Projects)</li>
                    <li><a href="#"><i class="icon-signal"></i> Stacked Commits</a></li>
                    <li class=active><a href="/stats/commits"><i class="icon-list"></i> Commit Log</a></li>
                    <li><a href="#"><i class="icon-adjust"></i> Project Percentage</a></li>
                    <li class="nav-header">Per Project</li>
                    <li><a href="#"><i class="icon-cog"></i> My Contribution</a></li>
                    <li><a href="/stats/hours"><i class="icon-time"></i> Working Hours</a></li>
                </ul>
            </div>
        </div>
        <div class=span9>
            <form action="" method="get" class="form-inline" style="float:right">
                <input type="text" class="input-small" placeholder="from date" />
                <input type="text" class="input-small" placeholder="to date" />
                <input type="submit" class="btn" value="Go" />
            </form>
            {foreach from=$commits item="commit"}
                <div>
                    {$commit->date|date_format:"d/m/Y H:i"}
                    {$commit->r_name}
                    {$commit->message}
                </div>
            {/foreach}
        </div>
    </div>
{/block}
