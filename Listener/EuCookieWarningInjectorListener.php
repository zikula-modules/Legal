<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\LegalModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\ThemeModule\Api\PageAssetApi;
use Zikula\ThemeModule\Engine\Asset;

/**
 * EuCookieWarningInjectorListener injects a warning to the user that cookies are
 * in use in order to comply with EU regulations.
 *
 * The onKernelResponse method must be connected to the kernel.response event.
 *
 * The Warning is only injected on well-formed HTML (with a proper <body> tag).
 * This means that the Warning is never included in sub-requests or ESI requests.
 */
class EuCookieWarningInjectorListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var string
     */
    private $stylesheetOverride;

    /**
     * @var PageAssetApi
     */
    private $pageAssetApi;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * Constructor.
     *
     * @param RouterInterface $router
     * @param Asset $assetHelper
     * @param string $stylesheetOverride Custom path to css file (optional)
     * @param PageAssetApi $pageAssetApi
     * @param bool $enabled
     */
    public function __construct(
        RouterInterface $router,
        Asset $assetHelper,
        $stylesheetOverride = null,
        PageAssetApi $pageAssetApi,
        $enabled
    ) {
        $this->router = $router;
        $this->assetHelper = $assetHelper;
        $this->stylesheetOverride = $stylesheetOverride;
        $this->pageAssetApi = $pageAssetApi;
        $this->enabled = (bool) $enabled;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->enabled) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();
        $routeInfo = $this->router->match($request->getPathInfo());
        $containsProhibitedRoute = in_array($routeInfo['_route'], ['_wdt', 'bazinga_jstranslation_js', 'fos_js_routing_js', 'zikulasearchmodule_search_opensearch']);
        $containsProhibitedRoute = $containsProhibitedRoute || (strpos($routeInfo['_route'], '_profiler') !== false);

        // do not capture redirects or modify XML HTTP Requests or routing or toolbar requests
        if ($request->isXmlHttpRequest()
            || $response->isRedirect()
            || $containsProhibitedRoute) {
            return;
        }

        // is cookie set?
        if ($request->cookies->has('cb-enabled') && $request->cookies->get('cb-enabled') == 'accepted') {
            return;
        }

        $this->injectWarning($request, $response);
    }

    /**
     * Injects the warning into the Response.
     *
     * @param Request  $request  A Request instance
     * @param Response $response A Response instance
     */
    protected function injectWarning(Request $request, Response $response)
    {
        // add javascript to bottom of body - jquery is assumed to be present
        $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/jquery.cookiebar/jquery.cookiebar.js');
        $this->pageAssetApi->add('javascript', $path, 100);
        $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/ZikulaLegalModule.Listener.EUCookieConfig.js');
        $this->pageAssetApi->add('javascript', $path, 101);
        // add stylesheet to head
        if (!empty($this->stylesheetOverride) && file_exists($this->stylesheetOverride)) {
            $path = $this->stylesheetOverride;
        } else {
            $path = $this->assetHelper->resolve('@' . LegalConstant::MODNAME . ':js/jquery.cookiebar/jquery.cookiebar.css');
        }
        $this->pageAssetApi->add('stylesheet', $path);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }
}
