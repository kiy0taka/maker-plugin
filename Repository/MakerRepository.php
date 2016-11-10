<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Maker\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Common\Constant;
use Plugin\Maker\Entity\Maker;

/**
 * Maker.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MakerRepository extends EntityRepository
{
    /**
     * Save method.
     *
     * @param Maker $Maker
     *
     * @return bool
     */
    public function save(Maker $Maker)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            if (!$Maker->getId()) {
                $rank = $this->createQueryBuilder('m')
                    ->select('MAX(m.rank)')
                    ->getQuery()
                    ->getSingleScalarResult();
                if (!$rank) {
                    $rank = 0;
                }
                $Maker->setRank($rank + 1);
                $Maker->setDelFlg(Constant::DISABLED);

                $em->createQueryBuilder()
                    ->update('Plugin\Maker\Entity\Maker', 'm')
                    ->set('m.rank', 'm.rank + 1')
                    ->where('m.rank > :rank')
                    ->setParameter('rank', $rank)
                    ->getQuery()
                    ->execute();
            }

            $em->persist($Maker);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();

            return false;
        }

        return true;
    }

    /**
     * Delete maker (del flg).
     *
     * @param Maker $Maker
     *
     * @return bool
     */
    public function delete(Maker $Maker)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $Maker->setDelFlg(Constant::ENABLED);
            $em->persist($Maker);
            $em->flush($Maker);

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();

            return false;
        }

        return true;
    }

    /**
     * Move rank.
     *
     * @param array $arrRank
     *
     * @return array
     *
     * @throws \Exception
     */
    public function moveMakerRank(array $arrRank)
    {
        $this->getEntityManager()->beginTransaction();
        $arrMoveRank = array();
        try {
            foreach ($arrRank as $makerId => $rank) {
                /* @var $Maker Maker */
                $Maker = $this->find($makerId);
                if ($Maker->getRank() == $rank) {
                    continue;
                }
                $arrMoveRank[$makerId] = $rank;
                $Maker->setRank($rank);
                $this->getEntityManager()->persist($Maker);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
        } catch (\Exception $e) {
            $this->getEntityManager()->rollback();
            throw $e;
        }

        return $arrMoveRank;
    }
}
