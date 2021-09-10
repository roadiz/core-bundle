<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class RoadizExtension extends AbstractExtension implements GlobalsInterface
{
    protected Settings $settingsBag;
    protected NodeTypes $nodeTypesBag;
    protected PreviewResolverInterface $previewResolver;
    protected NodeChrootResolver $chrootResolver;
    protected string $cmsVersion;

    /**
     * @param Settings $settingsBag
     * @param NodeTypes $nodeTypesBag
     * @param PreviewResolverInterface $previewResolver
     * @param NodeChrootResolver $chrootResolver
     * @param string $cmsVersion
     */
    public function __construct(Settings $settingsBag, NodeTypes $nodeTypesBag, PreviewResolverInterface $previewResolver, NodeChrootResolver $chrootResolver, string $cmsVersion)
    {
        $this->settingsBag = $settingsBag;
        $this->nodeTypesBag = $nodeTypesBag;
        $this->previewResolver = $previewResolver;
        $this->chrootResolver = $chrootResolver;
        $this->cmsVersion = $cmsVersion;
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        return [
            'cms_version' => !$this->settingsBag->get('hide_roadiz_version', false) ? $this->cmsVersion : null,
            'cms_prefix' => !$this->settingsBag->get('hide_roadiz_version', false) ? $this->cmsVersion : null,
            'help_external_url' => 'http://docs.roadiz.io',
            'is_preview' => $this->previewResolver->isPreview(),
            'bags' => [
                'settings' => $this->settingsBag,
                'nodeTypes' => $this->nodeTypesBag,
            ],
            'chroot_resolver' => $this->chrootResolver,
            'meta' => [
                'siteName' => $this->settingsBag->get('site_name'),
                'siteCopyright' => $this->settingsBag->get('site_copyright'),
                'siteDescription' => $this->settingsBag->get('seo_description'),
            ]
        ];
    }
}
