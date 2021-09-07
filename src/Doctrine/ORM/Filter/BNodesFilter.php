<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter
 */
class BNodesFilter extends ANodesFilter
{
    public static function getSubscribedEvents()
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', 40]],
            QueryBuilderBuildEvent::class => [['onNodeQueryBuilderBuild', 30]]
        ];
    }

    /**
     * @return string
     */
    protected function getProperty()
    {
        return 'bNodes';
    }

    /**
     * @return string
     */
    protected function getNodeJoinAlias()
    {
        return 'b_n';
    }

    /**
     * @return string
     */
    protected function getNodeFieldJoinAlias()
    {
        return 'b_n_f';
    }
}
