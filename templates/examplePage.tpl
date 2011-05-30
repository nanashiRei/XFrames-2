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
			<h1>Example Page</h1>
			<p>Hi, there :)</p>
			<img src="{$XF->getConfig('global','Path')}/images/xflogo.png" alt="xflogo" />
			<h2>Get Variables</h2>
			<ul>
				{foreach $smarty.get as $var}
					<li>{$var@key} => {$var}</li>
				{/foreach}
			</ul>
			<h2>Xframes2 Environment</h2>
			<p>$XF->Environment</p>
			<p>You can assign get variables to any Xframes2 variable you want, and it's fail safe! And access them via the XF::Environment or via Smarty directly.</p>
			<code>$this->assignEnvironment('var1','othervar','whatever');</code>
			<table>
				<thead>
					<tr>
						<th>Variable</th>
						<th>Value</th>
					</tr>
				</thead>
				<tbody>
    				{foreach $XF->Environment as $var}
    					<tr>
    						<td>XF::Environment[{$var@key}]</td>
    						<td>{$var}</td>
    					</tr>
    					<tr>
    						<td>${$var@key}</td>
    						<td>{$var}</td>
    					</tr>
    				{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>
<div id="footer">Copyright &copy; YaS-Online.net. All Rights Reserved. | <a href="http://validator.w3.org/check?uri=referer">XHTML</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> | <a href="http://www.yas-online.net">Design by nanashiRei</a></div>
</body>
</html>