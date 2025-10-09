<?php

namespace Monosize\Adventcalendar\Domain\Repository;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Winning Prize repository with all the callable functionality
 *
 */
class SponsorRepository extends Repository
{

    /**
     *
     */
    public function initializeObject()
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @param int $calendarId
     * @param int $entityUid
     * @param int $day
     *
     * @return object
     */
    public function existsSponsor($calendar, $entity, $day, $sponsorPid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false)->setStoragePageIds([$sponsorPid]);
        $query->matching(
            $query->logicalAnd(
                // $query->equals('pid', (int)$sponsorPid),
                $query->equals('entity', $entity),
                $query->equals('calendar',$calendar),
                $query->equals('day', (int)$day)
            )
        );

        return $query->execute()
            ->getFirst();
    }
}
