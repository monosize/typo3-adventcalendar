<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

// TYPO3 13.4 native plugin registration
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'adventcalendar',
    'Calendar',
    [
        \Monosize\Adventcalendar\Controller\CalendarController::class => 'calendar,list,sponsors,category'
    ],
    [
        \Monosize\Adventcalendar\Controller\CalendarController::class => 'calendar,list'
    ]
);

// Add PageTS configuration
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    @import \'EXT:adventcalendar/Configuration/PageTS/PageTS.typoscript\'
    ');

// Icon registration moved to Configuration/Icons.php for TYPO3 13.4
