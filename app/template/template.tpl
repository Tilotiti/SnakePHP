<!doctype html>
<html lang="fr">
<head>
    {include file="tools/meta.tpl"}
</head>
<body>
    <header>
        {include file="tools/header.tpl"}
        <nav id="menu">
            {include file="tools/menu.tpl"}
        </nav>
        <div class="clear"></div>
    </header>
    <nav id="ariane">
        {$page->ariane()}
    </nav>
    <aside id="sidebarLeft">
        {foreach $page->getSidebar('left') as $sidebar}
            <div class="sideBox">
                {include file="sidebar/`$sidebar`.tpl"}
            </div>
        {/foreach}
    </aside>
    <section>
	{if $message}
            <h4 class="alert_{$message.type}">{$message.text}</h4>
        {/if}
        <h1>{$page->get('title')}</h1>
        <article>
            {include file="`$page->get('template')`.tpl"}
        </article>
        {$debug->clear()}
    </section>
    <aside id="sidebarRight">
        {foreach $page->getSidebar('right') as $sidebar}
            <div class="sideBox">
                {include file="sidebar/`$sidebar`.tpl"}
            </div>
        {/foreach}
    </aside>
    <footer>{$page->copyright()}</footer>
</body>
</html>