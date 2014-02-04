<!doctype html>
<html lang="fr">
<head>
    <meta charset="{$smarty.const.CHARSET}"/>
    <meta name="description" content="{$page->get('description')}">
    <meta name="keywords" content="{$page->get('keywords')}">
    
	<title>{$smarty.const.SITE} - {$page->get('title')}</title>
	
	{foreach $page->getCSS()  as $css}<link rel="stylesheet" media="screen" type="text/css" href="/css/{$css}.css" />
	{/foreach}
	
	{foreach $page->getJS() as $js}<script type="text/javascript" src="/js/{$js}.js"></script>
	{/foreach}
</head>
<body>
    <header class="navbar navbar-default navbar-fixed-top">
    	<div class="navbar-header">
    		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#header-menu">
	    		<span class="sr-only">Toggle navigation</span>
	    		<span class="icon-bar"></span>
	    		<span class="icon-bar"></span>
	    		<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/">{$smarty.const.SITE}</a>
		</div>
		<div class="collapse navbar-collapse" id="header-menu">
			<div class="btn-group pull-right">
				<span class="btn">
					<i class="icon-map-marker"></i> {$page->ariane()}
				</span>
			</div>
			<!-- Menu -->
			<ul class="nav navbar-nav">
				<li class="{$page->active('index')}"><a href="/index/">Accueil</a></li>
				<li class="{$page->active('doc')}"><a href="/doc/">Documentation</a></li>
				<li class="{$page->active('download')}"><a href="/download/">T&eacute;l&eacute;chargement</a></li>
				<li class="{$page->active('contact')}"><a href="/contact/">Contact</a></li>
			</ul>
		</div>
    </header>
    
    <section class="container">
    	<div class="row">
        	<div id="sidebar" class="col-md-3">
				{foreach $page->getSidebar() as $sidebar}
				<div class="overlay">
				{include file="sidebar/`$sidebar`.tpl"}
				</div>
				{/foreach}
			</div>
			<div class="col-md-9 overlay">
				<h1>{$page->get('title')}</h1>
				{if isset($message.text)}
					<div class="alert alert-{$message.type}">
						{$message.text}.
					</div>
				{/if}
				{include file="`$page->get('templateTPL')`.tpl"}
			</div>
		</div>
		<hr>
		<footer>
        	<p>{$page->copyright()}</p>
        </footer>
    </section>
    
    {$page->debug()}
</body>
</html>