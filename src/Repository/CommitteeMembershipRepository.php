<?php

namespace AppBundle\Repository;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeMembershipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeMembership::class);
    }

    /**
     * Returns whether or not the given adherent is already an host of at least
     * one committee.
     */
    public function hostCommittee(Adherent $adherent, Committee $committee = null): bool
    {
        $qb = $this->createQueryBuilder('cm');

        $qb
            ->select('COUNT(cm.uuid)')
            ->where($qb->expr()->in('cm.privilege', CommitteeMembership::getHostPrivileges()))
            ->andWhere('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
        ;

        if ($committee) {
            $qb
                ->andWhere('cm.committee = :committee')
                ->setParameter('committee', $committee)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult() >= 1;
    }

    /**
     * Returns whether or not the given adherent is already the supervisor of at
     * least one committee.
     */
    public function superviseCommittee(Adherent $adherent, Committee $committee = null)
    {
        $qb = $this->createQueryBuilder('cm');

        $qb
            ->select('COUNT(cm.uuid)')
            ->where('cm.privilege = :supervisor')
            ->andWhere('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->setParameter('supervisor', CommitteeMembership::COMMITTEE_SUPERVISOR)
        ;

        if ($committee) {
            $qb
                ->andWhere('cm.committee = :committee')
                ->setParameter('committee', $committee)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult() >= 1;
    }

    public function findMemberships(Adherent $adherent): CommitteeMembershipCollection
    {
        $query = $this
            ->createQueryBuilder('cm')
            ->where('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    public function findMembership(Adherent $adherent, Committee $committee): ?CommitteeMembership
    {
        $query = $this
            ->createMembershipQueryBuilder($adherent, $committee)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * Creates the query builder to fetch the membership relationship between
     * an adherent and a committee.
     */
    private function createMembershipQueryBuilder(Adherent $adherent, Committee $committee): QueryBuilder
    {
        return $this
            ->createQueryBuilder('cm')
            ->where('cm.adherent = :adherent')
            ->andWhere('cm.committee = :committee')
            ->setParameter('adherent', $adherent)
            ->setParameter('committee', $committee)
        ;
    }

    /**
     * Returns the number of host members for the given committee.
     */
    public function countHostMembers(Committee $committee): int
    {
        return $this->countMembers($committee, CommitteeMembership::getHostPrivileges());
    }

    public function countSupervisorMembers(Committee $committee): int
    {
        return $this->countMembers($committee, [CommitteeMembership::COMMITTEE_SUPERVISOR]);
    }

    public function countMembers(Committee $committee, array $privileges): int
    {
        return (int) $this->createQueryBuilder('cm')
            ->select('COUNT(cm.uuid)')
            ->where('cm.committee = :committee')
            ->andWhere('cm.privilege IN (:privileges)')
            ->setParameter('committee', $committee)
            ->setParameter('privileges', $privileges)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns the list of all hosts memberships of a committee.
     */
    public function findHostMemberships(Committee $committee): CommitteeMembershipCollection
    {
        return $this->findPrivilegedMemberships($committee, [CommitteeMembership::COMMITTEE_HOST]);
    }

    public function findSupervisor(Committee $committee): ?Adherent
    {
        return $this->findPrivilegedMembers($committee, [CommitteeMembership::COMMITTEE_SUPERVISOR])->get(0);
    }

    /**
     * Returns the list of all hosts memberships of a committee.
     */
    public function findHostMembers(Committee $committee): AdherentCollection
    {
        return $this->findPrivilegedMembers($committee, CommitteeMembership::getHostPrivileges());
    }

    /**
     * Finds the list of all committee followers memberships.
     *
     * @param Committee $committee    The committee
     * @param bool      $includeHosts Whether or not to include committee hosts as followers
     *
     * @return CommitteeMembershipCollection
     */
    public function findFollowerMemberships(Committee $committee, bool $includeHosts = true): CommitteeMembershipCollection
    {
        $privileges = [CommitteeMembership::COMMITTEE_FOLLOWER];
        if ($includeHosts) {
            $privileges = array_merge($privileges, CommitteeMembership::getHostPrivileges());
        }

        return $this->findPrivilegedMemberships($committee, $privileges);
    }

    /**
     * Finds the list of all committee followers.
     *
     * @param Committee $committee    The committee UUID
     * @param bool      $includeHosts Whether or not to include committee hosts as followers
     *
     * @return AdherentCollection
     */
    public function findFollowers(Committee $committee, bool $includeHosts = true): AdherentCollection
    {
        $privileges = [CommitteeMembership::COMMITTEE_FOLLOWER];
        if ($includeHosts) {
            $privileges = array_merge($privileges, CommitteeMembership::getHostPrivileges());
        }

        return $this->findPrivilegedMembers($committee, $privileges);
    }

    /**
     * Returns the list of all privileged memberships of a committee.
     *
     * @param Committee $committee  The committee
     * @param array     $privileges An array of privilege constants (see {@link : CommitteeMembership}
     *
     * @return CommitteeMembershipCollection
     */
    private function findPrivilegedMemberships(Committee $committee, array $privileges): CommitteeMembershipCollection
    {
        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->where('cm.committee = :committee')
            ->andWhere($qb->expr()->in('cm.privilege', $privileges))
            ->orderBy('cm.joinedAt', 'ASC')
            ->setParameter('committee', $committee)
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    /**
     * Returns the list of all privileged members of a committee.
     *
     * @param Committee $committee  The committee
     * @param array     $privileges An array of privilege constants (see {@link : CommitteeMembership}
     *
     * @return AdherentCollection
     */
    private function findPrivilegedMembers(Committee $committee, array $privileges): AdherentCollection
    {
        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->select('cm', 'adherent')
            ->leftJoin('cm.adherent', 'adherent')
            ->where('cm.committee = :committee')
            ->andWhere($qb->expr()->in('cm.privilege', $privileges))
            ->orderBy('cm.privilege', 'DESC')
            ->addOrderBy('cm.joinedAt', 'ASC')
            ->setParameter('committee', $committee)
            ->getQuery()
        ;

        return $this->createAdherentCollection($query);
    }

    /**
     * Returns the list of all members of a committee.
     */
    public function findMembers(Committee $committee): AdherentCollection
    {
        return $this->createAdherentCollection($this->createCommitteeMembershipsQueryBuilder($committee)->getQuery());
    }

    /**
     * Returns the list of all committee memberships of a committee.
     */
    public function findCommitteeMemberships(Committee $committee): CommitteeMembershipCollection
    {
        return new CommitteeMembershipCollection(
            $this
                ->createCommitteeMembershipsQueryBuilder($committee)
                ->addSelect('a')
                ->getQuery()
                ->getResult()
        );
    }

    /**
     * Creates a QueryBuilder instance to fetch memberships of a committee.
     *
     * @param Committee $committee The committee for which the memberships to fetch belong
     * @param string    $alias     The custom root alias for the query
     *
     * @return QueryBuilder
     */
    private function createCommitteeMembershipsQueryBuilder(Committee $committee, string $alias = 'cm'): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->leftJoin($alias.'.adherent', 'a')
            ->where($alias.'.committee = :committee')
            ->orderBy($alias.'.privilege', 'DESC')
            ->addOrderBy('a.firstName', 'ASC')
            ->setParameter('committee', $committee)
        ;
    }

    /**
     * Creates an AdherentCollection instance with the results of a Query.
     *
     * The query must return a list of CommitteeMembership entities.
     *
     * @param Query $query The query to execute
     *
     * @return AdherentCollection
     */
    private function createAdherentCollection(Query $query): AdherentCollection
    {
        return new AdherentCollection(
            array_map(
                function (CommitteeMembership $membership) {
                    return $membership->getAdherent();
                },
                $query->getResult()
            )
        );
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostFirstName(string $firstName): array
    {
        return $this->findCommitteesUuid([
            'firstName' => $firstName,
            'privileges' => CommitteeMembership::getHostPrivileges(),
        ]);
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostLastName(string $lastName): array
    {
        return $this->findCommitteesUuid([
            'lastName' => $lastName,
            'privileges' => CommitteeMembership::getHostPrivileges(),
        ]);
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostEmailAddress(string $emailAddress): array
    {
        return $this->findCommitteesUuid([
            'emailAddress' => $emailAddress,
            'privileges' => CommitteeMembership::getHostPrivileges(),
        ]);
    }

    public function findCommitteesUuid(array $criteria): array
    {
        $qb = $this
            ->createQueryBuilder('cm')
            ->select('c.uuid')
            ->innerJoin('cm.committee', 'c')
        ;

        if (!empty($criteria['privileges'])) {
            $qb
                ->andWhere('cm.privilege IN (:privileges)')
                ->setParameter('privileges', (array) $criteria['privileges'])
            ;
        }

        if (isset($criteria['firstName']) || isset($criteria['lastName']) || isset($criteria['emailAddress'])) {
            $qb->innerJoin('cm.adherent', 'a');

            if (isset($criteria['firstName'])) {
                $qb
                    ->andWhere('a.firstName LIKE :firstName')
                    ->setParameter('firstName', '%'.$criteria['firstName'].'%')
                ;
            }

            if (isset($criteria['lastName'])) {
                $qb
                    ->andWhere('a.lastName LIKE :lastName')
                    ->setParameter('lastName', '%'.$criteria['lastName'].'%')
                ;
            }

            if (isset($criteria['emailAddress'])) {
                $qb
                    ->andWhere('a.emailAddress LIKE :emailAddress')
                    ->setParameter('emailAddress', '%'.$criteria['emailAddress'].'%')
                ;
            }
        }

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($qb->getQuery()->getArrayResult(), 'uuid'));
    }
}
