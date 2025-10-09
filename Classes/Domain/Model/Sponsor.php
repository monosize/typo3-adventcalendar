<?php

namespace Monosize\Adventcalendar\Domain\Model;

use Monosize\Entity\Domain\Model\Entity;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;

/**
 * This file is part of the "adventcalendar" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Winning sponsor model
 */
class Sponsor extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var \Monosize\Entity\Domain\Model\Entity
     */
    protected $entity;
    /**
     * @var int
     */
    protected $day;
    /**
     * @var \Monosize\Adventcalendar\Domain\Model\Calendar
     */
    protected $calendar;
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Monosize\Adventcalendar\Domain\Model\Prize>
     * @lazy
     */
    #[Lazy()]
    protected $prizes;

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->prizes = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return \Monosize\Entity\Domain\Model\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param \Monosize\Entity\Domain\Model\Entity $entity
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param int $day
     */
    public function setDay(int $day)
    {
        $this->day = $day;
    }

    /**
     * @return \Monosize\Adventcalendar\Domain\Model\Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param \Monosize\Adventcalendar\Domain\Model\Calendar $calendar
     */
    public function setCalendar(Calendar $calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Monosize\Adventcalendar\Domain\Model\Prize> $prizes
     */
    public function getPrizes()
    {
        return $this->prizes;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $prizes
     */
    public function setPrizes($prizes)
    {
        $this->prizes = $prizes;
    }

    /**
     * @param \Monosize\Adventcalendar\Domain\Model\Prize $prize
     */
    public function addPrize(Prize $prize)
    {
        $this->getPrizes()
            ->attach($prize);
    }
}
