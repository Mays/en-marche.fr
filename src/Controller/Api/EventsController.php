<?php

namespace AppBundle\Controller\Api;

use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\EventRegistrationRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/events")
 */
class EventsController extends Controller
{
    /**
     * @Route("", name="api_committees_events")
     * @Method("GET")
     */
    public function getUpcomingCommitteesEventsAction(Request $request): Response
    {
        return new JsonResponse($this->get('app.api.event_provider')->getUpcomingEvents($request->query->getInt('type')));
    }

    /**
     * @Route("/count-by-month", name="app_committee_events_count_by_month")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsCountInReferentManagedAreaAction(Request $request, EventRepository $eventRepository, EventRegistrationRepository $eventRegistrationRepository, CommitteeRepository $committeeRepository): Response
    {
        $referent = $this->getUser();
        try {
            $filter = StatisticsParametersFilter::createFromRequest($request, $committeeRepository);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $events = $eventRepository->countCommitteeEventsInReferentManagedArea($referent, $filter);
        $eventPariticipants = $eventRegistrationRepository->countEventPariticipantsInReferentManagedArea($this->getUser(), $filter);

        return new JsonResponse(array_merge_recursive($events, $eventPariticipants));
    }

    /**
     * @Route("/count", name="app_events_count")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function allTypesEventsCountInReferentManagedAreaAction(EventRepository $eventRepository, CitizenActionRepository $citizenActionRepository): Response
    {
        $referent = $this->getUser();
        $events = $eventRepository->countCommitteeEventsInReferentManagedArea($referent);
        $referentEvents = $eventRepository->countReferentEventsInReferentManagedArea($referent);
        $total = $eventRepository->countTotalEventsInReferentManagedAreaForCurrentMonth($referent);

        return new JsonResponse([
            'current_total' => $total,
            'monthly' => array_merge_recursive($events, $referentEvents),
        ]);
    }

    /**
     * @Route("/count_participants", name="app_committee_events_count_participants")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsCountInReferentManagedArea(EventRepository $eventRepository, EventRegistrationRepository $eventRegistrationRepository): Response
    {
        $referent = $this->getUser();

        return new JsonResponse([
            'total' => $eventRepository->countParticipantsInReferentManagedArea($referent),
            'monthly' => array_merge_recursive(
                $eventRegistrationRepository->countEventPariticipantsInReferentManagedArea($referent),
                $eventRegistrationRepository->countEventParticipantsInReferentManagedAreaInAtLeastOneCommittee($referent)
            ),
        ]);
    }
}
