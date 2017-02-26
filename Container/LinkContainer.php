<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\PermissionsModule\Api\PermissionApi;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * LinkContainer constructor.
     *
     * @param TranslatorInterface $translator    Translator service instance
     * @param RouterInterface     $router        RouterInterface service instance
     * @param PermissionApi       $permissionApi PermissionApi service instance
     * @param VariableApi         $variableApi   VariableApi service instance
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, PermissionApi $permissionApi, VariableApi $variableApi)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
    }

    /**
     * get Links of any type for this extension
     * required by the interface.
     *
     * @param string $type
     *
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $method = 'get'.ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }

    /**
     * get the Admin links for this extension.
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];

        if ($this->permissionApi->hasPermission($this->getBundleName().'::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url'  => $this->router->generate('zikulalegalmodule_config_config'),
                'text' => $this->translator->__('Settings', 'zikulalegalmodule'),
                'icon' => 'wrench',
            ];
        }

        return $links;
    }

    /**
     * get the User links for this extension.
     *
     * @return array
     */
    private function getUser()
    {
        $links = [];

        $moduleVars = $this->variableApi->getAll(LegalConstant::MODNAME);

        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_LEGALNOTICE_ACTIVE, false)) {
            $links[] = [
                'text' => $this->translator->__('Legal notice', 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_LEGALNOTICE_URL, 'legalnotice'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_ACTIVE, false)) {
            $links[] = [
                'text' => $this->translator->__('Terms of use', 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_TERMS_URL, 'termsofuse'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_ACTIVE, false)) {
            $links[] = [
                'text' => $this->translator->__('Privacy policy', 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_PRIVACY_URL, 'privacypolicy'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE, false)) {
            $links[] = [
                'text' => $this->translator->__('Trade conditions', 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_TRADECONDITIONS_URL, 'tradeconditions'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE, false)) {
            $links[] = [
                'text' => $this->translator->__('Cancellation right policy', 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, 'cancellationrightpolicy'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE, false)) {
            $links[] = [
                'text' => $this->translator->__('Accessibility statement', 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_ACCESSIBILITY_URL, 'accessibilitystatement'),
            ];
        }

        return $links;
    }

    /**
     * Determine the URL for a certain user link.
     *
     * @param string urlVar       Name of module var storing a possible custom url
     * @param string defaultRoute Suffix for route for default url
     */
    private function determineUrl($urlVar, $defaultRoute)
    {
        $customUrl = $this->variableApi->get(LegalConstant::MODNAME, $urlVar, '');
        if ($customUrl != '') {
            return $customUrl;
        }

        return $this->router->generate('zikulalegalmodule_user_'.$defaultRoute);
    }

    /**
     * set the BundleName as required by the interface.
     *
     * @return string
     */
    public function getBundleName()
    {
        return LegalConstant::MODNAME;
    }
}
