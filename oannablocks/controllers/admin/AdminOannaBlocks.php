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

require_once _PS_MODULE_DIR_.'oannablocks/classes/OannaBlockCollection.php';

class AdminOannaBlocksController extends ModuleAdminController
{
    private $views_dir = '';

    protected $_errors = array(); //non-critical errors

    protected $view_parent_id;
    protected $add_parent_id;

    protected $position_identifier = 'id_oannablock';

    public $fieldImageSettings = array(
        'name' => 'image',
        'dir' => ''
    );

    protected $_defaultOrderBy = 'position';
    protected $_defaultOrderWay = 'ASC';

    public function __construct()
    {
        $this->pagination = array(1000);
        $this->default_pagination = 1000;

        $part = 'views'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'front';
        $path = $this->getOannablocksPath($part);

        $this->views_dir = $path;
        $this->bootstrap = true;
        $this->table = 'oannablock';
        $this->identifier = 'id_oannablock';
        $this->className = 'OannaBlock';
        $this->lang = true;
        $this->view_parent_id = (int)Tools::getValue('id_oannablock');
        $this->add_parent_id = (int)Tools::getValue('id_parent');

        if (!$this->view_parent_id) {
            $this->addRowAction('view');
        }

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->fields_list = array(
            'id_oannablock' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30,
				'search'  => false,
                'orderby' => false
            ),

            'title' => array(
                'title' => $this->l('Block Title'),
                'width' => 200,
				'search'  => false,
                'orderby' => false
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'width' => 30,
                // 'position' => true,
				'search'  => false,
                'type' => 'position'
            ),
            'template' => array(
                'title' => $this->l('Template'),
                'width' => 30,
				'search'  => false,
                'orderby' => false
            ),
            'image' => array(
                'title' => $this->l('Image'),
                'width' => 30,
                'type' => 'image',
				'search'  => false,
                'orderby' => false
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'width' => 40,
                'active' => 'update',
                'align' => 'center',
                'type' => 'bool',
				'search'  => false,
                'orderby' => false
            )
        );

        //on masque la colonne d'alias dans les enfants
        if (!$this->view_parent_id) {
            $this->fields_list['alias'] = array(
                'title' => $this->l('Alias'),
                'width' => 30,
                'search' => false,
                'orderby' => false
            );
        }

        if ($this->view_parent_id == 0) {
            $this->fields_list['hook_ids'] = array(
                'title' => $this->l('Hooks'),
                'width' => 150,
                'align' => 'right',
                'orderby' => false,
                'search' => false
            );

        }

        $this->fields_list['date_upd'] = array(
            'title' => $this->l('Last Modified'),
            'width' => 150,
            'type' => 'date',
            'align' => 'right',
			'search'  => false,
            'orderby' => false
        );

        if ($this->view_parent_id != 0) {
            $this->fields_list['begin_date'] = array(
                'title' => $this->l('Date de début'),
                'width' => 150,
                'type' => 'datetime',
                'align' => 'right',
                'search'  => false,
                'orderby' => false
            );
            $this->fields_list['end_date'] = array(
                'title' => $this->l('Date de fin'),
                'width' => 150,
                'type' => 'datetime',
                'align' => 'right',
                'search'  => false,
                'orderby' => false
            );
        }


