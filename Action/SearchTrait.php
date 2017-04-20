<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Action;

use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Search\SearchRepositoryFactoryInterface;
use Ekyna\Component\Resource\Search\SearchRepositoryInterface;

use function is_null;

/**
 * Class SearchTrait
 * @package Ekyna\Bundle\ResourceBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @property Context $context
 */
trait SearchTrait
{
    private SearchRepositoryFactoryInterface $searchRepositoryFactory;


    /**
     * Sets the search repository factory.
     *
     * @param SearchRepositoryFactoryInterface $factory
     *
     * @required
     */
    public function setSearchRepositoryFactory(SearchRepositoryFactoryInterface $factory): void
    {
        $this->searchRepositoryFactory = $factory;
    }

    /**
     * Returns the search repository for the given resource class.
     *
     * @param string|null $class
     *
     * @return SearchRepositoryInterface
     */
    protected function getSearchRepository(string $class = null): SearchRepositoryInterface
    {
        if (is_null($class)) {
            $class = $this->context->getConfig()->getEntityClass();
        }

        return $this->searchRepositoryFactory->getRepository($class);
    }
}
