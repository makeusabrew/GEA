{extends 'default/views/base.tpl'}
{block name='body'}
    <form action="{$current_url}" method="post">
        <input type="text" name="name" />
        <input type="text" name="clone_url" />
        <input type="submit" class="btn" value="Add Project" />
    </form>
{/block}
