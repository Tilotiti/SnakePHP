<!doctype html>
<html lang="fr">
<head>
    <meta charset="{$smarty.const.CHARSET}"/>
	<title>{$smarty.const.SITE} - {$page->get('title')}</title>
	
	{foreach $page->getCSS()  as $css}
	<link rel="stylesheet" media="screen" type="text/css" href="/css/{$css}.css" />
	{/foreach}
	
	{foreach $page->getJS() as $js}
	<script type="text/javascript" src="/js/{$js}.js"></script>
	{/foreach}
</head>
<body>
	<div class="navbar navbar-fixed-top">
    	<div class="navbar-inner">
        	<div class="container-fluid">
        		<a class="brand" href="#">{$smarty.const.SITE}</a>
        		<div class="btn-group pull-right">
        			<span class="btn">
        				<i class="icon-map-marker"></i> {$page->ariane()}
        			</span>
        		</div>
        		<div class="nav-collapse">
        			<!-- Menu -->
        			<ul class="nav">
        				<li class="{$page->active('index')}"><a href="/index/">Accueil</a></li>
        				<li class="{$page->active('doc')}"><a href="/doc/">Documentation</a></li>
        				<li class="{$page->active('download')}"><a href="/download/">T&eacute;l&eacute;chargement</a></li>
        				<li class="{$page->active('contact')}"><a href="/contact/">Contact</a></li>
        			</ul>
        		</div>
        	</div>
        </div>
    </div>

    <div class="container-fluid">
    	<div class="row-fluid">
        	<div class="span3">
        		<div class="well sidebar-nav">
					{foreach $page->getSidebar() as $sidebar}
						{include file="sidebar/`$sidebar`.tpl"}
					{/foreach}
				</div>
			</div>
			<div class="span9">
				{if isset($message.text)}
					<div class="alert alert-{$message.type}">
						{$message.text}.
					</div>
				{/if}
				{include file="`$page->get('template')`.tpl"}
			</div>
		</div>
		<hr>
		<footer>
        	<p>{$page->copyright()}</p>
        </footer>
    </div>
    
    {$page->debug()}
</body>
</html>