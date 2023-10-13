/**
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
*/

Array.prototype.remove = function() {
    var what, a = arguments, L = a.length, ax;

    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }

    return this;
};

var homeproducts = {};

var prepare_template = function (config, messages) {
	var $imageForm = $('#image-name').parents('.form-group').last(),
		$linkForm = $('input[name^="link_"]').parents('.form-group').last(),
		$contentForm = $('textarea[name^="content"]').last().parents('.form-group').last(),
		$contentLabel = $contentForm.find('label'),
		$allAdditionalFieldForms = $('[id^="additional_field_"], [name^="additional_field_"]').closest('.form-group'),
		$templateAdditionalFieldForms = $('[id^="additional_field_'+config.basename+'_"], [name^="additional_field_'+config.basename+'_"]').closest('.form-group');

	$allAdditionalFieldForms
		.add($imageForm)
		.add($linkForm)
		.add($contentForm)
		.addClass('hide');
	$contentLabel.removeClass('required');
    $templateAdditionalFieldForms.removeClass('hide');

    if (config.homeproducts) {
        homeproducts[config.basename] = new anhomeproducts(config.basename);
        homeproducts[config.basename].init();
    }

    if (config.enabled_image) {
		$imageForm.removeClass('hide');
	}

    if (config.enabled_link) {
        $linkForm.removeClass('hide');
    }

    if (config.enabled_text) {
        $contentForm.removeClass('hide');
    }

    if (config.required_text) {
        $contentLabel.addClass('required');
    }
};

var anhomeproducts = function (template) {
    var self = this;
    this.template = template;
    this.token = token;
    this.prefix = 'additional_field_'+this.template;

    this.category_tree = $('#'+this.prefix+'_category_tree');
    this.products_tree = $('#'+this.prefix+'_products_tree');

    this.input = {
        value: $('input[name="'+self.prefix+'_value"]'),
        type: $('input[name="'+self.prefix+'_type"]'),
        products_input: $('input[name="'+self.prefix+'_products_input"]'),
        products_count: $('input[name="'+self.prefix+'_products_count"]'),
        category_box: this.category_tree.find('input[name="categoryBox[]"]')
    };

    var loc = location.pathname.split('/');
    loc.pop();
    this.ajax_products_list = loc.join('/')+'/';

    this.ajax_get_products = function (q) {
        return $.get(location.origin+self.ajax_products_list+'ajax_products_list.php?forceJson=1&disableCombination=1&exclude_packs=0&excludeVirtuals=0&limit=20&token='+this.token+'&q='+q);
    };

    this.add_entity = function (id_product) {
        var val = self.input.value.val().split(',');
        val.push(String(id_product));
        val.remove("").join(',');

        self.input.value.val(val);
        return self;
    };

    this.delete_entity = function(id_product) {
        self.input.value.val(self.input.value.val().split(',').remove(String(id_product)).join(','));
        return self;
    };

    this.bind_delete = function () {
        self.products_tree.find('i.delete').on('click', function () {
            self.delete_entity((parseInt($(this).attr('data-id')) || 0));
            return $(this).closest('li').remove();
        });

        return self;
    };

    this.disable = function () {
        self.category_tree.closest('.form-group').hide();
        self.products_tree.closest('.form-group').hide();
        self.input.type.closest('.form-group').hide();
        self.input.products_input.closest('.form-group').hide();
        self.input.products_count.closest('.form-group').hide();
        return self;
    };

    this.init = function () {
        self.category_tree.closest('.form-group').show();
        self.products_tree.closest('.form-group').show();
        self.input.type.closest('.form-group').show();
        self.input.products_input.closest('.form-group').show();
        self.input.products_count.closest('.form-group').show();

        $(document).ready(function () {
            var promise = $.when();
            var ctree = self.category_tree.closest('.form-group');
            var ptree = self.products_tree.closest('.form-group');
            var ptreeinput = self.input.products_input.closest('.form-group');
            var type_click_counter = 0;

            self.input.category_box.click(function() {
                if ($(this).is(':checked')) {
                    self.add_entity($(this).val());
                } else {
                    self.delete_entity($(this).val());
                }
            });

            self.input.type.click(function(e) {
                ctree.hide();
                ptree.hide();
                ptreeinput.hide();

                if (type_click_counter++ > 0) {
                    self.input.value.val('');
                    ptreeinput.find('#ajax-product-list').html('');
                    self.products_tree.find('ul').html('');
                    self.input.category_box.each(function () {
                        $(this).removeAttr('checked');
                        $(this).closest('.tree-selected').removeClass('tree-selected');
                    });
                }

                switch ($(this).val()) {
                    case 'category':
                        ctree.show();
                        break;

                    case 'ids':
                        ptree.show();
                        ptreeinput.show();
                        break;

                    case 'featured':
                        ctree.show();
                        break;
                }
            });

            ptreeinput.children('div').append($('<ul id="ajax-product-list" class="staticblock_ajax_product_list"></ul>'));

            (function (context) {
                'use strict';
                var checked = self.input.type.filter(':checked');

                if (checked.length) {
                    checked.click();
                } else {
                    self.input.type.filter(':first').click();
                }
            })(self);

            self.input.products_input.on('keyup', function () {
                var q = $(this).val();

                (function (value) {
                    promise = promise.then(function () {
                        return self.ajax_get_products(q);
                    }).then(function (response) {
                        if (!response) {
                            return false;
                        }

                        (function (products) {
                            ptreeinput.find('#ajax-product-list').html('');
                            $.each(products, function (i, product) {
                                ptreeinput.find('#ajax-product-list').append($('' +
                                    '<li data-id="'+product.id+'">' +
                                    '<img src="'+product.image+'">' +
                                    '<div class="label">'+product.name+'' + '</div>' +
                                    '</li>').on('click', function () {
                                    var id_product = parseInt($(this).attr('data-id')) || 0;

                                    if (self.input.value.val().split(',').indexOf(String(id_product)) != -1) {
                                        return false;
                                    }

                                    var label = $(this).children('.label').html();
                                    var image = $(this).children('img').attr('src');

                                    if (id_product > 0) {
                                        self.add_entity(id_product);
                                        self.products_tree.find('ul').append('' +
                                            '<li class="media">' +
                                            '<div class="media-left">' +
                                            '<img class="media-object image" src="'+image+'">' +
                                            '</div>' + '<div class="media-body media-middle">' +
                                            '<span class="label">'+label+'</span>' +
                                            '<i data-id="'+id_product+'" class="material-icons delete">delete</i>' +
                                            '</div></li>');
                                        self.bind_delete();
                                        ptreeinput.find('#ajax-product-list').html('');
                                    }
                                }));
                            });
                        })(JSON.parse(response));
                    });
                })(q);
            });

            self.bind_delete();
        });
    };
};