<?php

namespace AppBundle\CitizenProject;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Coordinator\Filter\CitizenProjectFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenProjectApprovalConfirmationMessage;
use AppBundle\Mailer\Message\CitizenProjectCommentMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationConfirmationMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationCoordinatorNotificationMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationNotificationMessage;
use AppBundle\Mailer\Message\CitizenProjectNewFollowerMessage;
use AppBundle\Mailer\Message\CitizenProjectRequestCommitteeSupportMessage;
use AppBundle\Repository\AdherentRepository;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CitizenProjectMessageNotifier implements EventSubscriberInterface
{
    const RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN = 100;
    const NOTIFICATION_PER_PAGE = MailerService::PAYLOAD_MAXSIZE;

    private $creationNotificationProducer;
    private $manager;
    private $mailer;
    private $committeeManager;
    private $router;
    private $adherentRepository;

    public function __construct(
        AdherentRepository $adherentRepository,
        ProducerInterface $creationNotificationProducer,
        CitizenProjectManager $manager,
        MailerService $mailer,
        CommitteeManager $committeeManager,
        RouterInterface $router
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->creationNotificationProducer = $creationNotificationProducer;
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->committeeManager = $committeeManager;
        $this->router = $router;
    }

    public function onCitizenProjectApprove(CitizenProjectWasApprovedEvent $event): void
    {
        $this->sendCreatorApprove($event->getCitizenProject());
        $this->sendAskCommitteeSupport($event->getCitizenProject());
    }

    public function onCitizenProjectCreation(CitizenProjectWasCreatedEvent $event): void
    {
        $creator = $event->getCreator();
        $citizenProject = $event->getCitizenProject();

        $this->sendCreatorCreationConfirmation($creator, $citizenProject);
        $this->sendCoordinatorCreationValidation($creator, $citizenProject);
    }

    public function onCitizenProjectFollowerAdded(CitizenProjectFollowerAddedEvent $followerAddedEvent): void
    {
        if (!$hosts = $this->manager->getCitizenProjectAdministrators($followerAddedEvent->getCitizenProject())->toArray()) {
            return;
        }

        $this->mailer->sendMessage(CitizenProjectNewFollowerMessage::create(
            $followerAddedEvent->getCitizenProject(),
            $hosts,
            $followerAddedEvent->getNewFollower()
        ));
    }

    public function sendCommentCreatedEmail(CitizenProjectCommentEvent $commentCreatedEvent): void
    {
        if ($commentCreatedEvent->isSendMail()) {
            foreach ($this->getOptinCitizenProjectFollowersChunks($commentCreatedEvent->getCitizenProject()) as $chunk) {
                $this->mailer->sendMessage(CitizenProjectCommentMessage::create($commentCreatedEvent->getComment(), $chunk));
            }
        }
    }

    public function sendAdherentNotificationCreation(Adherent $adherent, CitizenProject $citizenProject, Adherent $creator): void
    {
        $this->mailer->sendMessage(CitizenProjectCreationNotificationMessage::create($adherent, $citizenProject, $creator));
    }

    private function sendCreatorApprove(CitizenProject $citizenProject): void
    {
        $this->manager->injectCitizenProjectCreator([$citizenProject]);
        $this->mailer->sendMessage(CitizenProjectApprovalConfirmationMessage::create($citizenProject));
    }

    private function sendCreatorCreationConfirmation(Adherent $creator, CitizenProject $citizenProject): void
    {
        $this->mailer->sendMessage(CitizenProjectCreationConfirmationMessage::create(
            $citizenProject,
            $creator,
            $this->generateUrl('app_citizen_action_manager_create', [
                'project_slug' => $citizenProject->getSlug(),
            ])
        ));
    }

    private function sendCoordinatorCreationValidation(Adherent $creator, CitizenProject $citizenProject): void
    {
        $coordinators = $this->adherentRepository->findCoordinatorsByCitizenProject($citizenProject);

        foreach ($coordinators as $coordinator) {
            $this->mailer->sendMessage(
                CitizenProjectCreationCoordinatorNotificationMessage::create(
                    $citizenProject,
                    $creator,
                    $coordinator,
                    $this->generateUrl('app_coordinator_citizen_project', [
                        CitizenProjectFilter::PARAMETER_STATUS => CitizenProject::PENDING,
                    ])
                )
            );
        }
    }

    private function sendAskCommitteeSupport(CitizenProject $citizenProject): void
    {
        $this->manager->injectCitizenProjectCreator([$citizenProject]);
        foreach ($citizenProject->getPendingCommitteeSupports() as $committeeSupport) {
            if (!$committeeSupervisor = $this->committeeManager->getCommitteeSupervisor($committeeSupport->getCommittee())) {
                continue;
            }

            $this->mailer->sendMessage(
                CitizenProjectRequestCommitteeSupportMessage::create(
                    $citizenProject,
                    $committeeSupervisor,
                    $this->generateUrl('app_citizen_project_committee_support', [
                        'slug' => $citizenProject->getSlug(),
                    ])
                )
            );
        }
    }

    private function getOptinCitizenProjectFollowersChunks(CitizenProject $committee): array
    {
        return array_chunk(
            $this->manager->getCitizenProjectMembers($committee)->toArray(),
            MailerService::PAYLOAD_MAXSIZE
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CITIZEN_PROJECT_CREATED => ['onCitizenProjectCreation', -128],
            Events::CITIZEN_PROJECT_APPROVED => ['onCitizenProjectApprove', -128],
            Events::CITIZEN_PROJECT_FOLLOWER_ADDED => ['onCitizenProjectFollowerAdded', -128],
            Events::CITIZEN_PROJECT_COMMENT_CREATED => ['sendCommentCreatedEmail', -128],
        ];
    }

    private function generateUrl(string $name, array $parameters = []): string
    {
        return $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
