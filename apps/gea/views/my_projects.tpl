{extends 'default/views/base.tpl'}
{block name='body'}
    <a href="/projects/add">Add a project</a>
    {foreach from=$projects item="project"}
        <div>
            {$project->name}
        </div>
    {/foreach}
{/block}
