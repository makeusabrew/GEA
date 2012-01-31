<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{block name='title'}{setting value="site.title"}{/block}</title>
    <link rel="stylesheet" href="/bootstrap/docs/assets/css/bootstrap.css" />
    <link rel="stylesheet" href="/bootstrap/docs/assets/css/bootstrap-responsive.css" />
    <link rel="stylesheet" href="/css/main.css" />
    {include file='default/views/helpers/google_analytics.tpl'}
</head>
<body>
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="brand" href="/">Git(hub) Effort Aggregator</a>
                <div class="nav-collapse">
                    <ul class="nav">
                        <li class="active"><a href="/">Home</a></li>
                        {if $user->isAuthed()}
                            <li><a href="/{$user->username}">my page</a></li>
                        {else}
                            <li><a href="/login">Sign in</a></li>
                        {/if}
                    </ul>
                </div>
            </div>
        </div>
    </div>
    {block name="body"}
        <p>Your body content goes here. This block will be automatically
        overridden when you extend this base template and re-declare
        this block.</p>
    {/block}

    {*
      ordinarily body will probably be wrapped with surrounding markup, so it
      makes sense to have a separate block to put script tags in
     *}
     <script src="/js/jquery.min.js"></script>
    {block name="script"}{/block}

    {* default tracking is GA *}
</body>
</html>
