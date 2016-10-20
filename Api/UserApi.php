<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Api;

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
        $links = [];

        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, false)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_legalnotice');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_LEGALNOTICE_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = [
                'text' => $this->__('Legal notice'),
                'url'  => $url,
            ];
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_ACTIVE, false)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_termsofuse');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = [
                'text' => $this->__('Terms of use'),
                'url'  => $url,
            ];
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_ACTIVE, false)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_privacypolicy');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = [
                'text' => $this->__('Privacy policy'),
                'url'  => $url,
            ];
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, false)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_tradeconditions');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = [
                'text' => $this->__('Trade conditions'),
                'url'  => $url,
            ];
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, false)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_cancellationrightpolicy');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = [
                'text' => $this->__('Cancellation right'),
                'url'  => $url,
            ];
        }
        if (ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, false)) {
            $url = $this->get('router')->generate('zikulalegalmodule_user_accessibilitystatement');
            $customUrl = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_URL, '');
            if (!empty($customUrl)) {
                $url = $customUrl;
            }
            $links[] = [
                'text' => $this->__('Accessibility statement'),
                'url'  => $url,
            ];
        }

        return $links;
    }
}
