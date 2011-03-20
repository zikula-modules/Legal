<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: User.php 6 2010-06-15 15:50:00Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class Legal_Controller_User extends Zikula_AbstractController
{

    /**
     * Legal Module main user function
     * @return string HTML output string
     */
    public function main()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Legal::', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        return $this->view->fetch('legal_user_main.htm');
    }

    /**
     * Display Terms of Use
     * @return string HTML output string
     */
    public function termsofuse()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Legal::termsofuse', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // check the option is active
        if (!$this->getVar('termsofuse')) {
            return LogUtil::registerError($this->__("'Terms of use' not activated."));
        }

        // get the current users language
        $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());

        // work out the template path
        if ($this->view->template_exists($lang.'/legal_user_termsofuse.htm')) {
            $template = $lang.'/legal_user_termsofuse.htm';
        } else {
            $template = 'en/legal_user_termsofuse.htm';
        }

        return $this->view->fetch($template);
    }

    /**
     * Display Privacy Policy
     * @return string HTML output string
     */
    public function privacy()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Legal::privacy', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // check the option is active
        if (!$this->getVar('privacypolicy')) {
            return LogUtil::registerError($this->__("'Privacy policy' not activated."));
        }

        // get the current users language
        $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());

        // work out the template path
        if ($this->view->template_exists($lang.'/legal_user_privacy.htm')) {
            $template = $lang.'/legal_user_privacy.htm';
        } else {
            $template = 'en/legal_user_privacy.htm';
        }

        return $this->view->fetch($template);
    }

    /**
     * Display Accessibility statement
     * @return string HTML output string
     */
    public function accessibilitystatement()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Legal::accessibilitystatement', '::', ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        // check the option is active
        if (!ModUtil::getVar('Legal', 'accessibilitystatement')) {
            return LogUtil::registerError($this->__("'Accessibility statement' not activated."));
        }

        // get the current users language
        $lang = ZLanguage::transformFS(ZLanguage::getLanguageCode());

        // work out the template path
        if ($this->view->template_exists($lang.'/legal_user_accessibilitystatement.htm')) {
            $template = $lang.'/legal_user_accessibilitystatement.htm';
        } else {
            $template = 'en/legal_user_accessibilitystatement.htm';
        }

        return $this->view->fetch($template);
    }
}