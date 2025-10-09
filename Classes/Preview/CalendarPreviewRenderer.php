<?php

declare(strict_types=1);

namespace Monosize\Adventcalendar\Preview;

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * Preview renderer for Adventcalendar plugin
 * For TYPO3 13.4 compatibility
 */
class CalendarPreviewRenderer extends StandardContentPreviewRenderer
{
    /**
     * Render the preview for Adventcalendar plugin
     *
     * @param GridColumnItem $item The grid column item containing the record
     * @return string Preview HTML
     */
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $record = $item->getRecord();
        $content = '';
        
        if ($record['list_type'] !== 'adventcalendar_calendar') {
            return $content;
        }

        // Get FlexForm data
        $flexFormData = [];
        if (!empty($record['pi_flexform'])) {
            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            $flexFormData = $flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
        }

        // Get plugin mode
        $mode = $flexFormData['switchableControllerActions'] ?? 'Calendar->calendar';
        
        $content .= '<strong>Adventcalendar Plugin</strong><br>';
        $content .= 'Mode: ' . $this->getReadableModeName($mode) . '<br>';

        // Show specific configuration based on mode
        switch ($mode) {
            case 'Calendar->calendar;Calendar->list':
                if (!empty($flexFormData['settings']['calendarUid'])) {
                    $content .= 'Calendar: ' . $flexFormData['settings']['calendarUid'] . '<br>';
                }
                break;
                
            case 'Calendar->list':
                if (!empty($flexFormData['settings']['calendarUid'])) {
                    $content .= 'Calendar: ' . $flexFormData['settings']['calendarUid'] . '<br>';
                }
                if (!empty($flexFormData['settings']['limit'])) {
                    $content .= 'Limit: ' . $flexFormData['settings']['limit'] . '<br>';
                }
                break;
                
            case 'Calendar->sponsors':
                $content .= 'Sponsors display<br>';
                break;
                
            case 'Calendar->category':
                if (!empty($flexFormData['settings']['category'])) {
                    $content .= 'Category: ' . $flexFormData['settings']['category'] . '<br>';
                }
                break;
        }

        // Show template variant if set
        if (!empty($flexFormData['settings']['template'])) {
            $content .= 'Template: ' . $flexFormData['settings']['template'] . '<br>';
        }

        return $content;
    }

    /**
     * Get readable mode name
     *
     * @param string $mode
     * @return string
     */
    protected function getReadableModeName(string $mode): string
    {
        switch ($mode) {
            case 'Calendar->calendar;Calendar->list':
                return 'Calendar with List';
            case 'Calendar->list':
                return 'List Only';
            case 'Calendar->sponsors':
                return 'Sponsors';
            case 'Calendar->category':
                return 'Category View';
            default:
                return $mode;
        }
    }
}