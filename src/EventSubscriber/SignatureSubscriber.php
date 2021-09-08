<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
final class SignatureSubscriber implements EventSubscriberInterface
{
    /**
     * @var Settings
     */
    protected Settings $settingsBag;
    private string $version;
    private bool $debug;

    /**
     * @param Settings $settingsBag
     * @param string   $cmsVersion
     * @param bool     $debug
     */
    public function __construct(Settings $settingsBag, string $cmsVersion, bool $debug = false)
    {
        $this->version = $cmsVersion;
        $this->debug = $debug;
        $this->settingsBag = $settingsBag;
    }
    /**
     * Filters the Response.
     *
     * @param ResponseEvent $event A ResponseEvent instance
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest() || $this->settingsBag->get('hide_roadiz_version', false)) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->add(['X-Powered-By' => 'Roadiz CMS']);

        if ($this->debug && $this->version) {
            $response->headers->add(['X-Version' => $this->version]);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
