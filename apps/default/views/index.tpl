{extends file="base.tpl"}
{block name="body"}
    <h1>{setting value="site.title"}</h1>
    {if $user->isAuthed()}
        <p>Hi</p>
    {else}
        <p><a href="/login">Auth</a></p>
    {/if}
{/block}
