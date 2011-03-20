<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage legal
 */

class Legal_Installer extends Zikula_AbstractInstaller
{

    /**
     * initialise the template module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @author Mark West
     * @return bool true if successful, false otherwise
     */
    function install()
    {
        $this->setVar('termsofuse', true);
        $this->setVar('privacypolicy', true);
        $this->setVar('accessibilitystatement', true);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param        string   $oldVersion   version number string to upgrade from
     * @return       mixed    true on success, last valid version string or false if fails
     */
    function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion)
        {
            case '1.1':
                $this->setVar('termsofuse', true);
                $this->setVar('privacypolicy', true);
                $this->setVar('accessibilitystatement', true);

            case '1.2':
            case '1.3':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the Legal module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return bool true if successful, false otherwise
     */
    function uninstall()
    {
        $this->delVars();

        // Deletion successful
        return true;
    }
}