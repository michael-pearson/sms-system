<?php

namespace SmsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SmsBundle\Entity\Status;

class LoadStatuses implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // Add the queued status.
        $status = new Status();
        $status->setName('Queued');
        $status->setShortname('QUEUED');
        $status->setClass('secondary');

        // Persist the status.
        $manager->persist($status);

        // Add the sent status.
        $status = new Status();
        $status->setName('Sent');
        $status->setShortname('SENT');
        $status->setClass('success');

        // Persist the status.
        $manager->persist($status);

        // Add the failed status.
        $status = new Status();
        $status->setName('Failed');
        $status->setShortname('FAIL');
        $status->setClass('warning');

        // Persist the status.
        $manager->persist($status);

        // Make the changes.
        $manager->flush();
    }
}