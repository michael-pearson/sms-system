<?php

namespace SmsBundle\Repository;

use Doctrine\ORM\EntityRepository;

class StatusRepository extends EntityRepository
{
    /**
     * Fetches a status by it's shortname.
     */
    public function findByShortname(string $_shortname)
    {
        // Prepare the doctrine query.
        $query = $this->getEntityManager()->createQuery("
            SELECT 		s
            FROM 		SmsBundle\Entity\Status s
            WHERE       s.shortname = :shortname
            ")
            ->setParameter('shortname', $_shortname);

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
}