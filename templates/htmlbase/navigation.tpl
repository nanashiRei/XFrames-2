<div id="menubar">
	<ul id="menu">
		{foreach $XF->Navigation as $Item}
			<li><a href="{$XF->getConfig('global','Path')}{$Item->link}">{$Item->item}</a></li>
		{/foreach}
	</ul>
</div>