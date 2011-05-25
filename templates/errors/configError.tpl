<!DOCTYPE html>
<html>
{include 'htmlbase/header.tpl'}
<body>
<div id="main">
    <div id="links"></div>
    {include 'htmlbase/bodyheader.tpl'}
	<div id="site_content">
		<div class="sidebar">
			<h1>XFrames Base</h1>
			<ul>
				<li>Version: {$XF->getConfig('global','version')}</li>
				<li>MySQL: 5.1</li>
			</ul>
		</div>
		<div class="content">
			<h1>Config Error</h1>
			{include 'errors/errorBodySmall.tpl'}
		</div>
	</div>
</div>
<div id="footer">Copyright &copy; YaS-Online.net. All Rights Reserved. | <a href="http://validator.w3.org/check?uri=referer">XHTML</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> | <a href="http://www.yas-online.net">Design by nanashiRei</a></div>
</body>
</html>