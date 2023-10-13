{if $an_staticblock->link!=''}
<a href="{$an_staticblock->link}">
	{/if}
	{if $an_staticblock->getImageLink() != ''}
	<img
        {if $an_staticblock->param['lazyload']}
        class="owl-lazy" data-src="{$an_staticblock->getImageLink()}"
        {else}
        src="{$an_staticblock->getImageLink()}"
        {/if}
        width="auto" height="auto" alt="{$an_staticblock->title|escape:'htmlall':'UTF-8'}"
    >
	{/if}
	<div class="oannablocks-caption">
		<div class="container">
			<div class="oannablocks-homeslider-desc">
				<h2>{$an_staticblock->title|escape:'htmlall':'UTF-8'}</h2> {$an_staticblock->content nofilter} {if $an_staticblock->link!=''}
				<button class="btn btn-primary">{l s='Boutique en ligne' mod='oannablocks'}</button>
				{/if}
			</div>
		</div>
	</div>
	{if $an_staticblock->link!=''}
	</a>
	{/if}