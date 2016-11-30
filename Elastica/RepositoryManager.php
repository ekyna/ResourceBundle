<?php

namespace Ekyna\Bundle\ResourceBundle\Elastica;

use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderAwareInterface;
use Ekyna\Bundle\CoreBundle\Locale\LocaleProviderAwareTrait;
use FOS\ElasticaBundle\Doctrine\RepositoryManager as BaseManager;

/**
 * Class RepositoryManager
 * @package Ekyna\Bundle\ResourceBundle\Elastica
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RepositoryManager extends BaseManager implements LocaleProviderAwareInterface
{
    use LocaleProviderAwareTrait;

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

        if ($repository instanceof LocaleProviderAwareInterface) {
            $repository->setLocaleProvider($this->getLocaleProvider());
        }

        return $repository;
    }
}
