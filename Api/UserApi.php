<?php

/**
 * Copyright (c) 2001-2012 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html GNU/LGPLv3 (or at your option any later version).
 * @package Legal
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\LegalModule\Api;

use SecurityUtil;
use ModUtil;
use Zikula\LegalModule\Constant as LegalConstant;

/**
 * Administrative API functions.
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * Get available user links.
     *
     * @return array Array of links.
     */
    public function getLinks()
    {
        $links = array();

        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, false)) {
            $url = ModUtil::url(LegalConstant::MODNAME, 'user', 'legalNotice');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_LEGALNOTICE_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = array(
                'text' => $this->__('Legal notice'),
                'url' => $url);
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_ACTIVE, false)) {
            $url = ModUtil::url(LegalConstant::MODNAME, 'user', 'termsOfUse');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = array(
                'text' => $this->__('Terms of use'),
                'url' => $url);
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_ACTIVE, false)) {
            $url = ModUtil::url(LegalConstant::MODNAME, 'user', 'privacyPolicy');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = array(
                'text' => $this->__('Privacy policy'),
                'url' => $url);
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, false)) {
            $url = ModUtil::url(LegalConstant::MODNAME, 'user', 'tradeConditions');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = array(
                'text' => $this->__('Trade conditions'),
                'url' => $url);
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, false)) {
            $url = ModUtil::url(LegalConstant::MODNAME, 'user', 'cancellationRightPolicy');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = array(
                'text' => $this->__('Cancellation right'),
                'url' => $url);
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, false)) {
            $url = ModUtil::url(LegalConstant::MODNAME, 'user', 'accessibilityStatement');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = array(
                'text' => $this->__('Accessibility statement'),
                'url' => $url);
        }


        return $links;
    }

}