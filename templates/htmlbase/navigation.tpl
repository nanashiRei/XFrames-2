<div id="menubar">
	<ul id="menu" class="sf-menu">
		{foreach $XF->Navigation as $Item}
			<li><a href="{$XF->getConfig('global','Path')}{$Item->link}">{$Item->item}</a>
			{* {if $Item->hasSub}
				<ul>
					{foreach $Item->subItems as $SubItem}
						<li><a href="{$XF->getConfig('global','Path')}{$SubItem->link}">{$SubItem->item}</a></li>
					{/foreach}
				</ul>
			{/if} *}
			</li>
		{/foreach}
	</ul>
</div>