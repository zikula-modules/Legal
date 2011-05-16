<?php
/**
 * Copyright 2001 Zikula Foundation.
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
 * Provides version information for the Legal module.
 *
 * This class also sets up hook bundles.
 */
class Legal_Version extends Zikula_AbstractVersion
{
    /**
     * Retrieve version and other metadata for the Legal module.
     *
     * @return array Metadata for the Legal module, as specified by Zikula core.
     */
    public function getMetaData()
    {
        return array(
                'oldnames' => 'legal',
                'displayname' => __('Legal info manager'),
                'description' => __("Provides an interface for managing the site's 'Terms of use', 'Privacy statement' and 'Accessibility statement'."),
                //! module name that appears in URL
                'url' => __('legalmod'),
                'version' => '2.0.0',
                'core_min' => '1.3.0',
                'capabilities' => array(HookUtil::PROVIDER_CAPABLE => array('enabled' => true)),
                'securityschema' => array(
                        $this->name . '::' => '::',
                        $this->name . '::termsofuse' => '::',
                        $this->name . '::privacypolicy' => '::',
                        $this->name . '::agepolicy' => '::',
                        $this->name . '::accessibilitystatement' => '::'
                ),
        );
    }

    /**
     * Sets up hook bundles for the Legal modul.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
//        // Provider bundles
//        // Bundle to add change-of-password to login if desired.
//        $bundle = new Zikula_HookManager_ProviderBundle($this->name, 'provider.legal.ui-hooks.acceptpolicies', 'ui_hooks', $this->__('Legal policies user acceptance login hook provider.'));
//        $bundle->addServiceHandler('ui.view', 'Legal_HookHandler_AcceptPolicies', 'uiView', 'legal_acceptpolicies.service');
//        $bundle->addServiceHandler('ui.edit', 'Legal_HookHandler_AcceptPolicies', 'uiEdit', 'legal_acceptpolicies.service');
//        $bundle->addServiceHandler('validate.edit', 'Legal_HookHandler_AcceptPolicies', 'validateEdit', 'legal_acceptpolicies.service');
//        $bundle->addServiceHandler('process.edit', 'Legal_HookHandler_AcceptPolicies', 'processEdit', 'legal_acceptpolicies.service');
//        $this->registerHookProviderBundle($bundle);
    }

}