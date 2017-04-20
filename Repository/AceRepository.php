<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Ekyna\Bundle\ResourceBundle\Entity\AccessControlEntry;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;

/**
 * Class AceRepository
 * @package Ekyna\Bundle\ResourceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AceRepository
{
    private ObjectRepository $wrapped;
    private ?Query           $listQuery = null;


    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->wrapped = $registry->getRepository(AccessControlEntry::class);
    }

    /**
     * Finds one access control entry by subject, resource id and permission name.
     *
     * @param AclSubjectInterface $subject
     * @param string              $namespace
     * @param string              $resource
     * @param string              $permission
     *
     * @return AccessControlEntry|null
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function findAce(
        AclSubjectInterface $subject,
        string $namespace,
        string $resource,
        string $permission
    ): ?AccessControlEntry {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this
            ->wrapped
            ->createQueryBuilder('a')
            ->andWhere('a.subject = :subject')
            ->andWhere('a.namespace = :namespace')
            ->andWhere('a.resource = :resource')
            ->andWhere('a.permission = :permission')
            ->setMaxResults(1)
            ->getQuery()
            ->setParameters([
                'subject'    => $subject->getAclSubjectId(),
                'namespace'  => $namespace,
                'resource'   => $resource,
                'permission' => $permission,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Returns the ACL for the given subject.
     *
     * @param AclSubjectInterface $subject
     *
     * @return array
     */
    public function findAcl(AclSubjectInterface $subject): array
    {
        $qb = $this->getListQuery();

        $result = $qb
            ->setParameter('subject', $subject->getAclSubjectId())
            ->getScalarResult();

        return $this->transformScalarResult($result);
    }

    /**
     * Transforms scalar results to a acl list.
     *
     * @param array $result
     *
     * @return array
     * [
     *     '{namespace}' => [
     *         '{resource}' => [
     *             '{permission}' => (bool)
     *             ...
     *         ],
     *         ...
     *     ],
     *     ...
     * ]
     */
    private function transformScalarResult(array $result): array
    {
        $acl = [];

        foreach ($result as $ace) {
            if (!isset($acl[$ace['namespace']])) {
                $acl[$ace['namespace']] = [];
            }

            if (!isset($acl[$ace['namespace']][$ace['resource']])) {
                $acl[$ace['namespace']][$ace['resource']] = [];
            }

            $acl[$ace['namespace']][$ace['resource']][$ace['permission']] = (bool)$ace['granted'];
        }

        return $acl;
    }

    /**
     * Returns the list query.
     *
     * @return Query
     */
    private function getListQuery(): Query
    {
        if ($this->listQuery) {
            return $this->listQuery;
        }

        return $this->listQuery = $this
            ->wrapped
            ->createQueryBuilder('a')
            ->select(['a.namespace', 'a.resource', 'a.permission', 'a.granted'])
            ->andWhere('a.subject = :subject')
            ->addOrderBy('a.namespace', 'ASC')
            ->addOrderBy('a.resource', 'ASC')
            ->addOrderBy('a.permission', 'ASC')
            ->getQuery();
    }
}
