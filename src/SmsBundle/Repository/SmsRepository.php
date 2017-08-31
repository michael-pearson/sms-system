<?php

namespace SmsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SmsBundle\Entity\Sms;

class SmsRepository extends EntityRepository
{
    /**
     * Undocumented function
     *
     * @param int $_id
     * @return Sms|null
     */
    public function findSingleById(int $_id):?Sms
    {
        // Prepare the doctrine query.
        $query = $this->getEntityManager()->createQuery("
            SELECT      m
            FROM        SmsBundle\Entity\Sms m
            WHERE       m.id = :id
        ")
            ->setParameter('id', $_id);

        // Try and fetch the results.
        try
        {
            // Return the results.
            return $query->getSingleResult();
        }
        catch(\Doctrine\ORM\NoResultException $e)
        {
            // Return empty results.
            return null;
        }
    }

    /**
     * Fetches all of the messages sent by the system ordered by the date created.
     *
     * @return array|null
     */
    public function findAllOrderedByDateDesc():?array
    {
        // Prepare the doctrine query.
        $query = $this->getEntityManager()->createQuery("
            SELECT 		m
            FROM 		SmsBundle\Entity\Sms m 
            JOIN 	    m.user u
            ORDER BY    m.created_at DESC
        ");

        // Try and fetch the results.
        try
        {
            // Return the results.
            return $query->getResult();
        }
        catch(\Doctrine\ORM\NoResultException $e)
        {
            // Return empty results.
            return null;
        }
    }

    /**
     * Fetches all of the messages sent by the system ordered by the date created.
     *
     * @param int $_user
     * @return array|null
     */
    public function findByUserOrderedByDateDesc($_user):?array
    {
        // Prepare the doctrine query.
        $query = $this->getEntityManager()->createQuery("
            SELECT 		m
            FROM 		SmsBundle\Entity\Sms m 
            JOIN 	    m.user u
            WHERE       m.user = :user
            ORDER BY    m.created_at DESC
            ")
            ->setParameter('user', $_user);

        // Try and fetch the results.
        try
        {
            // Return the results.
            return $query->getResult();
        }
        catch(\Doctrine\ORM\NoResultException $e)
        {
            // Return empty results.
            return null;
        }
    }
}