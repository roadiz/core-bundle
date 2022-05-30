<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Api\Model\RealmsAwareWebResponseInterface;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\RealmVoter;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

trait RealmsAwareWebResponseOutputDataTransformerTrait
{
    abstract protected function getSecurity(): Security;
    abstract protected function getManagerRegistry(): ManagerRegistry;

    /**
     * @param RealmsAwareWebResponseInterface $output
     * @param NodesSources $data
     * @return WebResponseInterface
     * @throws UnauthorizedHttpException
     */
    protected function injectRealms(RealmsAwareWebResponseInterface $output, NodesSources $data): WebResponseInterface
    {
        $output->setRealms($this->getManagerRegistry()->getRepository(Realm::class)->findByNode(
            $data->getNode()
        ));
        $output->setHidingBlocks(false);

        $denyingRealms = array_filter($output->getRealms(), function (RealmInterface $realm) {
            return $realm->getBehaviour() === RealmInterface::BEHAVIOUR_DENY;
        });
        foreach ($denyingRealms as $denyingRealm) {
            if (!$this->getSecurity()->isGranted(RealmVoter::READ, $denyingRealm)) {
                throw new UnauthorizedHttpException(
                    $denyingRealm->getChallenge(),
                    'WebResponse was denied by Realm authorization, check Www-Authenticate header'
                );
            }
        }

        $blockHidingRealms = array_filter($output->getRealms(), function (RealmInterface $realm) {
            return $realm->getBehaviour() === RealmInterface::BEHAVIOUR_HIDE_BLOCKS;
        });
        foreach ($blockHidingRealms as $blockHidingRealm) {
            if (!$this->getSecurity()->isGranted(RealmVoter::READ, $blockHidingRealm)) {
                $output->setHidingBlocks(true);
            }
        }

        return $output;
    }
}
