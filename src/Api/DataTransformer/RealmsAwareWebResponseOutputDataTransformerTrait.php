<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use RZ\Roadiz\CoreBundle\Api\Model\RealmsAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait RealmsAwareWebResponseOutputDataTransformerTrait
{
    abstract protected function getRealmResolver(): RealmResolverInterface;

    /**
     * @param RealmsAwareWebResponseInterface $output
     * @param NodesSources $data
     * @return WebResponseInterface
     * @throws UnauthorizedHttpException
     */
    protected function injectRealms(RealmsAwareWebResponseInterface $output, NodesSources $data): WebResponseInterface
    {
        $output->setRealms($this->getRealmResolver()->getRealms($data->getNode()));
        $output->setHidingBlocks(false);

        $denyingRealms = array_filter($output->getRealms(), function (RealmInterface $realm) {
            return $realm->getBehaviour() === RealmInterface::BEHAVIOUR_DENY;
        });
        foreach ($denyingRealms as $denyingRealm) {
            $this->getRealmResolver()->denyUnlessGranted($denyingRealm);
        }

        $blockHidingRealms = array_filter($output->getRealms(), function (RealmInterface $realm) {
            return $realm->getBehaviour() === RealmInterface::BEHAVIOUR_HIDE_BLOCKS;
        });
        foreach ($blockHidingRealms as $blockHidingRealm) {
            if (!$this->getRealmResolver()->isGranted($blockHidingRealm)) {
                $output->setHidingBlocks(true);
            }
        }

        return $output;
    }
}
