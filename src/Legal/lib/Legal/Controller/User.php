<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: User.php 6 2010-06-15 15:50:00Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class Legal_Controller_User extends Zikula_Controller
{

    /**
     * Legal Module main user function
     * @return string HTML output string
     */
    public function main()
    {
        // Security check
        if (!SecurityUtil::checkPermission('legal::', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $renderer = Zikula_View::getInstance('Legal');

        return $renderer->fetch('legal_user_main.htm');
    }

    /**
     * Display Terms of Use
     * @return string HTML output string
     */
    public function termsofuse()
    {
        // Security check
        if (!SecurityUtil::checkPermission('legal::termsofuse', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // check the option is active
        if (!ModUtil::getVar('legal', 'termsofuse')) {
            return LogUtil::registerError($this->__("'Terms of use' not activated."));
        }

        // Create output object
        $renderer = Zikula_View::getInstance('legal');

        // get the current users language
        $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());

        // work out the template path
        if ($renderer->template_exists($lang.'/legal_user_termsofuse.htm')) {
            $template = $lang.'/legal_user_termsofuse.htm';
        } else {
            $template = 'en/legal_user_termsofuse.htm';
        }

        // check out if the contents are cached.
        // If this is the case, we do not need to make DB queries.
        if ($renderer->is_cached($template)) {
            return $renderer->fetch($template);
        }

        return $renderer->fetch($template);
    }

    /**
     * Display Privacy Policy
     * @return string HTML output string
     */
    public function privacy()
    {
        // Security check
        if (!SecurityUtil::checkPermission('legal::privacy', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // check the option is active
        if (!ModUtil::getVar('legal', 'privacypolicy')) {
            return LogUtil::registerError($this->__("'Privacy policy' not activated."));
        }

        // Create output object
        $renderer = Zikula_View::getInstance('legal');

        // get the current users language
        $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());

        // work out the template path
        if ($renderer->template_exists($lang.'/legal_user_privacy.htm')) {
            $template = $lang.'/legal_user_privacy.htm';
        } else {
            $template = 'en/legal_user_privacy.htm';
        }

        // check out if the contents are cached.
        // If this is the case, we do not need to make DB queries.
        if ($renderer->is_cached($template)) {
            return $renderer->fetch($template);
        }

        return $renderer->fetch($template);
    }

    /**
     * Display Accessibility statement
     * @return string HTML output string
     */
    public function accessibilitystatement()
    {
        // Security check
        if (!SecurityUtil::checkPermission('legal::accessibilitystatement', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // check the option is active
        if (!ModUtil::getVar('legal', 'accessibilitystatement')) {
            return LogUtil::registerError($this->__("'Accessibility statement' not activated."));
        }

        // Create output object
        $renderer = Zikula_View::getInstance('legal');

        // get the current users language
        $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());

        // work out the template path
        if ($renderer->template_exists($lang.'/legal_user_accessibilitystatement.htm')) {
            $template = $lang.'/legal_user_accessibilitystatement.htm';
        } else {
            $template = 'en/legal_user_accessibilitystatement.htm';
        }

        // check out if the contents are cached.
        // If this is the case, we do not need to make DB queries.
        if ($renderer->is_cached($template)) {
            return $renderer->fetch($template);
        }

        return $renderer->fetch($template);
    }
}