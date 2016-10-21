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

use ModUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\LegalModule\Constant as LegalConstant;

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
    private $stylesheetOverride;

    public function __construct($stylesheetOverride = null)
    {
        $this->stylesheetOverride = $stylesheetOverride;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if (!$event->isMasterRequest()) {
            return;
        }

        // do not capture redirects or modify XML HTTP Requests or routing or toolbar requests
        if ($request->isXmlHttpRequest()
            || $response->isRedirect()
            || $request->getPathInfo() == '/js/routing'
            || strpos($request->getPathInfo(), '/_wdt')) {
            return;
        }

        // is modvar enabled?
        // TODO legacy call
        $cookieSetting = ModUtil::getVar(LegalConstant::MODNAME, LegalConstant::MODVAR_EUCOOKIE);
        if (empty($cookieSetting)) {
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
        $content = $response->getContent();
        // jquery is assumed to be present

        // add javascript to bottom of body
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            // TODO legacy call
            $module = ModUtil::getModule(LegalConstant::MODNAME);
            $path = $request->getBasePath().'/'.$module->getRelativePath().'/Resources/public/js/jquery.cookiebar/jquery.cookiebar.js';
            $javascript = '<script type="text/javascript" src="'.$path.'"></script>';
            // allow translation of content
            $message = __('We use cookies to track usage and preferences', $module->getTranslationDomain());
            $acceptText = __('I Understand', $module->getTranslationDomain());
            $javascript .= '
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery.cookieBar({
        message: \''.$message.'\',
        acceptText: \''.$acceptText.'\'
    });
});
</script>';
            $content = substr($content, 0, $pos).$javascript.substr($content, $pos);
            $response->setContent($content);
        }

        // add stylesheet to head
        $pos = strripos($content, '</head>');
        if (false !== $pos) {
            // TODO legacy call
            $module = ModUtil::getModule(LegalConstant::MODNAME);
            if (!empty($this->stylesheetOverride) && file_exists($this->stylesheetOverride)) {
                $path = $this->stylesheetOverride;
            } else {
                $path = $request->getBasePath().'/'.$module->getRelativePath().'/Resources/public/js/jquery.cookiebar/jquery.cookiebar.css';
            }
            $css = '<link rel="stylesheet" type="text/css" href="'.$path.'" />';
            $content = substr($content, 0, $pos).$css.substr($content, $pos);
            $response->setContent($content);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }
}
