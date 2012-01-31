{extends 'default/views/base.tpl'}
{block name='body'}
    {foreach from=$projects item="project"}
        <div>
            {$project->name}
        </div>
    {/foreach}
{/block}
