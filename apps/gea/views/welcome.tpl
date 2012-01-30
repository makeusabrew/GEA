{extends 'default/views/base.tpl'}
{block name='body'}
    {foreach from=$repos item="repo"}
        <div>{$repo->name}</div>
    {/foreach}
{/block}
