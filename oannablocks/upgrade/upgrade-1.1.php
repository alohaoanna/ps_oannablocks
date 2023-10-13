<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1($module)
{

    //On crée le répertoire dans les images si il n'existe pas
    $path = _PS_IMG_DIR_.'oannablocks';
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        mkdir($path.'/images', 0777, true);
        mkdir($path.'/blocks', 0777, true);
    }


    $module->unregisterHook('displayPaymentEU');

    return true; // Return true if success.
}