<meta charset="{$smarty.const.CHARSET}"/>
<title>{$smarty.const.SITE} - {$page->title}</title>

{foreach $page->getCSS()  as $css}
<link rel="stylesheet" media="screen" type="text/css" href="/css/{$css}.css" />
{/foreach}
<!--[if lt IE 9]>
<link rel="stylesheet" href="/css/ie.css" type="text/css" media="screen" />
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
{foreach $page->getJS() as $js}
<script type="text/javascript" src="/js/{$js}.js"></script>
{/foreach}