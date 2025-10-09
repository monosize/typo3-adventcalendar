<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

// TYPO3 13.4 native plugin registration for backend
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'adventcalendar',
    'Calendar',
    'LLL:EXT:adventcalendar/Resources/Private/Language/locallang_be.xlf:plugin.calendar'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('adventcalendar', 'Configuration/TypoScript', 'Monosize AdventCalendar');
