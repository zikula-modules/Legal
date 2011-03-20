<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class Legal_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['oldnames'] = 'legal';
        $meta['displayname']    = __('Legal info manager');
        $meta['description']    = __("Provides an interface for managing the site's 'Terms of use', 'Privacy statement' and 'Accessibility statement'.");
        //! module name that appears in URL
        $meta['url']            = __('legalmod');
        $meta['version']        = '1.6.0';
        $meta['securityschema'] = array('Legal::' => '::',
                'Legal::termsofuse' => '::',
                'Legal::privacy' => '::',
                'Legal::accessibilitystatement' => '::');
        return $meta;
    }
}