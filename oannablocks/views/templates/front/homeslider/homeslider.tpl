<div class="{if $an_staticblock->formdata->additional_field_homeslider_responsiveImage =='1'}img-responsive{/if}
oannablocks_homeslider-block {if $an_staticblock->formdata &&
$an_staticblock->formdata->additional_field_homeslider_mobile=='0'}  oannablocks-homeslider-hide-mobile{/if} {if $an_staticblock->formdata && $an_staticblock->formdata->additional_field_homeslider_desktop=='0'}  oannablocks-homeslider-hide-desktop{/if} {if $an_staticblock->formdata && $an_staticblock->formdata->additional_field_homeslider_desktopcontent=='0'}  oannablocks-homeslider-hide-desktop-content{/if} {if $an_staticblock->formdata && $an_staticblock->formdata->additional_field_homeslider_mobilecontent=='0'}  oannablocks-homeslider-hide-mobile-content{/if}">
	<div class="{if $an_staticblock->formdata->additional_field_homeslider_responsiveImage =='1'}img-responsive{/if} oannablocks-homeslider {if $an_staticblock->formdata->additional_field_homeslider_preloader =='0'}owl-carousel{/if} owl-theme{if $an_staticblock->formdata && $an_staticblock->formdata->additional_field_homeslider_mobile=='0'}  oannablocks-homeslider-hide-mobile{/if}" id="oannablocks-homeslider_{$an_staticblock->id}" data-lazy="{$an_staticblock->formdata->additional_field_homeslider_lazyload}" {if $an_staticblock->formdata} data-items="{$an_staticblock->formdata->additional_field_homeslider_items}" data-nav="{$an_staticblock->formdata->additional_field_homeslider_nav}" data-dots="{$an_staticblock->formdata->additional_field_homeslider_dots}" data-loop="{$an_staticblock->formdata->additional_field_homeslider_loop}"   data-autoplay="{$an_staticblock->formdata->additional_field_homeslider_autoplay}" data-autoplaytimeout="{$an_staticblock->formdata->additional_field_homeslider_autoplayTimeout}"{/if}>
	{foreach from=$an_staticblock->getChildrenBlocks() item=block}
		{$param['lazyload'] = $an_staticblock->formdata->additional_field_homeslider_lazyload}
        <div class="item">{$block->getContent($param) nofilter}</div>
	{/foreach}
	</div>
	{if $an_staticblock->formdata}
		{if $an_staticblock->formdata->additional_field_homeslider_preloader}
		<div class="oannablocks-homeslider-loader">
			<div class="loader-dots">
				<div class="loader-image"></div>
			</div>
		</div>
		{/if}
	{/if}
</div>