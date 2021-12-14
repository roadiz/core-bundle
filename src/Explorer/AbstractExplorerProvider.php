<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use Psr\Container\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractExplorerProvider implements ExplorerProviderInterface
{
    protected array $options;
    protected ContainerInterface $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'page'       => 1,
            'search'   =>  null,
            'itemPerPage'   => 30
        ]);
    }
}
