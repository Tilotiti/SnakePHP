<div id="debugContent">
	<!-- Error -->
	<div id="debugErrorContent" class="debugContent">
		{foreach $listError as $error}
			<ul>
				<li><b>Type</b> :  {$error.type}</li>
                <li><b>File</b> :  {$error.file}</li>
                <li><b>Line</b> :  {$error.line}</li>
                <li><b>Message</b> : {$error.str}</li>
			</ul>
		{foreachelse}
			<div class="empty">No error</div>
		{/foreach}
	</div>
	<!-- SQL -->
	<div id="debugSQLContent" class="debugContent">
		{foreach $listSQL as $sql}
			<ul id="debugQuery{$sql.number}">
            	<li><b>{$sql.number} - Request</b> :{$sql.req}</li>
                <li><b>Result</b> : {$sql.count}</li>
                <li><b>Cached</b> : {if $sql.cached}true{else}false{/if}</li>
                <li><b>{if ($sql.cache===true || $sql.cache===false)}Cache{else}Category{/if}</b> :
                	{if $sql.cache===true}
                	true
                	{elseif $sql.cache===false}
                	false
                	{else}
                	"{$sql.cache}"
                	{/if}
                </li>
                <li><b>Timer</b> : {$sql.time}s</li>
			</ul>
		{foreachelse}
			<div class="empty">No SQL</div>
		{/foreach}
	</div>
	<!-- Dump -->
	<div id="debugDumpContent" class="debugContent">
		{foreach $listDump as $dump}
			<ul>
            	<li><b>{$dump.title}</b></li>
                <li><pre>{$dump.array}</pre></li>
			</ul>
		{foreachelse}
			<div class="empty">No Dump</div>
		{/foreach}
	</div>
	<!-- Global -->
	<div id="debugGlobalContent" class="debugContent">
		{foreach $listGlobal as $global}
			<ul>
            	<li><b>{$global.title}</b></li>
                <li><pre>{var_dump($global.var)}</pre></li>
			</ul>
		{foreachelse}
			<div class="empty">No Global</div>
		{/foreach}
	</div>
	<!-- Timer -->
	<div id="debugTimerContent" class="debugContent">
		<ul>
			{foreach $listTimer as $timer}
				<li>
	            	<span class="timerTitle"><b>{$timer.title}</b></span>
	                <div class="progress">
	                	<div class="bar" style="width: {$timer.pourcent}%"></div>
	                </div>
	                <span class="timerSeconde">{$timer.time} seconds</span>
				</li>
			{foreachelse}
				<div class="empty">No Timer</div>
			{/foreach}
		</ul>
	</div>
</div>
<div id="debug" class="navbar navbar-fixed-bottom">
	<div class="navbar-inner">
		<div class="container">
			<span class="brand" href="#">Debug</span>
			<ul class="nav">
				<!-- Error -->
				<li id="debugError">
					<a href="#">
						Error <span class="badge {$badge.error.type}">{$badge.error.count}</span>
					</a>
				</li>
				<!-- SQL -->
				<li id="debugSQL">
					<a href="#">
						SQL <span class="badge {$badge.sql.type}">{$badge.sql.count}</span>
					</a>
				</li>
				<!-- Dump -->
				<li id="debugDump">
					<a href="#">
						Dump <span class="badge {$badge.dump.type}">{$badge.dump.count}</span>
					</a>
				</li>
				<!-- Global -->
				<li id="debugGlobal">
					<a href="#">
						Global <span class="badge badge-info">3</span>
					</a>
				</li>
				<!-- Dump -->
				<li id="debugTimer">
					<a href="#">
						Timer <span class="badge {$badge.timer.type}">{$badge.timer.count}</span>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>