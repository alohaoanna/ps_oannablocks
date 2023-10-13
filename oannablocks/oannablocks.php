<?php
/**
 * 2020 Anvanto
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 *  @author Anvanto <anvantoco@gmail.com>
 *  @copyright  2020 Anvanto
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of Anvanto
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'oannablocks/lib/OannaSpyc.php';
require_once _PS_MODULE_DIR_.'oannablocks/lib/OannaFinder.php';
include_once _PS_MODULE_DIR_.'oannablocks/classes/OannaBlockData.php';
include_once _PS_MODULE_DIR_.'oannablocks/classes/OannaBlockDataValidator.php';
include_once _PS_MODULE_DIR_.'oannablocks/classes/OannaBlock.php';
require_once _PS_MODULE_DIR_.'oannablocks/classes/OannaBlockCollection.php';

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;


class Oannablocks extends Module implements WidgetInterface
{
    const PREFIX = 'oa_st_';
    public $blocks_dir = '';

    protected static $ignore_hook = array(
        'additionalCustomerFormFields',
        'displayAdminProductsExtra',
        'addWebserviceResources',
        'displayAfterBodyOpeningTag',
        'displayAfterCarrier',
        'displayAttributeForm',
        'displayAttributeGroupForm',
        'displayAttributeGroupPostProcess',
        'displayAuthenticateFormBottom',
        'dashboardData',
        'dashboardZoneOne',
        'dashboardZoneTwo',
        'displayAdminOrder',
        'displayAdminOrderContentOrder',
        'displayAdminOrderContentShip',
        'displayAdminOrderTabOrder',
        'displayAdminOrderTabShip',
        'displayAdminAfterHeader',
        'displayAdminCustomers',
        'displayAdminNavBarBeforeEnd',
        'displayAdminStatsGraphEngine',
        'displayAdminStatsGridEngine',
        'displayAdminStatsModules',
        'displayBackOfficeCategory',
        'displayBackOfficeFooter',
        'displayBackOfficeHome',
        'displayBackOfficeTop',
        'displayCreateAccountEmailFormBottom',
        'displayDashboardTop',
        'displayFeatureForm',
        'displayFeaturePostProcess',
        'displayFeatureValueForm',
        'displayFeatureValuePostProcess',
        'displayInvoice',
        'displayInvoiceLegalFreeText',
        'displayMaintenance',
        'displayOverrideTemplate',
        'displayPaymentEU',
        'displayPDFInvoice',
        'displayProductListFunctionalButtons',
        'displayProductPageDrawer',
        'paymentOptions',
        'productSearchProvider',
        'filterCategoryContent',
        'filterCmsCategoryContent',
        'filterCmsContent',
        'filterHtmlContent',
        'filterManufacturerContent',
        'filterProductContent',
        'filterProductSearch',
        'filterSupplierContent',
        'sendMailAlterTemplateVars',
        'validateCustomerFormFields',
        'displayBeforeCarrier',
        'displayCarrierExtraContent',
        'displayCarrierList',
        'displayCustomerAccountForm',
        'displayCustomerAccountFormTop',
        'displayOrderDetail',
        'displayPaymentReturn',
        'displayProductExtraContent',
        'search',
        'displayAdminProductsCombinationBottom',
        'displayAdminProductsMainStepLeftColumnBottom',
        'displayAdminProductsMainStepLeftColumnMiddle',
        'displayAdminProductsMainStepRightColumnBottom',
        'displayAdminProductsOptionsStepBottom',
        'displayAdminProductsOptionsStepTop',
        'displayAdminProductsPriceStepBottom',
        'displayAdminProductsQuantitiesStepBottom',
        'displayAdminProductsSeoStepBottom',
        'displayAdminProductsShippingStepBottom',
        'displayBeforeBodyClosingTag',
        'displayCartExtraProductActions',
        'displayCartExtraProductActions',
        'displayOrderConfirmation',
        'displayOrderConfirmation2',
    );

    public static $ignore_form_hook = array(
        'displayBackOfficeHeader',
        'Header',
    );

    public static $custom_hooks = array(
        'displaySliderContainerWidth',
        'displayCopyrightContainer',
        'displayHomeBefore',
        'displayHomeAfter',
        'displayHome',
    );

    public function __construct()
    {
        $this->name = 'oannablocks';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'OANNA';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->registerHook('ModuleRoutes');



        $this->blocks_dir = _PS_IMG_DIR_ . 'oannablocks/images/blocks/';
        $this->displayName = $this->l('OANNA Blocs');
        $this->description = $this->l('OANNA Blocs de contenu');
        $this->new =  version_compare(_PS_VERSION_, '1.7.4', '>') ? false : true;
    }

    public function renderHomeproductsForm($object)
    {
        $products = array();

        $tree = new HelperTreeCategories('associated-categories-tree-'.uniqid(), $this->l('Associated categories'));
        $tree->setRootCategory((int)Category::getRootCategory()->id)
            ->setUseCheckBox(true)
            ->setUseSearch(true);

        if (Validate::isLoadedObject($object)) {
            $formdata = $object->getFormdata();

            if (Validate::isLoadedObject($formdata)) {
                $prefix = 'additional_field_'.basename($object->template, '.tpl').'_';
                $values = array_map('intval', explode(',', $formdata->__get($prefix.'value')));
                $tree->setSelectedCategories($values);
                $products = $formdata->__get($prefix.'type') == 'category' ? array() : $values;
            }
        }

        return array(
            array(
                'type' => 'radio',
                'label' => $this->l('Type:'),
                'name' => 'type',
                'required' => true,
                'values' => array(
                    array(
                        'id' => 'category_on',
                        'value' => 'category',
                        'label' => $this->l('Category')
                    ),
                    array(
                        'id' => 'ids_on',
                        'value' => 'ids',
                        'label' => $this->l('Product IDs')
                    ),
                ),
            ),
            array(
                'type' => 'hidden',
                'name' => 'value',
                'id' => 'value'
            ),
            array(
                'type' => 'free',
                'ignore' => true,
                'label' => $this->l('Categories:'),
                'default_value' => '<div id="additional_field_#tpl#_category_tree">'.$tree->render().'</div>',
                'name' => 'category_tree'
            ),
            array(
                'type' => 'free',
                'ignore' => true,
                'default_value' => '<div id="additional_field_#tpl#_products_tree">'.$this->getProductsTree($products).'</div>',
                'name' => 'product_tree',
                'id' => 'product_tree'
            ),
            array(
                'type' => 'text',
                'ignore' => true,
                'label' => $this->l('Products:'),
                'name' => 'products_input',
                'id' => 'products_input',
                'size' => 50,
                'maxlength' => 10,
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Products count:'),
                'name' => 'products_count',
                'id' => 'products_count',
                'validator' => 'isUnsignedInt',
                'size' => 50,
                'maxlength' => 10,
                'required' => true,
                'default_value' => 3
            )
        );
    }

    public function getProductsTree($products)
    {
        $collection = new Collection('Product');
        $context = Context::getContext();

        $this->context->smarty->assign(array(
            'products' => !empty($products) ? array_map(function ($product) use ($context) {
                $id_image = Product::getCover($product->id);
                $product->name = $product->name[$context->language->id];

                if (is_array($id_image) && isset($id_image['id_image'])) {
                    $image = new Image($id_image['id_image']);
                    $product->image = _THEME_PROD_DIR_.$image->getImgPath().'.'.$image->image_format;
                }

                return $product;
            }, iterator_to_array($collection->where('id_product', 'in', $products))) : array()
        ));

        return $this->display(__FILE__, 'views/templates/admin/products_tree.tpl');
    }

    public static function getHooks()
    {
        $_hooks = Hook::getHooks();

        foreach ($_hooks as $key => $_hook) {
            if (Tools::substr($_hook['name'], 0, 6) == 'action' || in_array($_hook['name'], self::$ignore_hook)) {
                unset($_hooks[$key]);
            }
        }
        
        return $_hooks;
    }
    public function install()
    {
        if (Tools::file_exists_no_cache(_PS_MODULE_DIR_.$this->name.'/sql/install.php')) {
            include(_PS_MODULE_DIR_.$this->name.'/sql/install.php');
        } else {
            return false;
        }
        
        $install = parent::install();

        $this->_theme_path = __PS_BASE_URI__;
        $this->context->smarty->assign('theme_path', $this->_theme_path);
        
        $languages = Language::getLanguages();

        $new_tab = new Tab();
        $new_tab->class_name = 'AdminOannaBlocks';
        $new_tab->id_parent = Tab::getIdFromClassName('IMPROVE');
        $new_tab->module = $this->name;
        $new_tab->active = 1;
        $new_tab->icon = 'bookmark';
        foreach ($languages as $language) {
            $new_tab->name[$language['id_lang']] = 'OANNA Blocs';
        }
        $new_tab->add();
        
        foreach (self::getHooks() as $hook) {
            $this->registerHook($hook['name']);
        }
        
        foreach (self::$custom_hooks as $_hook) {
            $this->registerHook($_hook);
        }

        return $install
            && $this->importBlocks();
    }

    protected function importBlocks()
    {
        return !(bool)count(array_filter(glob($this->blocks_dir.'*.json'), function ($file) {
            $data = json_decode(Tools::file_get_contents($file), true);
            $block = new OannaBlock();

            $result = $block->import($data)->save();

            if ($block->id) {
                foreach ($data['children'] as $children) {
                    $_block = new OannaBlock();
                    $_block->import($children)->id_parent = $block->id;
                    $_block->save();

                    if ($_block->id) {
                        if (is_array($children['formdata'])) {
                            $formdata = new OannaBlockData();
                            $formdata->setData($children['formdata']['data'])->setIdBlock($_block->id)->save();
                        }
                    }
                }

                if (is_array($data['formdata'])) {
                    $formdata = new OannaBlockData();
                    $formdata->setData($data['formdata']['data'])->setIdBlock($block->id)->save();
                }
            }

            return false;
        }));
    }

    public function uninstall()
    {
        if (Tools::file_exists_no_cache(_PS_MODULE_DIR_.$this->name.'/sql/uninstall.php')) {
            include(_PS_MODULE_DIR_.$this->name.'/sql/uninstall.php');
        }

        $idTab = Tab::getIdFromClassName('AdminOannaBlocks');
        $deletion_tab = true;

        if ($idTab) {
            $tab = new Tab($idTab);
            $deletion_tab = $tab->delete();
        }
        
        foreach (self::getHooks() as $hook) {
            $this->unregisterHook($hook['name']);
        }

        return parent::uninstall()
            && $deletion_tab;
    }

    public function __call($function, $args)
    {
        $html = '';
        $hookName = str_replace('hook', '', $function);

        $hookStorage = array();
        foreach (Hook::getHooks() as $hook) {
            $hookStorage[] = $hook['name'];
        }

        $hookName = lcfirst($hookName);
        if (!in_array($hookName, $hookStorage)) {
            return '';
        }

        $blocks = OannaBlock::getBlocksByHookName($hookName);

        foreach ($blocks as $block) {
            $html .= $block->getContent();
        }

        /**
         * PS 1.7.0 - What is the "\PrestaShopBundle\Service\Hook\HookFinder::find" method?
         * It brakes product page when a module tries to use the "displayProductExtraContent" hook.
         * Why should it return Array type instead of String?
        **/
        if ($hookName == 'displayProductExtraContent') {
            return array();
        }

        return $html;
    }

    public static function getBlockContent($block_identifier)
    {
        return self::getBlockObject($block_identifier)->content;
    }

    public static function getBlockObject($block_identifier)
    {
        return OannaBlock::getBlockObject($block_identifier);
    }

    public function getBlock($block_identifier)
    {
        $block = OannaBlockCollection::get((int)Context::getContext()->language->id)->where('block_identifier', '=', $block_identifier)->getFirst();



        if ($block) {
            $this->context->smarty->assign('an_staticblock', $block);
            return $this->display($this->name, $block->template);
        }
        return '';
    }

    public static function isEnabledBlock($block_identifier)
    {
        return (bool)OannaBlockCollection::get((int)Context::getContext()->language->id)
            ->where('block_identifier', '=', $block_identifier)
            ->where('status', '=', 1)
            ->count();
    }

    protected function addBlockJs($basedir, $js)
    {

        if (is_array($js)) {
            $priority = isset($js['priority']) ? (int)$js['priority'] : 200;
            $position = isset($js['position']) ? (string)$js['position'] : 'bottom';
            $path = isset($js['path']) ? (string)$js['path'] : null;
            $server = isset($js['server']) && in_array((string)$js['server'], array('local', 'remote')) ? (string)$js['server'] : 'local';
        } else if (is_string($js)) {
            $priority = 200;
            $position = 'bottom';
            $path = $js;
            $server = 'local';
        }

        if ($path === null) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->context->controller->addJS($this->_path.$path);
        } else {
            $this->context->controller->registerJavascript(sha1('modules/'.$this->name.'/'.$basedir.'/'.$path), 'modules/'.$this->name.'/'.$path, array(
                'priority' => $priority,
                'position' => $position,
                'server' => $server
            ));
        }
    }

    protected function addBlockCSS($basedir, $css)
    {
        if (is_array($css)) {
            $priority = isset($css['priority']) ? (int)$css['priority'] : 200;
            $media = isset($css['media']) ? (string)$css['media'] : 'all';
            $path = isset($css['path']) ? (string)$css['path'] : null;
            $server = isset($css['server']) && in_array((string)$css['server'], array('local', 'remote')) ? (string)$css['server'] : 'local';
        } else if (is_string($css)) {
            $priority = 200;
            $media = 'all';
            $path = $css;
            $server = 'local';
        }

        if ($path === null) {
            return false;
        }
        
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->context->controller->addCSS($this->_path.$path);
        } else {
            $this->context->controller->registerStylesheet(sha1('modules/'.$this->name.'/'.$basedir.'/'.$path), 'modules/'.$this->name.'/'.$path, array(
                'priority' => $priority,
                'media' => $media,
                'server' => $server
            ));
        }
    }

    public function hookDisplayBackOfficeHeader($params = null)
    {        
        if (Dispatcher::getInstance()->getController() == 'AdminOannaBlocks') {
            $this->context->controller->addJquery();

            if (!Tools::getIsset('addoannablocks') && !Tools::getIsset('updateoannablocks')) {
                $this->context->controller->addCSS($this->_path.'views/css/back-table.css');
                $this->context->controller->addCSS($this->_path.'views/css/back.css');
                $this->context->controller->addJS($this->_path.'views/js/Sortable/Sortable.min.js');
                $this->context->controller->addJS($this->_path.'views/js/sorting.min.js');
                $this->context->controller->addJS($this->_path.'views/js/back.js');
            }
        }
    }

    public function hookDisplayHeader($params = null)
    {
        foreach (OannaBlock::getActive() as $block) {
            $basedir = pathinfo($block->template, PATHINFO_DIRNAME);

            foreach ($block->getConfigJS() as $js) {
                $this->addBlockJs($basedir, $js);
            }
                
            foreach ($block->getConfigCSS() as $css) {
                $this->addBlockCSS($basedir, $css);
            }
        }

        $this->context->controller->addJS($this->_path.'views/js/owl.carousel.min.js');

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->context->controller->addCSS($this->_path.'views/css/front.css');
        } else {
            $this->context->controller->registerStylesheet('modules-an-staticblock', 'modules/'.$this->name.'/views/css/front.css', array('media' => 'all', 'priority' => 200));
        }
    }



    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        if(isset($configuration['alias'])) {
            $block =  OannaBlock::getBlockByAlias($configuration['alias']);
        }else if(isset($configuration['id'])) {
            $block =  OannaBlock::getBlockById($configuration['id']);
        }

        //        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $block->getContent();
    }

    public function hookModuleRoutes($params)
    {
        $routes = array(
            // Single
            'module-oannablocks-detail' => array(
                'controller' => 'detail',
                'rule' => 'les-collections{/:id}-{:name}',
                'keywords' => array(
                    'id' => array(
                        'regexp' => '[0-9]+',
                        'param' => 'id',
                    ),
                    'name' => array(
                        'regexp' => '[_a-zA-Z0-9-\pL]*',
                        'param' => 'name',
                    ),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'oannablocks',
                ),
            ),
        );

        return $routes;
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
//        $address = $this->context->shop->getAddress();
//
//        $is_state_multilang = !empty(State::$definition['multilang']);
//        $state_name = (new State($address->id_state))->name;
//
//        $contact_infos = [
//            'company' => Configuration::get('PS_SHOP_NAME'),
//            'address' => [
//                'formatted' => AddressFormat::generateAddress($address, array(), '<br />'),
//                'address1' => $address->address1,
//                'address2' => $address->address2,
//                'postcode' => $address->postcode,
//                'city' => $address->city,
//                'state' => $is_state_multilang ? $state_name[$this->context->language->id] : $state_name,
//                'country' => (new Country($address->id_country))->name[$this->context->language->id],
//            ],
//            'phone' => Configuration::get('PS_SHOP_PHONE'),
//            'fax' => Configuration::get('PS_SHOP_FAX'),
//            'email' => Configuration::get('PS_SHOP_EMAIL'),
//        ];
//
//        return [
//            'contact_infos' => $contact_infos,
//            'display_email' => Configuration::get('PS_CONTACT_INFO_DISPLAY_EMAIL'),
//        ];
    }
}
