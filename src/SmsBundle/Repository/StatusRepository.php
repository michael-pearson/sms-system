<?php

namespace SmsBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SmsBundle\Entity\Status;

class StatusRepository extends EntityRepository
{
    /**
     * Fetches a status by it's shortname.
     *
     * @param string $_shortname
     * @return Status|null
     */
    public function findByShortname(string $_shortname):?Status
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