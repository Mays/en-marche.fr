<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Mooc\Mooc;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMoocData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $mooc = new Mooc(
            'Faire sa fourchette un actce politique',
            'Description du MOOC, faire sa fourchette un actce politique'
        );

        $mooc->addChapter($this->getReference('mooc-chapter-1'));
        $mooc->addChapter($this->getReference('mooc-chapter-2'));
        $manager->persist($mooc);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadMoocChapterData::class,
        ];
    }
}
