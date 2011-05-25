<h2>#{$Exception->getLine()}: {basename($Exception->getFile())}</h2>
<p>{$Exception->getMessage()}</p>
<h5>Stack Tracke</h5>
<code>
	{foreach $Exception->getTrace() as $Stack}
		#{$Stack.line}: [{basename($Stack.file)}] in function {$Stack.class}::{$Stack.function}<br />
	{/foreach}
</code>