            $this->_where .= ' AND a.id_parent = ' . $this->view_parent_id . ' ';

        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
            $this->_where .= ' AND a.' . $this->identifier . ' IN (
                SELECT sa.' . $this->identifier . '
                FROM `' . _DB_PREFIX_ . $this->table . '_shop` sa
                WHERE sa.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')
            )';
        }

        $this->identifiersDnd = array('id_oannablock' => 'id_sslide_to_move');
    }

    public function initToolbarTitle()
    {
        if ($this->view_parent_id && $this->display == 'view') {
            $obj = $this->loadObject(true);
            $title = $obj->title[$this->context->language->id];
            $this->toolbar_title[] = $this->l('View children of ', null, null, false) . $title;
            $this->addMetaTitle($this->l('View children of ', null, null, false) . $title);
        } else {
            parent::initToolbarTitle();
        }
    }

    public function addMetaTitle($entry)
    {
        // Only add entry if the meta title was not forced.
        if (is_array($this->meta_title)) {
            $this->meta_title[] = $entry;
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();

        $this->toolbar_btn['new'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token.'&id_parent='.$this->view_parent_id,
            'desc' => $this->l('Add new')
        );
    }

    protected function getErrors()
    {
        $errors = json_decode(urldecode(Tools::getValue('errors', '')), true);

        return is_array($errors) ? '<div class="alert alert-danger">'.implode('<br>', $errors).'</div>' : '';
    }

    public function renderList() {
        return parent::renderList();
    }

    public function renderView()
    {
        return $this->renderList();
    }

    public function renderForm()
    {
        $this->display = 'edit';
        $this->initToolbar();

        if (!$obj = $this->loadObject(true)) {
            return;
        }

        //Gestion si l'objet n'existe pas encore en DB
        if (!$obj->id) {
            $obj->id_parent = $this->add_parent_id;

            if ($obj->id_parent == 0) {

                //Si un template a deja été setté, on l'utilise
                if (Tools::getIsset('template')) {


                    $template = Tools::getValue('template');
                    $template = 'views/templates/front/'.$template.'/'.$template.'.tpl';
                    $path = $this->getOannablocksPath();

                    if (Tools::file_exists_no_cache($path.$template)) {
                        //path du template file
                        $obj->template = $template;
                    }
                }
            }
        }

        //Gestion de l'image principale du oannaBlock
        $image = $obj->getImage();
        $image_url = false;
        $thumb_size = false;
        if (file_exists($image)) {

            $image_url = '<img src="' .$obj->getImagesDirectory(). $obj->img . '.jpg?rand='.rand(1, 1000).'" alt="" class="imgm img-thumbnail" />';
            $thumb_size = filesize($image) / 1000;
        }


        //Gestion des hooks
        $ignore = Oannablocks::$ignore_form_hook;
        $hooks = array_filter(array_merge(
            array(
                array(
                    'name' => '',
                    'title' => ''
                )
            ),
            Oannablocks::getHooks()
        ), function ($hook) use ($ignore) {
            return !in_array($hook['name'], $ignore);
        });


        //on récupère tous les blocs liés au bloc parent (si nécessaire)
        $parentBlocks = new Collection('OannaBlock', $this->context->language->id);
        $parentBlocks->where('id_parent', '=', 0);
        if ($obj->id_parent) {
            $parentBlocks->where('id_oannablock', '=', $obj->id_parent);
        } else if ($obj->id) {
            $parentBlocks->where('id_oannablock', '!=', $obj->id);
        }

        $parentBlocks = array_merge(
            array(
                array(
                    'id_oannablock' => '',
                    'title' => ''
                )
            ),
            $parentBlocks->getResults()
        );


        if ($obj->id_parent) {
            array_shift($parentBlocks);
        }

        $_child = false;

        if (isset($parentBlocks[0]) && $parentBlocks[0] instanceof OannaBlock && isset($parentBlocks[0]->template)) {
            $_tpl_part = explode('/', $parentBlocks[0]->template);
            $_child = $_tpl_part[count($_tpl_part)-1];
        }

        $templates = OannaBlock::getTemplates($_child !== false ? $_child : false, $this->object);

        $path = $this->getOannablocksPath();
        $template_info = OannaBlock::getTemplateInfo(new SplFileInfo($path.$obj->template), $_child !== false ? $_child : false, $this->object);
        $additional_fields = array();



        if ($obj->id_parent == 0) {
            if (isset($template_info['config']['fields'])) {
                $this->setAdditionalFileFields($template_info['config']['fields'], $obj);
                $additional_fields = array_merge($additional_fields, $template_info['config']['fields']);
            }
        } else {
            foreach ($templates as $tpl) {
                if (isset($tpl['config']['fields'])) {
                    $this->setAdditionalFileFields($tpl['config']['fields'], $obj);
                    $additional_fields = array_merge($additional_fields, $tpl['config']['fields']);

                }
            }
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Static Block'),
                'image' => '../img/admin/add.gif'
            ),
            'input' => array_merge(array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title:'),
                    'name' => 'title',
                    'id' => 'title',
                    'lang' => true,
                    'required' => true,
                    'size' => 50,
                    'maxlength' => 50,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Alias:'),
                    'name' => 'alias',
                    'id' => 'alias',
                    'lang' => false,
                    'size' => 50,
                    'maxlength' => 50,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enabled:'),
                    'name' => 'status',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(array(
                        'id' => 'is_enabled_on',
                        'value' => 1), array(
                        'id' => 'is_enabled_off',
                        'value' => 0)
                    )
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Parent Block:'),
                    'name' => 'id_parent',
                    'index' => 'id_parent',
                    'style' => 'width:100px;',
                    'class' => 'fixed-width-xxl',
                    'options' => array(
                        'query' => $parentBlocks,
                        'id' => 'id_oannablock',
                        'name' => 'title',
                    ),
                ),
                array(
                    'type' => 'hidden',
                    'label' => $this->l('Position:'),
                    'name' => 'position',
                    'id' => 'position',
                    'size' => 50,
                    'maxlength' => 10,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('PrestaShop Hook:'),
                    'name' => 'hook_ids[]',
                    'index' => 'hook_ids[]',
                    'style' => 'width:100px;',
                    'class' => 'fixed-width-xxl',
                    'options' => array(
                        'query' => $hooks,
                        'id' => 'name',
                        'name' => 'name',
                    ),
                    'desc' => $this->l("Leave empty if you dont want to assign the block to a standard hook."),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Link:'),
                    'name' => 'link',
                    'id' => 'link',
                    'lang' => true,
                ),
                array(
                   'type' => 'file',
                   'label' => $this->l('Image'),
                   'name' => 'image',
                   'display_image' => true,
                   'image' => $image_url,
                   'size' => $thumb_size,
                   'format' => version_compare(_PS_VERSION_, '1.7.0.0', '<') ? ImageType::getFormatedName('medium') : ImageType::getFormattedName('medium'),
                   'delete_url' => self::$currentIndex.'&'.$this->identifier.'='.$obj->id.'&token='.$this->token.'&deleteImage=1',
                ),
                array(
                    'type' => empty($templates) || count($templates) == 1 ? 'hidden' : 'select',
                    'label' => $this->l('Template:'),
                    'name' => 'template',
                    'index' => 'template',
                    'style' => 'width:100px;',
                    'class' => 'fixed-width-xxl',
                    'options' => array(
                        'query' => $templates,
                        'id' => 'file',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Content:'),
                    'name' => 'content',
                    'autoload_rte' => true,
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                ),
            ), $additional_fields),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title' => $this->l('Save'),
                    'icon' => 'process-icon-save',
                    'class' => 'pull-right',
                    'name' => 'submitAdd'.$this->table.'AndBackToParent'
                ),
                array(
                    'type' => 'submit',
                    'title' => $this->l('Save and stay'),
                    'icon' => 'process-icon-save',
                    'class' => 'pull-right',
                    'name' => 'submitAdd'.$this->table
                ),
            )
        );


        foreach ($this->fields_form['input'] as $key => $input) {
            $unset = false;
            //champs désactivés dans les enfants
            if ($input['name'] == 'hook_ids[]' && $obj->id_parent) {
                $unset = true;
            }
            if ($input['name'] == 'alias' && $obj->id_parent) {
                $unset = true;
            }

            //champs désactivés dans les parents
            if ($input['name'] == 'id_parent' && !$obj->id_parent) {
                $unset = true;
            }

            if ($unset) {
                unset($this->fields_form['input'][$key]);
            }
        }

        if (!$obj->id_parent) {
            $this->fields_value['hook_ids[]'] = $this->object->hook_ids;
        }

        if ($obj->formdata instanceof OannaBlockData) {
            $data = $obj->formdata->getData();


            $id_lang = (int)Context::getContext()->language->id;

            //construct fields list with lang
            $fields = [];
            foreach ($additional_fields as $field) {
                if (isset($field['lang']) && $field['lang']) {
                    array_push($fields, $field['name']);
                }
            }

            //reparse data from OannaBlockData for langs
            foreach ($data as $index => $value) {
                foreach ($value as $prop => $val) {

                    if (in_array($prop, $fields)) {
                        if (!isset ($this->fields_value[$prop])) {
                            $this->fields_value[$prop] = [];
                        }
                        $this->fields_value[$prop][$index] = $val;
                    }
                    else {
                        $this->fields_value[$prop]= $data[$id_lang][$prop];
                    }
                }
            }

        }

        if (count($templates) == 1 && isset($templates[0], $templates[0]['file'])) {
            $this->fields_value['template'] = $templates[0]['file'];
        }

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->tpl_form_vars = array(
            'status' => $this->object->status
        );

        $content = parent::renderForm();

        try {
            $this->context->smarty->assign(array(
                'id' => (int)$obj->id,
                'id_parent' => (int)$obj->id_parent,
                'templates' => $templates,
                'token' => Tools::getAdminTokenLite('AdminProducts'),
                'template' => ($template = array_filter($templates, function ($tpl) use ($obj) {
                    return $tpl['file'] == $obj->template;
                })) && !empty($template) ? array_shift($template) : false,
                'cancel_url' => $obj->id_parent ? $this->context->link->getAdminLink($this->controller_name).'&'.$this->identifier.'='.$obj->id_parent.'&viewoannablock&conf=4&token='.$this->token : $this->context->link->getAdminLink($this->controller_name).'&conf=4&token='.$this->token
            ));


            //on rajoute l'url du folder de preview
            if (!is_dir( _PS_THEME_DIR_.'modules/oannablocks/views/templates/front/')) {
                $previewUrl = __PS_BASE_URI__.'modules/oannablocks/views/templates/front/';
            }
            else {
                $previewUrl = _PS_THEME_URI_.'modules/oannablocks/views/templates/front/';
            }


            $adminTemplatesPath = _PS_MODULE_DIR_.'oannablocks/views/templates/admin/';
            $_content = $this->context->smarty->createTemplate($adminTemplatesPath.'configure.tpl', null, null, $this->context->smarty)->fetch();

            if ((int)$obj->id_parent == 0 && Tools::getIsset('addoannablock') && !Tools::getIsset('template')) {
                $this->context->smarty->assign(array(
                    'preview_url' => $previewUrl,
                    'template_select_url' => $this->context->link->getAdminLink($this->controller_name).'&addoannablock&id_parent=0&token='.$this->token.'&template='
                ));
                return $this->getErrors().$this->context->smarty->createTemplate($adminTemplatesPath.'step_one.tpl', null, null, $this->context->smarty)->fetch();
            }

            $content = $_content.$content;
        } catch (SmartyException $e) {
            // var_dump($e->getMessage());
        }

        return $this->getErrors().$content;
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        
        foreach ($this->_list as &$list) {
            if (Tools::file_exists_no_cache(OannaBlock::getRootImagesPath().$list['img'].'.jpg')) {
                $list['image'] = '<img style="max-width: 75px; max-height: 75px" src="../img/oannablocks/images/'.$list['img'].'.jpg?rand='.rand(1, 1000).'" alt="" class="imgm img-thumbnail" />';
            } else {
                $list['image'] = $this->l('No image');
            }

            $list['disabled_actions'] = array();

            $_child = false;

            if ((int)$list['id_parent'] == 0) {
                if (isset($list['template'])) {
                    $_tpl_part = explode('/', $list['template']);
                    $_child = $_tpl_part[count($_tpl_part)-1];
                }

                $list['template'] = ($template = array_filter(OannaBlock::getTemplates(), function ($tpl) use ($list) {
                    return $list['template'] == $tpl['file'];
                })) && count($template) && ($template = array_shift($template)) ? $template['name'] : $this->l('No template');

                if (!count(iterator_to_array(OannaBlock::getTemplatesLow($_child)))) {
                    $list['disabled_actions'][] = 0; //disable view action
                }
            } else {

                if (isset($list['template'])) {
                    $_tpl_part = explode('/', $list['template']);
                    $_child = $_tpl_part[count($_tpl_part)-2];
                }

                $list['template'] = ($template = array_filter(OannaBlock::getTemplates($_child), function ($tpl) use ($list) {
                    return $list['template'] == $tpl['file'];
                })) && count($template) && ($template = array_shift($template)) ? $template['name'] : $this->l('No template');

                //on remove l'alias dans les détails
                unset($list['alias']);

                $list['noclick'] = true;
            }

            //gestion des dates
            $item = New OannaBlock($list['id_oannablock']);

            $list['begin_date'] = $item->getBeginDate();
            $list['end_date'] = $item->getEndDate();

        }
    }

    protected function getValidationRules()
    {
        $definition = ObjectModel::getDefinition($this->className);

        if (isset(OannaBlock::$definition['fields']['content']['required']) && OannaBlock::$definition['fields']['content']['required'] === true) {
            $definition['fields']['content']['required'] = true;
        }

        return $definition;
    }

    public function processDeleteImage()
    {
        $object = parent::processDeleteImage();

        if (Validate::isLoadedObject($object)) {
            $this->redirect_after = self::$currentIndex.'&update'.$this->table.'&'.$this->identifier.'='.Tools::getValue($this->identifier).'&conf=7&token='.$this->token;
        }
    }

    public function processSave()
    {
        $id_parent = (int)Tools::getValue('id_parent');
        $data = array();
        $template = null;
        $do_save = true;

        $path = $this->getOannablocksPath();

        if (Tools::getIsset('template')) {
            $template = $path.Tools::getValue('template');
            $template_info = OannaBlock::getTemplateInfo(new SplFileInfo($template), (bool)$id_parent);

            if (isset($template_info['config'])) {
                if ($template_info['config']['homeproducts']) {
                    $itemType = Tools::getValue('additional_field_item_type');
                    $selectedCategories = Tools::getValue('categoryBox');

                    if ($itemType == 'featured' && empty($selectedCategories)) {
                        $this->_errors[] = $this->l('Please select category');
                        $do_save = false;
                    }
                }

                if ($template_info['config']['enabled_text'] === true && $template_info['config']['required_text'] === true) {
                    OannaBlock::$definition['fields']['content']['required'] = true;
                }

                if ($template_info['config']['enabled_dates'] === true && $template_info['config']['required_dates'] === true) {
                    OannaBlock::$definition['fields']['begin_date']['required'] = true;
                    OannaBlock::$definition['fields']['end_date']['required'] = true;
                }

                if (isset($template_info['config']['fields'])) {
                    foreach ($template_info['config']['fields'] as $key => $field) {
                        if (isset($field['ignore']) && $field['ignore'] == true) {
                            continue;
                        }

                        if (isset($field['lang']) && $field['lang']) {
                            $value = [];

                            foreach(Language::getLanguages() as $key=>$lang){
                                $value[$lang['id_lang']] = Tools::getValue($field['name'].'_'.($lang['id_lang']));
                            }
                        }
                        else {
                            $value = Tools::getValue($field['name']);
                        }

                        //case upload image
                        if ($value && $field['type'] == 'file'){
                            $value = $this->uploadImage(1, $field['name'], null, false, null, null, true);
                        }
                        if (isset($template_info['config']['fields'][$key]['validator'])) {
                            $validator = OannaBlockDataValidator::create($value, $template_info['config']['fields'][$key]['validator']);

                            if (!$validator->validate()) {
                                if (isset($template_info['config']['fields'][$key]['required']) && $template_info['config']['fields'][$key]['required'] === true) {
                                    $this->errors[] = sprintf($validator->getMessage(), $field['label']);
                                    $do_save = false;
                                } else {
                                    $this->_errors[] = sprintf($validator->getMessage(), $field['label']);
                                }

                                $data[$field['name']] = null;
                            } else {
                                $data[$field['name']] = $value;
                            }
                        } else {
                            if ($field['type'] == 'file'){

                                if (!empty($value)) {
                                    $data[$field['name']] = $value;
                                }
                            }
                            elseif ($field['type'] == 'textarea'){

                                if (isset($template_info['config']['fields'][$key]['autoload_rte']) && $template_info['config']['fields'][$key]['autoload_rte']) {

                                    $data[$field['name']] = $value;
                                }
                                else {
                                    $data[$field['name']] = $value;
                                }

                            }
                            else {
                                $data[$field['name']] = $value;


                            }
                        }
                    }
                }
            }
        }

        if ($this->module->new){
            if (isset($selectedCategories) && !empty($selectedCategories)){
                $data['additional_field_item_value'] = implode(',',$selectedCategories);
            }
        }

        try {
            if ($do_save) {
                $object = parent::processSave();
            } else {
                $object = $this->loadObject();
            }
        } catch (PrestaShopException $e) {
            $this->errors[] = $e->getMessage();
        }

        if (!Validate::isLoadedObject($object)) {
            $this->redirect_after = $this->context->link->getAdminLink($this->controller_name).'&id_parent='.$id_parent.'&addoannablock&token='.$this->token.'&back=1';

            if (!empty($template)) {
                $this->redirect_after .= '&template='.basename($template, '.tpl');
            }
        } else {

            $collection = new Collection('OannaBlockData');
            $formdata = $collection->where('id_oannablock', '=', $object->id)->getFirst();


            if ($formdata === false) {
                $formdata = new OannaBlockData();
            }


            foreach ($data as $field_name => &$_data) {
                if ($_data === null) {
                    if ($field_name != 'additional_field_item_begin_date' && $field_name != 'additional_field_item_end_date')
                    $_data = $formdata->{$field_name};
                }
            }


            if ($id_parent > 0) {
                //ITEMS
                $fileInputs = ['additional_field_item_image1', 'additional_field_item_image2', 'additional_field_item_image3', 'additional_field_item_image4', 'additional_field_item_image5',
                    'additional_field_item_img1', 'additional_field_item_img2', 'additional_field_item_img3', 'additional_field_item_img4', 'additional_field_item_img5'];
            }
            else {
                //PARENTS
                $template = $path.Tools::getValue('template');
                $template_info = OannaBlock::getTemplateInfo(new SplFileInfo($template), (bool)$id_parent);

                $fileInputs = [
                    'additional_field_'.$template_info['name'].'_image1',
                    'additional_field_'.$template_info['name'].'_image2',
                    'additional_field_'.$template_info['name'].'_image3',
                    'additional_field_'.$template_info['name'].'_image4',
                    'additional_field_'.$template_info['name'].'_image5',
                    'additional_field_'.$template_info['name'].'_img1',
                    'additional_field_'.$template_info['name'].'_img2',
                    'additional_field_'.$template_info['name'].'_img3',
                    'additional_field_'.$template_info['name'].'_img4',
                    'additional_field_'.$template_info['name'].'_img5'
                ];
            }

            foreach ($fileInputs as $fileInput) {
                if (!isset($data[$fileInput])) {
                    $data[$fileInput] = $formdata->{$fileInput};
                }
            }

            //reformat for langs
            $finalAdditionalData = [];
            foreach(Language::getLanguages() as $key=>$lang){
                $temp = [];
                foreach ($data as $k=>$d)  {
                    if (is_array($d)){
                        if ($d[$lang['id_lang']] != "") {
                            $temp[$k] = $d[$lang['id_lang']];
                        }
                        else {
                            $temp[$k] = $d[1];
                        }
                    }
                    else {
                        $temp[$k] = $d;
                    }
                }
                $finalAdditionalData[$lang['id_lang']] = $temp;

                $formdata->setIdLang($lang['id_lang'])->setIdBlock($object->id)->setData($finalAdditionalData);
                $formdata->save();
            }

            if (Tools::getIsset('submitAdd'.$this->table.'AndBackToParent')) {
                $this->redirect_after = $this->context->link->getAdminLink($this->controller_name).($id_parent > 0 ? '&'.$this->identifier.'='.$id_parent.'&viewoannablock' : '').'&token='.$this->token;
            } else {
                $this->redirect_after = $this->context->link->getAdminLink($this->controller_name).'&updateoannablock&token='.$this->token.'&back=1&'.$this->identifier.'='.$object->id;
            }
        }

        if (count($this->_errors) || count($this->errors)) {
            $this->redirect_after .= '&errors='.urlencode(json_encode(array_merge($this->_errors, (array)$this->errors)));
        } else {
            $this->redirect_after .= '&conf=4';
            $this->doExportObjects();
        }

        return $object;
    }
    
    protected function deleteLinks($content)
    {
        
        $hrefs = array();
        preg_match_all('/href="[^"]*"/i', $content, $hrefs);
        foreach ($hrefs[0] as $href) {
            $content = str_replace($href, 'href="#"', $content);
        }
        return $content;
    }

    public function doExportObjects()
    {
        foreach ($this->exportObjects() as $_object) {
            $_object['link'] = '#';
            $_object['content'] = $this->deleteLinks($_object['content']);
            
            
            foreach ($_object['children'] as $key => $item) {
                $_object['children'][$key]['link'] = '#';
                $_object['children'][$key]['content'] = $this->deleteLinks($_object['children'][$key]['content']);
            }
            
            @file_put_contents($this->module->blocks_dir.$_object['block_identifier'].'.json', json_encode($_object));
        }
    }

    protected function afterUpdate($object)
    {
        $this->object = $object;
        return true;
    }

    public function exportObjects($id_parent = 0)
    {
        $_this = $this;
        $collection = new Collection($this->className);
        return array_map(function ($object) use ($_this) {
            return array_merge($object->loadFormdata()->export(), array('children' => $_this->exportObjects($object->id)));
        }, iterator_to_array($collection->where('id_parent', '=', (int)$id_parent)));
    }

    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        $object->useDataAsArray('hook_ids', Tools::getValue('hook_ids', array()));
    }

    public function ajaxProcessUpdatePositions()
    {
        $status = false;
        $position = 1;
        $positions = array_map('intval', (array)Tools::getValue('positions'));
        $blocks = new OannaBlockCollection($this->className);

        $status = !count(array_filter(array_map(function ($block) use (&$position) {
            return !$block->setPosition($position++)->update();
        }, iterator_to_array($blocks->where('id_oannablock', 'in', $positions)->customOrder('FIELD(a0.`id_oannablock`, '.implode(',', $positions).')')))));

        $this->doExportObjects();

        return $this->setJsonResponse(array(
            'success' => $status,
            'message' => $this->l($status ? 'Blocks reordered successfully' : 'An error occurred')
        ));
    }

    protected function setJsonResponse($response)
    {
        header('Content-Type: application/json; charset=utf8');
        print(json_encode($response));
        exit;
    }

    protected function uploadImage($id, $name, $dir, $ext = false, $width = null, $height = null, $return = false)
    {
        $uniqName = uniqid();
        $object = $this->loadObject();

        if (!Validate::isLoadedObject($object)) {
            return false;
        }

        if (isset($_FILES[$name]['tmp_name']) && !empty($_FILES[$name]['tmp_name'])) {
            $object->deleteImage();

            // Check image validity
            $max_size = isset($this->max_image_size) ? $this->max_image_size : 0;

            if ($error = ImageManager::validateUpload($_FILES[$name], Tools::getMaxUploadSize($max_size))) {
                $this->errors[] = $error;
            }

            $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');

            if (!$tmp_name) {
                return false;
            }

            if (!move_uploaded_file($_FILES[$name]['tmp_name'], $tmp_name)) {
                return false;
            }


            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
            if (!ImageManager::checkImageMemoryLimit($tmp_name)) {
                $this->errors[] = Tools::displayError('Due to memory limit restrictions, this image cannot be loaded. Please increase your memory_limit value via your server\'s configuration settings. ');
            }

            // Copy new image
            if (empty($this->errors) && !ImageManager::resize($tmp_name, OannaBlock::getRootImagesPath().$uniqName.'.'.$this->imageType, (int)$width, (int)$height, ($ext ? $ext : $this->imageType))) {
                $this->errors[] = Tools::displayError('An error occurred while uploading the image.');
            }

            if (!count($this->errors)) {
                if ($this->afterImageUpload()) {
                    if ($return) {
                        unlink($tmp_name);
                        return $uniqName.'.'.$this->imageType;
                    }

                    $object->img = $uniqName;
                    $object->save();
                    unlink($tmp_name);
                    return true;
                }
            }

            return false;
        }

        return true;
    }


    private function setAdditionalFileFields(&$array, $obj) {
        foreach ($array as $key => &$field) {


            if (strpos($field['type'], 'file') > -1) {
                if ($obj->formdata instanceof OannaBlockData) {
                    $f = $obj->formdata->{$field['name']};

                    if (isset($f)) {
                        $img = $obj->getRootImagesPath() . $obj->formdata->{$field['name']};
                        $img_url = false;
                        $thumb_size = false;

                        if (file_exists($img)) {
                            $img_url = '<img src="' .$obj->getImagesDirectory(). $obj->formdata->{$field['name']} . '?rand=' . rand(1, 1000) . '" alt="" class="imgm img-thumbnail" />';
                            $thumb_size = filesize($img) / 1000;
                        }
                        $field['display_image'] = true;
                        $field['image'] = $img_url;
                        $field['size'] = $thumb_size;
                        $field['format'] = version_compare(_PS_VERSION_, '1.7.0.0', '<') ? ImageType::getFormatedName('medium') : ImageType::getFormattedName('medium');
                        $field['delete_url'] = self::$currentIndex . '&' . $this->identifier . '=' . $obj->id . '&token=' . $this->token . '&deleteImage=' . $field['name'];
                    }
                }
            }
        }
    }

    private function getOannablocksPath($part = '') {
        $path = _PS_THEME_DIR_.'modules'.DIRECTORY_SEPARATOR.'oannablocks'.DIRECTORY_SEPARATOR . $part;
        if (!is_dir($path)) {
            $path = _PS_MODULE_DIR_.'oannablocks'.DIRECTORY_SEPARATOR . $part;
        }

        return $path;
    }
}

