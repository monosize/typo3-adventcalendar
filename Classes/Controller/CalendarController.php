<?php

namespace Monosize\Adventcalendar\Controller;

use Monosize\Adventcalendar\Domain\Repository\CalendarRepository;
use Monosize\Adventcalendar\Domain\Repository\PrizeRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Controller of news records
 *
 */
class CalendarController extends ActionController
{
    /**
     * @param int $day
     * @throws \Exception
     */
    public function calendarAction($day = null): ResponseInterface
    {
        $day = (int)$day;
        $calendar = $this->getCalendar();
        // $now = new \DateTime('24.12.2024');
        $now = new \DateTime();
        $actDay = 0;
        $selectedDay = 0;

        if ((int)$now->format('Ym') >= (int)$this->getCalendar()
                ->getYear() . '12') {
            $actDay = (int)$now->format('d');
            $selectedDay = $day > 0 && $day < 25 ? $day : 0;
            if ($selectedDay === 0) {
                $selectedDay = $actDay > 24 ? 0 : $actDay;
            } elseif ($selectedDay > $actDay) {
                $selectedDay = -1;
            }
        } else {
            if ($day !== 0) {
                $selectedDay = -1;
            }
        }
        $prizes = [];
        if ($selectedDay > 0) {
            $prizeRepository = GeneralUtility::makeInstance(PrizeRepository::class);
            $query = $prizeRepository->createQuery();

            $prizes = $query->matching($query->logicalAnd(
                $query->equals('sponsor.day', $selectedDay),
                $query->equals('sponsor.calendar', $calendar)
            ))
                ->setOrderings(['winningNumber' => QueryInterface::ORDER_ASCENDING])
                ->execute();
        }
        $this->view->assignMultiple([
            'calendar'          => $this->getCalendar(),
            'contentObjectData' => $this->request->getAttribute('currentContentObject')?->data ?? [],
            'now'               => $actDay,
            'day'               => $selectedDay,
            'prizes'            => $prizes,
        ]);
        
        return $this->htmlResponse();
    }

    /**
     * List action
     */
    public function listAction(): ResponseInterface
    {
        $calendar = $this->getCalendar();
        // $now = new \DateTime('24.12.2024');
        $now = new \DateTime();
        $actDay = 0;
        $act = (int)$now->format('Ym');
        $cal = (int)$this->getCalendar()
                ->getYear() . '12';
        if ($act >= $cal) {
            if ($act > $cal) {
                $actDay = 24;
            } else {
                $actDay = (int)$now->format('d');
                $actDay = $actDay > 24 ? 24 : $actDay;
            }
        }
        $prizes = [];
        if ($actDay > 0) {
            $prizeRepository = GeneralUtility::makeInstance(PrizeRepository::class);
            $query = $prizeRepository->createQuery();

            $prizes = $query->matching($query->logicalAnd(
                $query->lessThanOrEqual('sponsor.day', $actDay),
                $query->equals('sponsor.calendar', $calendar)
            ))
                ->setOrderings([
                    'winningNumber' => QueryInterface::ORDER_ASCENDING,
                ])
                ->execute();
        }
        $this->view->assignMultiple([
            'calendar'          => $this->getCalendar(),
            'contentObjectData' => $this->request->getAttribute('currentContentObject')?->data ?? [],
            'actDay'            => $actDay,
            'prizes'            => $prizes,
        ]);
        
        return $this->htmlResponse();
    }

    /**
     * Sponsors action
     */
    public function sponsorsAction(): ResponseInterface
    {
        $calendar = $this->getCalendar();
        $this->view->assignMultiple([
            'calendar'          => $this->getCalendar(),
            'contentObjectData' => $this->request->getAttribute('currentContentObject')?->data ?? [],
            'sponsors'          => $calendar->getSponsors(),
        ]);
        
        return $this->htmlResponse();
    }

    /**
     * Sponsors action
     */
    public function categoryAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @return \Monosize\Adventcalendar\Domain\Model\Calendar
     */
    public function getCalendar()
    {
        $calendarRepository = GeneralUtility::makeInstance(CalendarRepository::class);
        /** @var \Monosize\Adventcalendar\Domain\Model\Calendar $calendar */
        $calendar = $calendarRepository->findByUid((int)$this->settings['calendar']);

        return $calendar;
    }
}
