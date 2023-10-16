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

class OannaBlockData extends ObjectModel
{
    public $id;
    public $id_oannablock_data;
    public $id_oannablock;

    public $data;

    protected $_data = array();

    public static $definition = array(
        'table' => 'oannablock_data',
        'primary' => 'id_oannablock_data',
        'multilang' => true,
        'fields' => array(
            'id_oannablock' => array('type' => self::TYPE_INT),
            'data' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 65535),
        ),
    );

    public function setIdBlock($id)
    {
        $this->id_oannablock = (int)$id;
        return $this;
    }

    public function setIdLang($id_lang)
    {
        $this->id_lang = (int)$id_lang;
        return $this;
    }

    public function setData($data)
    {
        $this->data = array();
        foreach(Language::getLanguages() as $key=>$lang){

            $this->data[$lang['id_lang']] = json_encode((array)$data[$lang['id_lang']]);
        }
        return $this;
    }

    public function export()
    {
        $data = $this->getData();

        return array(
            'id_oannablock' => $this->id_oannablock,
            'id_oannablock_data' => $this->id_oannablock_data,
            'data' => $data[Context::getContext()->language->id]
        );
    }

    public function import(array $data)
    {
        $this->id_oannablock = isset($data['id_oannablock']) ? $data['id_oannablock'] : $this->id_oannablock;
        $this->id_oannablock_data = isset($data['id_oannablock_data']) ? $data['id_oannablock_data'] : $this->id_oannablock_data;
        $this->data = isset($data['data']) ? $data['data'] : $this->data;

        return $this;
    }

    public function __set($field, $value)
    {
        $this->_data[$field] = $value;
        return $this;
    }

    public function __get($field)
    {
        if(isset($this->_data) && is_array($this->_data)){
            return array_key_exists($field, $this->_data) ? $this->_data[$field] : null;
        }else{
            return null;
        }
    }

    public function hydrate(array $data, $id_lang = null)
    {
        parent::hydrate($data, $id_lang);
        return $this->prepare();
    }

    public function prepare()
    {
        if ($this->data !== null) {
            $data = $this->data;
            if (is_array($data)) {
                $data = $data[Context::getContext()->language->id];
            }
            $data = (array)$data;
            $this->_data = json_decode(array_shift($data), true);
        }
        return $this;
    }

    public function getData()
    {
        return array_map(function ($data) {
            return json_decode($data, true);
        }, (array)$this->data);
    }
}
