<?php
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

class OannaBlock extends ObjectModel
{
    public $id;

    /** @var integer block ID */
    public $id_oannablock;

    public $id_parent;

    /** @var string Title */
    public $title;

    /** @var string Identifier */
    public $block_identifier;

    /** @var boolean Status for display */
    public $status = 1;

    public $hook_ids;
    
    public $position;

    /** @var string Content */
    public $content;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    public $link;

    public $image;


    public $template;

    public $alias;

    public $img;

    protected $config;

    public $products = array();

    public $formdata = array();

    public $page_number = 0;
    public $nb_products = 25;
    public $limit = 25;

    public static $definition = array(
        'table' => 'oannablock',
        'primary' => 'id_oannablock',
        'multilang' => true,
        'fields' => array(
            'block_identifier' => array('type' => self::TYPE_STRING, 'size' => 50),
            'id_parent' => array('type' => self::TYPE_INT),
            'status' => array('type' => self::TYPE_INT),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE),
            'date_upd' => array('type' => self::TYPE_DATE),
            'hook_ids' => array('type' => self::TYPE_STRING),
            'alias' => array('type' => self::TYPE_STRING),
            'template' => array('type' => self::TYPE_STRING),

            'img' => array('type' => self::TYPE_STRING),

            // Lang fields
            'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 128),
            'content' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
            'image' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),

