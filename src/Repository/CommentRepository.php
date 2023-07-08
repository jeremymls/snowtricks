<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Parameters;
use App\Entity\Trick;
use App\Repository\Model\Stats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    use Stats;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function add(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCommentPaginator(Trick $trick, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.trick = :trick')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('trick', $trick)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($this->getCommentPerPage())
            ->getQuery()
        ;

        return new Paginator($query);
    }

    public function getCommentPerPage(): int
    {
        $parameter = $this->getEntityManager()->getRepository(Parameters::class)->findOneBy(['name' => 'commentsPerPage']);
        return $parameter ? $parameter->getValue() : 4;
    }
}
