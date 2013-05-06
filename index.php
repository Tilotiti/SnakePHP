<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
	<title>SnakePHP - Error</title>
	<link rel="stylesheet" media="screen" type="text/css" href="/webroot/css/bootstrap.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/webroot/css/snakephp.css" />
</head>
<body>
	<div id="main">
		<h1>SnakePHP</h1>
		<div id="content">
			<p>Thank you for using SnakePHP, now you should configure your server and your file <code>/config.php</code> :</p>
			<ul>
				<li>Configure your SnakePHP in <code>/config.php</code></li>
				<li>Enable <code>mod_rewrite</code> in apache : <code>a2enmod rewrite</code></li>
				<li>Set the <code>AllowOverride</code> option to <code>All</code> in your apache2 vhost</li>
				<li>Set <code>CHMOD 777</code> on <code>/cache/</code></li>
				<li>Set <code>CHMOD 777</code> on <code>/lang/</code></li>
				<li>Set <code>CHMOD 777</code> on <code>/log/</code></li>
				<li>Set <code>CHMOD 777</code> on <code>/webroot/file/</code></li>
				<li>Set <code>CHMOD 644</code> on <code>/app/</code></li>
				<li>Set <code>CHMOD 644</code> on <code>/lib/</code></li>
				<li>Have fun !</li>
			</ul>
		</div>
	</div>
</body>
</html>