            'link' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 3999999999999),
        ),
        'associations' => array(
            'OannaBlockData' => array('object' => 'OannaBlockData', 'field' => 'id_oannablock', 'foreign_field' => 'id_oannablock')/*,
            'OannaBlockShop' => array('object' => 'OannaBlockShop', 'field' => 'id_oannablock', 'foreign_field' => 'id_oannablock')*/
        )
);

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        return $this->loadFormdata();
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (empty($this->block_identifier)) {
            $this->block_identifier = uniqid();
        }
    
        return parent::add($auto_date, $null_values);
    }

    public function setFormdata($data)
    {
        $this->formdata = $data;
        return $this;
    }

    public function getFormdata()
    {
        return $this->formdata;
    }

    public static function getViewsDir()
    {
        $path = _PS_THEME_DIR_.'modules/oannablocks/views/templates/front/';
        if (!is_dir($path)) {
            $path = _PS_MODULE_DIR_.'oannablocks/views/templates/front/';
        }

        return $path;
    }

    public function getField($field, $default_value = false)
    {
        return !array_key_exists($field, $this->formdata) ? $default_value : $this->formdata[$field];
    }
    
    public static function getTemplates($child = false, $object = null)
    {
        try {
            return array_map(function ($file) use ($child, $object) {
                return OannaBlock::getTemplateInfo($file, $child, $object);
            }, iterator_to_array(self::getTemplatesLow($child)), array()); //fix keys
        } catch (Exception $e) {
            return array();
        }
    }

    public static function getTemplateInfo($file, $child = false, $object = null, $load_form = true)
    {
        $views_dir = self::getViewsDir();
        $basename = $file->getBasename('.tpl');
        $config = self::getDefaultConfig();
        $config['basename'] = $basename;
        $path = $file->getPath();


        if (!is_dir($path)) {
            $path = _PS_THEME_DIR_.'modules/oannablocks/'.$path;
            if (!is_dir($path)) {
                $path = _PS_MODULE_DIR_.'oannablocks/'.$path;
            }
        }



        $config = $child === false ?
            array_merge($config, (array)OannaSpyc::YAMLLoad(Tools::file_get_contents($path.'/config.yml'))) :
            array_merge($config, (array)OannaSpyc::YAMLLoad(Tools::file_get_contents($path.'/'.$basename.'.yml')));



        if ($load_form === true) {
            if (isset($config['homeproducts']) && $config['homeproducts'] === true) {
                $config['fields'] = isset($config['fields']) ? array_merge($config['fields'], Module::getInstanceByName('oannablocks')->renderHomeproductsForm($object)) : Module::getInstanceByName('oannablocks')->renderHomeproductsForm($object);
            }

            if (isset($config['fields'])) {
                $config['fields'] = array_map(function ($field) use ($file) {
                    $basename = $file->getBasename('.tpl');

                    $field['ignore'] = isset($field['ignore']) ? (bool)$field['ignore'] : false;
                    $field['name'] = 'additional_field_'.$basename.'_'.$field['name'];

                    if (isset($field['default_value'])) {
                        $field['default_value'] = str_replace('#tpl#', $basename, $field['default_value']);
                    }

                    return $field;
                }, $config['fields']);
            }
        }

        return array(
            'file' => 'views/templates/front/'.basename($file->getPath()).'/'.$file->getBasename(),
            'name' =>  isset($config['name']) ? $config['name'] : $file->getBasename('.tpl'),
            'basename' => $basename,
            'config' => $config,
            'preview' => Tools::file_exists_no_cache($file->getPath().'/preview.png') ? true : false
        );
    }

    public static function getDefaultConfig()
    {
        return array(
            'enabled_text' => true,
            'enabled_link' => true,
            'enabled_image' => true,
            'enabled_dates' => false,
            'required_text' => false,
            'required_dates' => false,
            // 'name' => '',
            'description' => '',
            'homeproducts' => false,
            'js' => array(),
            'css' => array()
        );
    }

    public static function getTemplatesLow($child)
    {
        $finder = new OannaFinder(new GlobIterator($child === false ? self::getViewsDir().'/*/*.tpl' : self::getViewsDir().'/'.basename($child, '.tpl').'/*.tpl', FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::SKIP_DOTS));
        return $finder->setChild($child);
    }

    public function loadFormdata($id_lang = null)
    {
        if ($this->id > 0) {
            $collection = new Collection('OannaBlockData', $id_lang);
            $this->formdata = $collection->where('id_oannablock', '=', $this->id)->getFirst();
        }

        return $this;
    }

    public function export()
    {
        $formdata = !($this->formdata instanceof OannaBlockData) ? false : $this->formdata->export();

        if (is_array($formdata)) {
            $basename = basename($this->template, '.tpl');
            $prefix = 'additional_field_'.$basename;
            
            if (isset($formdata[$prefix.'_type']) && in_array($formdata[$prefix.'_type'], array('category', 'ids'))) {
                $formdata[$prefix.'_type'] = 'new';
                $formdata[$prefix.'_value'] = '';
            }
        }

        return array(
            'id_oannablock' => $this->id_oannablock,
            'block_identifier' => $this->block_identifier,
            'id_parent' => $this->id_parent,
            'status' => $this->status,
            'position' => $this->position,
            'date_add' => $this->date_add,
            'date_upd' => $this->date_upd,
            'hook_ids' => $this->hook_ids,
            'template' => $this->template,
            'alias' => $this->alias,
            'img' => $this->img,

            // Lang fields
            'title' => is_array($this->title) ? current($this->title) : '',
            'content' => is_array($this->content) ? current($this->content) : '',
            'image' => is_array($this->image) ? current($this->image) : '',

            'link' => is_array($this->link) ? current($this->link) : '',
            'formdata' => $formdata
        );
    }

    public function import(array $data)
    {
        $this->id_oannablock = isset($data['id_oannablock']) ? $data['id_oannablock'] : $this->id_oannablock;
        $this->block_identifier = isset($data['block_identifier']) ? $data['block_identifier'] : $this->block_identifier;
        $this->id_parent = isset($data['id_parent']) ? $data['id_parent'] : $this->id_parent;
        $this->status = isset($data['status']) ? $data['status'] : $this->status;
        $this->position = isset($data['position']) ? $data['position'] : $this->position;
        $this->date_add = isset($data['date_add']) ? $data['date_add'] : $this->date_add;
        $this->date_upd = isset($data['date_upd']) ? $data['date_upd'] : $this->date_upd;
        $this->hook_ids = isset($data['hook_ids']) ? $data['hook_ids'] : $this->hook_ids;
        $this->template = isset($data['template']) ? $data['template'] : $this->template;
        $this->alias = isset($data['alias']) ? $data['alias'] : $this->alias;
        $this->img = isset($data['img']) ? $data['img'] : $this->img;
        
        $languages = array_flip(Language::getLanguages(1, 0, 1));

        if (isset($data['title']) && is_string($data['title'])) {
            $this->title = array_map(function () use ($data) {
                return $data['title'];
            }, $languages);
        }
        
        if (isset($data['content']) && is_string($data['content'])) {
            $this->content = array_map(function () use ($data) {
                return $data['content'];
            }, $languages);
        }
        
        if (isset($data['image']) && is_string($data['image'])) {
            $this->image = array_map(function () use ($data) {
                return $data['image'];
            }, $languages);
        }



        if (isset($data['link']) && is_string($data['link'])) {
            $this->link = array_map(function () use ($data) {
                return $data['link'];
            }, $languages);
        }

        if (isset($data['formdata']) && $data['formdata']) {
            $formdata = new OannaBlockData();
            $this->formdata = $formdata->import($data['formdata']);
        }

        return $this;
    }

    public function setPosition($position)
    {
        $this->position = (int)$position;
        return $this;
    }

    public function getConfigJS()
    {
        return isset($this->prepareConfig()->config['js']) ? $this->config['js'] : array();
    }

    public function getConfigCSS()
    {
        return isset($this->prepareConfig()->config['css']) ? $this->config['css'] : array();
    }

    public function getConfig()
    {
        return $this->prepareConfig()->config;
    }

    protected function prepareConfig()
    {
        if ($this->config === null && isset($this->template)) {
            $_part = explode('/', $this->template);

            $config_file = self::getViewsDir().basename($_part[count($_part)-1], '.tpl').'/config.yml';
            $this->config = Tools::file_exists_no_cache($config_file) ? (array)OannaSpyc::YAMLLoad(Tools::file_get_contents($config_file)) : array();
        }

        return $this;
    }

    public function hydrate(array $data, $id_lang = null)
    {
        parent::hydrate($data, $id_lang);
        return $this->loadFormdata($id_lang);
    }

    public function generateIdentifier()
    {
        if (!$this->block_identifier) {
            $title = $this->title;
            if (is_array($title)) {
                $title = current($title);
            }
            $title = preg_replace("/[^a-zA-Z0-9]+/", "_", $title);
            $this->block_identifier = (int)$this->id_parent . '_' . $title . '_' . time();
        }
    }

    public function useDataAsArray($field, array $data = array())
    {
        if (empty($data)) {
            return explode(',', $this->$field);
        }

        $this->$field = implode(',', $data);
    }

    public static function getBlockByIdentifier($block_identifier)
    {
        $sql = '
        SELECT `id_oannablock`
        FROM `' . _DB_PREFIX_ . 'oannablock`
        WHERE `block_identifier` = "' . pSQL($block_identifier) . '";';

        $block_id = (int)Db::getInstance()->getValue($sql);
        $_block = new self((int)$block_id);

        if ($_block->id) {
            return $_block;
        }
        
        return false;
    }

    public static function getBlockByAlias($block_alias)
    {
        $sql = '
        SELECT `id_oannablock`
        FROM `' . _DB_PREFIX_ . 'oannablock`
        WHERE `alias` = "' . pSQL($block_alias) . '";';
        $block_id = (int)Db::getInstance()->getValue($sql);
        $_block = new self((int)$block_id, Context::getContext()->cookie->id_lang);


        if ($_block->id) {
            return $_block;
        }

        return false;
    }

    public static function getBlockById($block_id)
    {
        $sql = '
        SELECT `id_oannablock`
        FROM `' . _DB_PREFIX_ . 'oannablock`
        WHERE `id_oannablock` = "' . pSQL($block_id) . '";';
        $block_id = (int)Db::getInstance()->getValue($sql);
        $_block = new self((int)$block_id, Context::getContext()->cookie->id_lang);
        if ($_block->id) {
            return $_block;
        }
        return false;
    }

    public static function getEnabledBlockByIdentifier($block_identifier)
    {
        $sql = '
        SELECT `id_oannablock`
        FROM `' . _DB_PREFIX_ . 'oannablock`
        WHERE `status`=1 AND `block_identifier` = "' . pSQL($block_identifier) . '";';

        $block_id = (int)Db::getInstance()->getValue($sql);
        $_block = new self((int)$block_id);
        
        if ($_block->id) {
            return $_block;
        } else {
            return false;
        }
    }

    public static function getActive()
    {
        if (Shop::isFeatureActive()) {
            $r =  Db::getInstance()->executeS('
            SELECT tb.id_oannablock
            FROM `' . _DB_PREFIX_ . 'oannablock` tb
            LEFT JOIN `' . _DB_PREFIX_ . 'oannablock_shop` ts ON (tb.`id_oannablock` = ts.`id_oannablock`)
            WHERE  ts.`id_shop` = ' . (int) Context::getContext()->shop->id . ' AND tb.`status` = 1 AND tb.`id_parent` = 0');
        } else {
            $r =  Db::getInstance()->executeS('
            SELECT tb.id_oannablock
            FROM `' . _DB_PREFIX_ . 'oannablock` tb
            WHERE  tb.`status` = 1 AND tb.`id_parent` = 0');
        }

        foreach($r as &$result) {
            $result = $result['id_oannablock'];
        }

        $hooks = new Collection(__CLASS__, Context::getContext()->language->id);

        if(empty($r)) {
            return array();
        }
        $hooks->where('id_oannablock', 'in', $r);

        $hooks->orderBy('position');
        return $hooks;
    }

    public static function getBlocksByHookName($hookName)
    {
        $hooks = array();
        if ($hookName) {
            $hooks = new Collection(__CLASS__, Context::getContext()->language->id);
            $hooks = iterator_to_array($hooks->where('status', '=', 1)
                ->where('id_parent', '=', 0)
                ->where('hook_ids', 'like', '%' . pSQL($hookName) . '')
                ->orderBy('position'));
            if (Shop::isFeatureActive()) {
                foreach ($hooks as $key => $hook) {
                    $db = Db::getInstance()->executeS(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'oannablock_shop`
                        WHERE `id_oannablock` = "' . (int)$hook->id . '"
                        AND `id_shop` = "' . (int)Context::getContext()->shop->id . '"'
                    );
                    if (!count($db) || !count($db[0])) {
                        unset($hooks[$key]);
                    }
                }
            }
        }
        return $hooks;
    }

    public static function getBlockObject($block_identifier)
    {
        if (Module::isEnabled('oannablocks')) {
            $sql = '
            SELECT `id_oannablock`
            FROM `' . _DB_PREFIX_ . 'oannablock`
            WHERE `block_identifier` = "' . pSQL($block_identifier) . '" AND `status` = "1"';

            if (Shop::isFeatureActive()) {
                $sql .= ' AND `id_oannablock` IN (
                    SELECT sa.`id_oannablock`
                    FROM `' . _DB_PREFIX_ . 'oannablock_shop` sa
                    WHERE sa.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')
                )';
            }

            $block_id = (int)Db::getInstance()->getValue($sql);

            if ($block_id) {
                $block = new self($block_id, Context::getContext()->cookie->id_lang);
                return $block;
            }
        }

        return new self;
    }

    public static function getRootImagesPath()
    {
        $path = _PS_IMG_DIR_ . 'oannablocks/images/';

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }

    public function getImage()
    {
        //return path from root folser
        return self::getRootImagesPath().$this->img.'.jpg';
    }

    public function getImagesDirectory()
    {
        return __PS_BASE_URI__.'img/oannablocks/images/';
    }

    public function getImageLink()
    {
        if (file_exists($this->getImage())) {
            return __PS_BASE_URI__.'img/oannablocks/images/'.$this->img.'.jpg';
        }

        return false;
    }

    public function getImagePath()
    {
        return self::getRootImagesPath();
    }

    public function getBeginDate() {
        $begin = $this->loadFormdata()->formdata;
        if ($begin && $begin->__get('additional_field_item_begin_date')) {
            return $begin->__get('additional_field_item_begin_date');
        }
        return null;
    }

    public function getEndDate() {
        $end = $this->loadFormdata()->formdata;
        if ($end && $end->__get('additional_field_item_end_date')) {
            return $end->__get('additional_field_item_end_date');
        }
        return null;
    }

    public function isInDates() {
        $begin = $this->getBeginDate() ? new DateTime($this->getBeginDate()) : null;
        $end = $this->getEndDate() ? new DateTime($this->getEndDate()) : null;
        $now = new DateTime();

        if ($begin){
            if ($end) {
                if ($now > $begin && $now < $end) {
                    return true;
                }
            }
            else {
                if ($now > $begin) {
                    return true;
                }
            }
        }

        if ($end && !$begin) {
            if ($now < $end) {
                return true;
            }
        }

        if (!$begin && !$end) {
            return true;
        }

        return false;
    }


    public function delete()
    {
        $block_identifier = $this->block_identifier;
        
        if (parent::delete() !== false) {
            if (Tools::file_exists_no_cache(_PS_IMG_DIR_.'oannablocks/blocks/'.$block_identifier.'.json')) {
                @unlink(_PS_IMG_DIR_.'oannablocks/blocks/'.$block_identifier.'.json');
            }

            return !count(array_filter(array_map(function ($block) {
                return !$block->delete();
            }, iterator_to_array($this->getChildrenBlocks(true)))))
            && $this->deleteImage();
        }

        return false;
    }

    public function deleteImage($force_delete = false)
    {

        $this->image_dir = OannaBlock::getRootImagesPath();
        $this->image_format = 'jpg';

        $field = Tools::getValue('deleteImage');
        if ($field === '1') {
            $field = 1;
        }

        if (empty($field)) {
            return false;
        }

        if (!isset($this->img) && !is_string($field)) {
            return false;
        }

        if (is_string($field)) {
            $imageToDelete = $this->formdata->{$field};
        }
        else {
            $imageToDelete = $this->img.'.'.$this->image_format;
        }

        if ($imageToDelete) {
            if (file_exists($this->image_dir.$imageToDelete)
                && !unlink($this->image_dir.$imageToDelete)) {
                return false;
            }

            if (file_exists(_PS_TMP_IMG_DIR_.$this->def['table'].'_'.$imageToDelete)
                && !unlink(_PS_TMP_IMG_DIR_.$this->def['table'].'_'.$imageToDelete)) {
                return false;
            }

            if (file_exists(_PS_TMP_IMG_DIR_.$this->def['table'].'_mini_'.$imageToDelete)
                && !unlink(_PS_TMP_IMG_DIR_.$this->def['table'].'_mini_'.$imageToDelete)) {
                return false;
            }

            $types = ImageType::getImagesTypes();

            foreach ($types as $image_type) {
                if (file_exists($this->image_dir.$this->img.'-'.Tools::stripslashes($image_type['name']).'.'.$this->image_format)
                    && !unlink($this->image_dir.$this->img.'-'.Tools::stripslashes($image_type['name']).'.'.$this->image_format)) {
                    return false;
                }
            }
        }
        if (is_string($field)) {
            $this->save();

            $oannaBlockData = $this->formdata;
            $oannaBlockData->__set($field, '');
            $oannaBlockData->save();
        }
        else {
            $this->img = '';
        }

        return $this->save();
    }

    public function getChildrenBlocks($delete = false)
    {
        if (!$this->id) {
            return array();
        }

        $childrenBlocks = new Collection(__CLASS__, Context::getContext()->language->id);
        $childrenBlocksResult = $childrenBlocks->where('id_parent', '=', $this->id)->orderBy('position')->where('status', '=', 1);
        if (Shop::isFeatureActive() && !$delete) {
            foreach ($childrenBlocksResult as $key => $block) {
                $db = Db::getInstance()->executeS(
                    'SELECT *
                                FROM `' . _DB_PREFIX_ . 'oannablock_shop`
                                WHERE `id_oannablock` = "' . (int)$block->id . '"
                                AND `id_shop` = "' . (int)Context::getContext()->shop->id . '"'
                );
                if(!count($db) || !count($db[0])) {
                    unset($childrenBlocksResult[$key]);
                }
            }
        }
        return $childrenBlocksResult;
    }

    public function getContent($param = array())
    {
        if (!$this->template) {
            return $this->content;
        }

        if (Validate::isLoadedObject($this->formdata)) {
            $config = self::getTemplateInfo(new SplFileInfo($this->template), (bool)(int)$this->id_parent, $this, false);

            if (isset($config['config']['homeproducts'])) {
                $this->products = $this->getProducts();
            }
        }

        $this->param = $param;

        $imgplaceholder = '';
        if (isset($config['config']['placeholder']) || !empty($config['config']['placeholder'])) {
            $imgplaceholder = __PS_BASE_URI__ . 'modules/oannablocks/' . $config['config']['placeholder'];
        }

        Context::getContext()->smarty->assign(array(
            'an_staticblock'=> $this,
            'an_placeholder' => $imgplaceholder
        ));
        return Module::getInstanceByName('oannablocks')->display('oannablocks', $this->template);
    }

    public function getPrefix()
    {
        return 'additional_field_'.basename($this->template, '.tpl').'_';
    }

    //TODO: refactor all calls
    public function getAdditionalData($key)
    {
        return $this->formdata->__get($this->getPrefix().$key);
    }

    public function getProducts()
    {
        $context = Context::getContext();
        $prefix = $this->getPrefix();
        $page_number = (int) $this->page_number;
        $nb_products = (int) $this->nb_products;
        $products_count = (int) $this->getAdditionalData('products_count');
        $factor = $page_number * $nb_products;


        if ($nb_products + $factor > $products_count) {
            $this->limit = $products_count - $factor;
        }



        $values = explode(',',  $this->getAdditionalData('value'));
        $values = array_map('trim', $values);
        $values = array_map('intval', $values);



        $method = 'getBy' . $this->getAdditionalData('type');


        if (method_exists($this, $method)) {
            $products = $this->$method($values);

            if (is_array($products)) {
                if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                    return $products;
                } else {
                    include_once _PS_MODULE_DIR_ . 'oannablocks/classes/OannaBlocksListing.php';
                    $listing = new OannaBlocksListing();
                    return $listing->prepare($products);
                }
            }
        }

        return array();
    }

    protected function getByIds($ids)
    {
        if (!count($ids)) {
            return array();
        }

        $context = Context::getContext();
        $id_lang = $context->language->id;
        $nb_days_new_product = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!Validate::isUnsignedInt($nb_days_new_product)) {
            $nb_days_new_product = 20;
        }

        $sql = 'SELECT DISTINCT p.*, product_shop.*, stock.out_of_stock, IFnull(stock.quantity, 0) AS quantity' . (Combination::isFeatureActive() ? ', IFnull(product_attribute_shop.id_product_attribute, 0) AS id_product_attribute,
                    product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity' : '') . ', pl.`description`, pl.`description_short`, pl.`available_now`,
                    pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, image_shop.`id_image` id_image,
                    il.`legend` as legend, m.`name` AS manufacturer_name, cl.`name` AS category_default,
            DATEDIFF(product_shop.`date_add`, DATE_SUB("' . date('Y-m-d') . ' 00:00:00",
            INTERVAL ' . (int) $nb_days_new_product . ' DAY)) > 0 AS new, product_shop.price AS orderprice
        FROM `' . _DB_PREFIX_ . 'category_product` cp
        LEFT JOIN `' . _DB_PREFIX_ . 'product` p
            ON p.`id_product` = cp.`id_product`
        ' . Shop::addSqlAssociation('product', 'p') .
            (Combination::isFeatureActive() ? ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop
        ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int) $context->shop->id . ')' : '') . '
        ' . Product::sqlStock('p', 0) . '
        LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
            ON (product_shop.`id_category_default` = cl.`id_category`
            AND cl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
            ON (p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
            ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $context->shop->id . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il
            ON (image_shop.`id_image` = il.`id_image`
            AND il.`id_lang` = ' . (int) $id_lang . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m
            ON m.`id_manufacturer` = p.`id_manufacturer`
        WHERE product_shop.`id_shop` = ' . (int) $context->shop->id . '
            AND p.`id_product` IN (' . implode(', ', $ids) . ')'
            . ' AND product_shop.`active` = 1';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);

        return Product::getProductsProperties($id_lang, $result);
    }

    public function getByCategory(array $category_ids)
    {

        if (!count($category_ids)) {
            return array();
        }

        $page_number = $this->page_number;
        $limit = $this->limit;
        $context = Context::getContext();
        $products = array();

        foreach ($category_ids as $id_category) {

            $category = new Category((int) $id_category);
            $_products = $category->getProducts($context->language->id, $page_number, $limit);
            if ($_products) {
                $products = array_merge($products, $_products);
                $products = array_unique($products, SORT_REGULAR);
            }

            if (count($products) < $limit){
                $limit = $limit - count($products);
            } else {
                break;
            }
        }

        return $products;
    }

}
