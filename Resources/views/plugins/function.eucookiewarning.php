<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Displays a warning that the site uses cookies ensuring compliance with EU regulations.
 *
 * Available attributes:
 *  - owner             (string)    (optional) If set uses it as the module owner of the Zikula_View instance. Default owner is the Settings module
 *  - assign            (string)    (optional) The name of the template variable to which the script tag string is assigned, <i>instead of</i>
 *                                             adding them to the page variables through PageUtil::addVar
 *  - template          (string)    (optional) The name of a template file
 *
 *
 * Examples:
 *
 * <samp>{eucookiewarning}</samp>
 *
 * <samp>{eucookiewarning owner="mymod" template="mytemplate.tpl"}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_eucookiewarning($params, Zikula_View $view)
{
    if (!isset($params['owner']) || !ModUtil::available($params['owner'])) {
        $params['owner'] = 'ZikulaLegalModule';
    }
    if (!isset($params['template'])) {
        $params['template'] = 'eucookiewarning.tpl';
    }

    $renderer = Zikula_View::getInstance($params['owner']);
    $renderer->setCaching(Zikula_View::CACHE_DISABLED);
    $return = $renderer->fetch($params['template']);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
