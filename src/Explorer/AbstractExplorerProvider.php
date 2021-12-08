<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractExplorerProvider implements ExplorerProviderInterface
{
    protected array $options;

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
