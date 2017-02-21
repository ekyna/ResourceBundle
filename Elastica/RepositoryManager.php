<?php

namespace Ekyna\Bundle\ResourceBundle\Elastica;

use Ekyna\Component\Resource\Locale;
use FOS\ElasticaBundle\Doctrine\RepositoryManager as BaseManager;

/**
 * Class RepositoryManager
 * @package Ekyna\Bundle\ResourceBundle\Elastica
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RepositoryManager extends BaseManager implements Locale\LocaleProviderAwareInterface
{
    use Locale\LocaleProviderAwareTrait;

    /**
     * Return repository for entity.
     *
     * @param string $entityName
     *
     * @return \FOS\ElasticaBundle\Repository
     */
    public function getRepository($entityName)
    {
        $repository = parent::getRepository($entityName);

        if ($repository instanceof Locale\LocaleProviderAwareInterface) {
            $repository->setLocaleProvider($this->getLocaleProvider());
        }

        return $repository;
    }
}
