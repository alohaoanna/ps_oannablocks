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

class OannablocksOverride extends Oannablocks
{

    public function hookModuleRoutes($params)
    {
        $routes = array(
            // Single
            'module-oannablocks-detail' => array(
                'controller' => 'detail',
                'rule' => 'actualites{/:id}-{:name}',
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
                    'template' => 'detail-actualites',
//                    'parentBreadcrumb' => 'Actualités',
//                    'url' => 'modulename',
                ),
            ),
        );
        return $routes;
    }


}
