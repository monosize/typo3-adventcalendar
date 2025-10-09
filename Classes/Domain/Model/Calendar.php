<?php

namespace Monosize\Adventcalendar\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This file is part of the "adventcalendar" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Calendar model
 */
class Calendar extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var int
     */
    protected $year;
    /**
     * image
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @lazy
     */
    #[Lazy()]
    protected $image;
    /**
     * logo
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @lazy
     */
    #[Lazy()]
    protected $logo;
    /**
     * sponsors
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Monosize\Adventcalendar\Domain\Model\Sponsor>
     * @lazy
     */
    #[Lazy()]
    protected $sponsors;

    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->image = new ObjectStorage();
        $this->logo = new ObjectStorage();
        $this->sponsors = new ObjectStorage();
    }

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
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year)
    {
        $this->year = $year;
    }

    /**
     * Returns the images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $images
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $images
     */
    public function setImage($images)
    {
        $this->image = $images;
    }

    /**
     * Returns the images
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $images
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Monosize\Adventcalendar\Domain\Model\Sponsor> $sponsors
     */
    public function getSponsors()
    {
        return $this->sponsors;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sponsors
     */
    public function setSponsors($sponsors)
    {
        $this->sponsors = $sponsors;
    }
}
