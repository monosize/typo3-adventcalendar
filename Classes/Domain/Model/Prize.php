<?php

namespace Monosize\Adventcalendar\Domain\Model;

/**
 * This file is part of the "adventcalendar" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Winning price model
 */
class Prize extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var \Monosize\Adventcalendar\Domain\Model\Sponsor
     */
    protected $sponsor;

    /**
     * @var string
     */
    protected $winningNumber;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return \Monosize\Adventcalendar\Domain\Model\Sponsor
     */
    public function getSponsor()
    {
        return $this->sponsor;
    }

    /**
     * @param \Monosize\Adventcalendar\Domain\Model\Sponsor $sponsor
     */
    public function setSponsor(Sponsor $sponsor)
    {
        $this->sponsor = $sponsor;
    }

    /**
     * @return string
     */
    public function getWinningNumber()
    {
        return $this->winningNumber;
    }

    /**
     * @param string $winningNumber
     */
    public function setWinningNumber(string $winningNumber)
    {
        $this->winningNumber = $winningNumber;
    }

}
