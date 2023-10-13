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

class OannaBlockDataValidator
{
    protected $content = '';
    protected $validator = '';
    protected $messages = array();

    public function __construct($content, $validator)
    {
        $this->content = $content;
        $this->validator = $validator;
        $this->messages = array(
            'isUnsignedInt' => $this->l('Field `%s` should be numeric, more than 0 and less than 4294967296'),
            'default' => $this->l('Invalid `%s` field!')
        );

        return $this;
    }

    public static function create($content, $validator)
    {
        return new self($content, $validator);
    }

    public function getMessage()
    {
        return array_key_exists($this->validator, $this->messages) ? $this->messages[$this->validator] : $this->messages['default'];
    }

    public function validate()
    {
        $reflector = new ReflectionMethod('Validate', $this->validator);
        return $reflector->isStatic() && $reflector->isPublic() ? call_user_func('Validate::'.$this->validator, $this->content) : true;
    }

    public function __toString()
    {
        return (bool)$this->validate();
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
