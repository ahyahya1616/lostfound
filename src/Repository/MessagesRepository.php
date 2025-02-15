<?php

namespace App\Repository;

use App\Entity\AbstractObject;
use App\Entity\Messages;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messages::class);
    }

    public function findAllConversations(User $user)
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Requête SQL native pour obtenir les IDs des derniers messages de chaque conversation
        $sql = "
            SELECT MAX(m.id) as message_id
            FROM messages m
            WHERE m.sender_id = :userId OR m.receiver_id = :userId
            GROUP BY 
                LEAST(COALESCE(m.sender_id, 0), COALESCE(m.receiver_id, 0)),
                GREATEST(COALESCE(m.sender_id, 0), COALESCE(m.receiver_id, 0)),
                COALESCE(m.object_id, 0)
        ";
        
        $resultSet = $conn->executeQuery($sql, [
            'userId' => $user->getId()
        ]);
        
        $messageIds = $resultSet->fetchFirstColumn();
        
        if (empty($messageIds)) {
            return [];
        }

        // Utiliser Doctrine pour récupérer les entités Messages complètes
        return $this->createQueryBuilder('m')
            ->where('m.id IN (:messageIds)')
            ->setParameter('messageIds', $messageIds)
            ->orderBy('m.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findConversationByUsersAndObject(User $user1, User $user2, ?AbstractObject $object)
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
            ->andWhere('m.object = :object OR (m.object IS NULL AND :object IS NULL)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('object', $object)
            ->orderBy('m.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUnreadConversationMessages(User $user, User $sender, ?AbstractObject $object = null)
    {
        return $this->createQueryBuilder('m')
            ->where('m.receiver = :user')
            ->andWhere('m.sender = :sender')
            ->andWhere('m.isRead = false')
            ->andWhere('m.object = :object OR (m.object IS NULL AND :object IS NULL)')
            ->setParameter('user', $user)
            ->setParameter('sender', $sender)
            ->setParameter('object', $object)
            ->getQuery()
            ->getResult();
    }

    public function findConversationMessages(User $user1, User $user2, ?AbstractObject $object = null)
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
            ->andWhere('m.object = :object OR (m.object IS NULL AND :object IS NULL)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('object', $object)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countUnreadMessages(User $user)
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.receiver = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}