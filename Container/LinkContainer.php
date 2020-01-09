<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

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
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
    }

    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        $method = 'get'.ucfirst(mb_strtolower($type));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return [];
    }

    /**
     * Get the admin links for this extension.
     */
    private function getAdmin(): array
    {
        $links = [];

        if ($this->permissionApi->hasPermission($this->getBundleName().'::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url'  => $this->router->generate('zikulalegalmodule_config_config'),
                'text' => $this->translator->trans('Settings', [], 'zikulalegalmodule'),
                'icon' => 'wrench',
            ];
        }

        return $links;
    }

    /**
     * Get the user links for this extension.
     */
    private function getUser(): array
    {
        $links = [];

        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_LEGALNOTICE_ACTIVE)) {
            $links[] = [
                'text' => $this->translator->trans('Legal notice', [], 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_LEGALNOTICE_URL, 'legalnotice'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_ACTIVE)) {
            $links[] = [
                'text' => $this->translator->trans('Terms of use', [], 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_TERMS_URL, 'termsofuse'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_ACTIVE)) {
            $links[] = [
                'text' => $this->translator->trans('Privacy policy', [], 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_PRIVACY_URL, 'privacypolicy'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE)) {
            $links[] = [
                'text' => $this->translator->trans('Trade conditions', [], 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_TRADECONDITIONS_URL, 'tradeconditions'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE)) {
            $links[] = [
                'text' => $this->translator->trans('Cancellation right policy', [], 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, 'cancellationrightpolicy'),
            ];
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE)) {
            $links[] = [
                'text' => $this->translator->trans('Accessibility statement', [], 'zikulalegalmodule'),
                'url'  => $this->determineUrl(LegalConstant::MODVAR_ACCESSIBILITY_URL, 'accessibilitystatement'),
            ];
        }

        return $links;
    }

    /**
     * Get the account links for this extension.
     */
    private function getAccount(): array
    {
        $links = [];
        $links[] = [
            'url'  => $this->router->generate('zikulalegalmodule_user_index'),
            'text' => $this->translator->trans('Legal Docs', [], 'zikulalegalmodule'),
            'icon' => 'gavel',
        ];

        return $links;
    }

    /**
     * Determine the URL for a certain user link.
     */
    private function determineUrl(string $urlVar, string $defaultRoute): string
    {
        $customUrl = $this->variableApi->get(LegalConstant::MODNAME, $urlVar, '');
        if (null !== $customUrl && '' !== $customUrl) {
            return $customUrl;
        }

        return $this->router->generate('zikulalegalmodule_user_'.$defaultRoute);
    }

    public function getBundleName(): string
    {
        return LegalConstant::MODNAME;
    }
}
