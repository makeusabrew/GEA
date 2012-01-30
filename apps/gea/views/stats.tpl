{extends 'default/views/base.tpl'}
{block name='body'}
    {foreach from=$commits item="commit"}
        <div>
            {$commit->date|date_format:"d/m/Y H:i"}
            {$commit->message}
        </div>
    {/foreach}
{/block}
