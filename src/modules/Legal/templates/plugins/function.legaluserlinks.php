<?php
/**
 * Copyright Zikula Foundation 2001 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Legal
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display user links for the Legal module.
 *
 * Example
 * {legaluserlinks start='[' end=']' seperator='|' class='z-menuitem-title'}
 *
 * Template used:
 *      legal_function_legaluserlinks.tpl
 *
 * Template Parameters:
 *      string $params['start']     DEPRECATED, modify the template instead; The string to display before all of the links; optional; default '['.
 *      string $params['end']       DEPRECATED, modify the template instead; The string to display between each of the links; optional; default '|'.
 *      string $params['separator'] DEPRECATED, modify the template instead; The string to display before all of the links; optional; default ']'.
 *      string $params['class']     DEPRECATED, modify the template instead; The string to display before all of the links; optional; default 'z-menuitem-title'.
 *
 * @param array       $params All parameters passed to this function from the template.
 * @param Zikula_View &$view  Reference to the Zikula view object, a subclass of Smarty.
 *
 * @return string The rendered legal_function_legaluserlinks.tpl template.
 */
function smarty_function_legaluserlinks($params, &$view)
{
    $templateVariables = array(
        'policies'  => array(
            'termsofuse'            => ModUtil::getVar(Legal_Constant::MODNAME, Legal_Constant::MODVAR_TERMS_ACTIVE, false),
            'privacypolicy'         => ModUtil::getVar(Legal_Constant::MODNAME, Legal_Constant::MODVAR_PRIVACY_ACTIVE, false),
            'accessibilitystatement'=> ModUtil::getVar(Legal_Constant::MODNAME, Legal_Constant::MODVAR_ACCESSIBILITY_ACTIVE, false),
        ),
        'domain'    => ZLanguage::getModuleDomain(Legal_Constant::MODNAME),
        'start'     => isset($params['start'])     ? $params['start']     : '',
        'end'       => isset($params['end'])       ? $params['end']       : '',
        'seperator' => isset($params['seperator']) ? $params['seperator'] : '',
        'class'     => isset($params['class'])     ? $params['class']     : '',
    );

    return $view->assign($templateVariables)
            ->fetch('plugins/legal_function_legaluserlinks.tpl');
}
