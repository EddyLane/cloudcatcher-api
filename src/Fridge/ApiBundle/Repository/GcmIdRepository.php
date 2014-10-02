<?php
/**
 * Created by PhpStorm.
 * User: edwardlane
 * Date: 03/10/2014
 * Time: 00:12
 */

namespace Fridge\ApiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;
use Fridge\ApiBundle\Entity\GcmId;


class GcmIdRepository extends EntityRepository
{
    public function deleteAllButNLatest(UserInterface $user, $latest = 5)
    {
        $qb = $this->_em->createQueryBuilder();

        $toKeep = $this->createQueryBuilder('g')
            ->orderBy('g.id', 'desc')
            ->where($qb->expr()->eq('g.user', ':user'))
            ->setMaxResults($latest)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;

        $qb
            ->delete($this->_entityName)
            ->where($qb->expr()->notIn('id', ':id'))
            ->andWhere($qb->expr()->eq('g.user', ':user'))
            ->setParameter('id', array_map(function (GcmId $e) {
                return $e->getId();
            }, $toKeep))
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function deleteAll()
    {
        $this->_em->createQueryBuilder()
            ->delete($this->_entityName)
            ->getQuery()
            ->getResult()
        ;
    }

} 