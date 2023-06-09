<?php

namespace App\EventListener;

use App\Entity\Formations;
use App\Repository\FormationsRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Toiba\FullCalendarBundle\Entity\Event;
use Toiba\FullCalendarBundle\Event\CalendarEvent;

class FullCalendarListener
{
    private $formationsRepository;
    private $router;

    public function __construct(
        FormationsRepository $formationsRepository,
        UrlGeneratorInterface $router
    ) {
        $this->formationsRepository = $formationsRepository;
        $this->router = $router;
    }

    public function loadEvents(CalendarEvent $calendar): void
    {
        $startDate = $calendar->getStart();
        $endDate = $calendar->getEnd();
        $filters = $calendar->getFilters();

        // Modify the query to fit to your entity and needs
        // Change b.beginAt by your start date in your custom entity
        $formations = $this->formationsRepository
            ->createQueryBuilder('formations')
            ->where('formations.dateDebut BETWEEN :startDate and :endDate')
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
        ;

        foreach ($formations as $formation) {
            // this create the events with your own entity (here booking entity) to populate calendar
            $formationEvent = new Event(
                $formation->getNom(),
                $formation->getDateDebut(),
                $formation->getDateFin() // If the end date is null or not defined, a all day event is created.
            );

            /*
             * Optional calendar event settings
             *
             * For more information see : Toiba\FullCalendarBundle\Entity\Event
             * and : https://fullcalendar.io/docs/event-object
             */
            // $bookingEvent->setUrl('http://www.google.com');
            // $bookingEvent->setBackgroundColor($booking->getColor());
            // $bookingEvent->setCustomField('borderColor', $booking->getColor());

            $formationEvent->setUrl(
                $this->router->generate('formations_show', [
                    'id' => $formation->getId(),
                ])
            );

            // finally, add the booking to the CalendarEvent for displaying on the calendar
            $calendar->addEvent($formationEvent);
        }
    }
}
