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

namespace Zikula\LegalModule;

/**
 * Provides version information for the Legal module.
 */
class LegalModuleVersion extends \Zikula_AbstractVersion
{

    /**
     * Retrieve version and other metadata for the Legal module.
     *
     * @return array Metadata for the Legal module, as specified by Zikula core.
     */
    public function getMetaData()
    {
        return array(
            'oldnames' => array('legal', 'Legal'),
            'displayname' => $this->__('Legal'),
            'description' => $this->__('Provides an interface for managing the site\'s legal documents.'),
            'url' => $this->__('legal'),
            'version' => '2.1.1',
            'core_min' => '1.4.0',
            'core_max' => '1.4.99',
            'securityschema' => array(
                $this->name . '::' => '::',
                $this->name . '::legalnotice' => '::',
                $this->name . '::termsofuse' => '::',
                $this->name . '::privacypolicy' => '::',
                $this->name . '::agepolicy' => '::',
                $this->name . '::accessibilitystatement' => '::',
                $this->name . '::cancellationrightpolicy' => '::',
                $this->name . '::tradeconditions' => '::'
            )
        );

    }

}
