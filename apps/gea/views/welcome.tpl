{extends 'default/views/base.tpl'}
{block name='body'}
    <form action="/import" method="post">
        <ul>
            {foreach from=$repos item="repo"}
                <li>
                    <input type="checkbox" id="repo_{$repo->id}" name="repos[{$repo->id}]" checked="" />
                    <label for="repo_{$repo->id}">{$repo->name}
                </li>
            {/foreach}
        </ul>
        <input type="submit" value="Aggregate Selected" />
    </form>
{/block}
