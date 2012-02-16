{extends 'default/views/base.tpl'}
{block name='body'}
    <h2>Add a project</h2>
    <form action="{$current_url}" method="post" class="form-horizontal">
        {include file="default/views/helpers/field.tpl" field="type"}
        {include file="default/views/helpers/field.tpl" field="name"}
        {include file="default/views/helpers/field.tpl" field="clone_url"}
        {include file="default/views/helpers/field.tpl" field="auth_type"}
        <input type="submit" class="btn-primary" value="Add Project" />
    </form>
{/block}
{block name='script'}
    <script>
        $(function() {
            $("form select[name='type']").change(function(e) {
                if ($(this).val() == 'other') {
                    //
                }
            });
        });
    </script>
{/block}
