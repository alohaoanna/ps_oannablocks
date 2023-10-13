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

<script type="text/javascript">
    var templates = JSON.parse("{$templates|json_encode|escape:'javascript':'UTF-8'}");
    var token = '{$token|escape:'html':'UTF-8'}';
    var template = JSON.parse("{$template|json_encode|escape:'javascript':'UTF-8'}");
    var messages = {
        required_message: "{l s='Content required' mod='oannablocks'}"
    };

    jQuery(document).ready(function () {
        $('.panel-footer a[href="1"]').attr({
            href: decodeURIComponent('{$cancel_url|urlencode}'),
            onclick: 'return true'
        });

        var select_template = function (template) {
            return prepare_template(templates.filter(function (tpl) {
                return tpl.file == template;
            }).shift().config, messages);
        };

        if (jQuery('select[name="template"]').length) {
            jQuery('select[name="template"]').on('change', function () {
                return select_template(jQuery(this).val());
            }).trigger('change');

            if (typeof template == 'object') {
                jQuery('select[name="template"]').val(template.file).trigger('change');
            }
        } else {
            return select_template(jQuery('input[name="template"]').val());
        }
    });
</script>

{if $id_parent|intval == 0}
    <script type="text/javascript">
        $(document).ready(function () {
            $('select[name="template"]').closest(".form-group").addClass('hide');
        });
    </script>
    {if $id|intval != 0}
        <div class="panel preview">
            <div class="panel-heading">
                {l s='Template' mod='oannablocks'}
            </div>
            {if $template !== false}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="oannablocks-preview">
                            <div class="oannablocks-preview-thumbnail">
                                <img src="{$smarty.const._THEME_DIR_}modules/oannablocks/views/templates/front/{$template.file|basename:'.tpl'}/preview.png">
                            </div>
                            <div class="oannablocks-tpl-name">
                                {$template.name|escape:'htmlall':'UTF-8'}
                            </div>
                            {if ($template.config.description != '')}
                                <div class="oannablocks-tpl-desc">
                                    {$template.config.description|escape:'htmlall':'UTF-8'}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {else}
                <div class="alert alert-danger">
                    {l s='Can not preview template' mod='oannablocks'}
                </div>
            {/if}
        </div>
    {/if}
{/if}
