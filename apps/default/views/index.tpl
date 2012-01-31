{extends file="base.tpl"}
{block name="body"}
    <h1>{setting value="site.title"}</h1>
    {if $user->isAuthed()}
        <p>Welcome <strong>{$user->username}</strong>.</p>
    {else}
        <p>Hello! Why not sign in to get started?</p>
    {/if}
{/block}
