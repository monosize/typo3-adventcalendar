<?php

declare(strict_types=1);

defined('TYPO3') or die();

// Plugin signature for adventcalendar extension
$pluginSignature = 'adventcalendar_calendar';

// Add FlexForm DataStructure pointer
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'recursive,select_key,pages';

// Register FlexForm DataStructure
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:adventcalendar/Configuration/FlexForms/Calendar.xml'
);

// Add pi_flexform field to the plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pi_flexform',
    'list',
    'after:header'
);

// Register PreviewRenderer for TYPO3 13.4
$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer'][$pluginSignature] = \Monosize\Adventcalendar\Preview\CalendarPreviewRenderer::class;