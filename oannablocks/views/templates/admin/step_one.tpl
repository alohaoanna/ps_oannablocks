{**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Apply Novation <applynovation@gmail.com>
*  @copyright 2016-2017 Apply Novation
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="panel" id="step_one_panel">
    <div class="panel-heading">{l s='Step one' mod='oannablocks'}</div>
    <div class="templates-select">
            {foreach from=$templates item=template}
                <div class="oannablocks-tpl">
                    <a href="{$template_select_url|escape:'htmlall':'utf-8'}{$template.file|basename:'.tpl'|escape:'htmlall':'utf-8'}" class="oannablocks-tpl-wrap">
						<div class="oannablocks-tpl-thumbnail">
							<img style="width: 100%" src="{$preview_url}{$template.file|basename:'.tpl'}/preview.png">
						</div>
                        <div class="oannablocks-tpl-name">{$template.name|escape:'htmlall':'UTF-8'}</div>
						{if $template.config.description != ''}
                        <div class="oannablocks-tpl-desc">{$template.config.description|escape:'htmlall':'UTF-8'}</div>
						{/if}
                    </a>
                </div>
            {/foreach}
    </div>
</div>