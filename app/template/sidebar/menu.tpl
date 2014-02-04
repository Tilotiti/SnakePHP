<ul class="nav nav-pills nav-stacked">
	<li class="{$page->active('index')}">
		<a href="/">
			<i class="glyphicon glyphicon-home"></i> {lang::title('index')}
		</a>
	</li>
	<li class="{$page->active('page1')}">
		<a href="/page1/" title="{lang::title('page1')}">
			<i class="glyphicon glyphicon-book"></i> {lang::title('page1')}
		</a>
	</li>
	<li class="{$page->active('page2')}">
		<a href="/page2/" title="{lang::title('page2')}">
			<i class="glyphicon glyphicon-hdd"></i> {lang::title('page2')}
		</a>
	</li>
	<li class="{$page->active('page3')}">
		<a href="/page3/" title="{lang::title('page3')}">
			<i class="glyphicon glyphicon-envelope"></i> {lang::title('page3')}
		</a>
	</li>
</ul>