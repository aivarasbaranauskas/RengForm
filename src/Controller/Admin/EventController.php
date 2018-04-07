<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\MultiEvent;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\EventTimeRepository;
use App\Repository\MultiEventRepository;
use App\Repository\WorkshopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EventController
 */
class EventController extends Controller
{
    /**
     * @var MultiEventRepository
     */
    private $multiEventRepository;

    /**
     * @var EventRepository
     */
    private $repository;

    /**
     * @var WorkshopRepository
     */
    private $workshopRepository;

    /**
     * @var EventTimeRepository
     */
    private $eventTimeRepository;

    /**
     * EventController constructor.
     * @param MultiEventRepository $multiEventRepository
     * @param EventRepository      $repository
     * @param WorkshopRepository   $workshopRepository
     * @param EventTimeRepository  $eventTimeRepository
     */
    public function __construct(
        MultiEventRepository $multiEventRepository,
        EventRepository $repository,
        WorkshopRepository $workshopRepository,
        EventTimeRepository $eventTimeRepository
    ) {
        $this->multiEventRepository = $multiEventRepository;
        $this->repository = $repository;
        $this->workshopRepository = $workshopRepository;
        $this->eventTimeRepository = $eventTimeRepository;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $user = $this->getUser();
        $multiEvents = $this->multiEventRepository->findBy(['owner' => $user]);
        $events = $this->repository->findBy(['owner' => $user]);

        return $this->render(
            'Admin/Event/index.html.twig',
            [
                'multiEvents' => $multiEvents,
                'events'      => $events,
            ]
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function create(Request $request)
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setOwner($this->getUser());
            $this->repository->save($event);

            return $this->redirectToRoute(
                'event_show',
                [
                    'eventId' => $event->getId(),
                ]
            );
        }

        return $this->render(
            'Admin/Event/create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @param         $eventId
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function update(Request $request, $eventId)
    {
        $event = $this->repository->find($eventId);

        if ($event === null) {
            throw new \Exception(sprintf('Event by id %s not found', $eventId));
        }

        $originalTimes = new ArrayCollection();
        foreach ($event->getTimes() as $time) {
            $originalTimes->add($time);
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($originalTimes as $time) {
                if (false === $event->getTimes()->contains($time)) {
                    $this->eventTimeRepository->remove($time);
                }
            }
            $this->repository->update($event);

            return $this->redirectToRoute(
                'event_show',
                [
                    'eventId' => $event->getId(),
                ]
            );
        }

        return $this->render(
            'Admin/Event/update.html.twig',
            [
                'form'  => $form->createView(),
                'event' => $event,
            ]
        );
    }

    /**
     * @param $eventId
     * @return Response
     * @throws \Exception
     */
    public function show($eventId)
    {
        $event = $this->multiEventRepository->find($eventId);

        if ($event !== null) {
            return $this->showMultiEvent($event);
        }

        $event = $this->repository->find($eventId);

        if ($event !== null) {
            return $this->showEvent($event);
        }

        throw new \Exception(sprintf('Event by id %s not found', $eventId));
    }

    /**
     * @param MultiEvent $event
     * @return Response
     */
    private function showMultiEvent(MultiEvent $event): Response
    {
        $workshops = $this->workshopRepository->getByEventId($event->getId());

        return $this->render(
            'Admin/MultiEvent/show.html.twig',
            [
                'event'     => $event,
                'workshops' => $workshops,
            ]
        );
    }

    /**
     * @param Event $event
     * @return Response
     */
    private function showEvent(Event $event): Response
    {
        return $this->render(
            'Admin/Event/show.html.twig',
            [
                'event' => $event,
            ]
        );
    }
}
