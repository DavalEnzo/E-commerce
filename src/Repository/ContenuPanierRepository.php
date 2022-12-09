<?php

namespace App\Repository;

use App\Entity\ContenuPanier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContenuPanier>
 *
 * @method ContenuPanier|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContenuPanier|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContenuPanier[]    findAll()
 * @method ContenuPanier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContenuPanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContenuPanier::class);
    }

    public function save(ContenuPanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContenuPanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * On cherche si le produit est déjà dans le panier pour éviter les doublons
     * @return ContenuPanier[] Returns an array of ContenuPanier objects
     * @throws NonUniqueResultException
     */
    public function produitAlreadyInContenuPanier($idPanier, $idProduit): ?ContenuPanier
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.panier = :idPanier')
            ->andWhere('c.produit = :idProduit')
            ->setParameter('idProduit', $idProduit)
            ->setParameter('idPanier', $idPanier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

//    public function findOneBySomeField($value): ?ContenuPanier
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
