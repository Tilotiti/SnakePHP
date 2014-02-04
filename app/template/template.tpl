<!doctype html>
<html lang="fr">
<head>
    <meta charset="{$smarty.const.CHARSET}"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{$page->get('description')}">
    <meta name="keywords" content="{$page->get('keywords')}">
    
	<title>{$smarty.const.SITE} - {$page->get('title')}</title>
	
	{foreach $page->getCSS()  as $css}<link rel="stylesheet" media="screen" type="text/css" href="/css/{$css}.css" />
	{/foreach}
	
	{foreach $page->getJS() as $js}<script type="text/javascript" src="/js/{$js}.js"></script>
	{/foreach}
	
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <header class="navbar navbar-default navbar-fixed-top">
    	<div class="container-fluid">
	    	<div class="navbar-header">
	    		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#header-menu">
		    		<span class="sr-only">Toggle navigation</span>
		    		<span class="icon-bar"></span>
		    		<span class="icon-bar"></span>
		    		<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/" rel="home" title="{lang::title('index')}">{$smarty.const.SITE}</a>
			</div>
			<div class="collapse navbar-collapse" id="header-menu">
				<!-- Menu -->
				<ul class="nav navbar-nav">
					<li class="{$page->active('')}">
						<a href="/" title="{lang::title('index')}">{lang::title('index')}</a>
					</li>
					<li class="{$page->active('page1')}">
						<a href="/page1/" title="{lang::title('page1')}">{lang::title('page1')}</a>
					</li>
					<li class="{$page->active('page2')}">
						<a href="/page2/" title="{lang::title('page2')}">{lang::title('page2')}</a>
					</li>
					<li class="{$page->active('page3')}">
						<a href="/page3/" title="{lang::title('page3')}">{lang::title('page3')}</a>
					</li>
				</ul>
				<div class="navbar-right" id="breadcrumb">
					{$page->ariane()}
				</div>
			</div>
    	</div>
    </header>
    
    <section class="container">
    	<div class="row">
        	<div id="sidebar" class="col-md-3">
				{foreach $page->getSidebar() as $sidebar}
					{$page->display("sidebar/{$sidebar}")}
				{/foreach}
			</div>
			<div class="col-md-9">
				{if isset($message.text)}
					<div class="alert alert-{$message.type}">
						{$message.text}.
					</div>
				{/if}
				<div class="panel panel-default">
					<div class="panel-heading">
						<h1 class="panel-title">{$page->get('title')}</h1>
					</div>
					<div class="panel-body">
						{$page->display()}
					</div>
				</div>
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