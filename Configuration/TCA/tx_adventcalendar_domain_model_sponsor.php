<?php

// DB locallang
$ll = 'LLL:EXT:adventcalendar/Resources/Private/Language/locallang.xlf:tx_adventcalendar_domain_model_sponsor.';

// General locallang (TYPO3 13.4)
$llGeneral = 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:';

$GLOBALS['TCA']['tx_adventcalendar_domain_model_sponsor'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:adventcalendar/Resources/Private/Language/locallang.xlf:tx_adventcalendar_domain_model_sponsor',
        'label' => 'entity',
        'label_alt' => 'day',
        'label_alt_force' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'default_sortby' => 'ORDER BY day',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:adventcalendar/Resources/Public/Icons/NewsContent.svg',
        'searchFields' => 'uid,day',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
    ],
    'interface' => [
        'showRecordFieldList' => 'sorting,hidden,starttime,endtime,entity,day,calendar,prizes',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => $llGeneral . 'LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        $llGeneral . 'LGL.allLanguages',
                        -1,
                        'flags-multiple',
                    ],
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => $llGeneral . 'LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_adventcalendar_domain_model_sponsor',
                'foreign_table_where' => 'AND tx_adventcalendar_domain_model_sponsor.pid=###CURRENT_PID### AND tx_adventcalendar_domain_model_sponsor.sys_language_uid IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => $llGeneral . 'LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => $llGeneral . 'LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => $llGeneral . 'LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
            ],
        ],
        'sorting' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'crdate' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'entity' => [
            'exclude' => 1,
            'label' => $ll . 'entity',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_entity_domain_model_entity',
                'eval' => 'required',
                'maxitems' => 1
            ]
        ],
        'day' => [
            'exclude' => 0,
            'label' => $ll . 'day',
            'config' => [
                'type' => 'number',
                'size' => 2,
                'range' => ['lower' => 1, 'upper' => 31],
                'eval' => 'required'
            ]
        ],
        'calendar' => [
            'exclude' => 1,
            'label' => $ll . 'calendar',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_adventcalendar_domain_model_calendar',
                'eval' => 'required',
                'maxitems' => 1
            ]
        ],
        'prizes' => [
            'exclude' => 1,
            'label' => $ll . 'prizes',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_adventcalendar_domain_model_prize',
                'foreign_field' => 'sponsor',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 0,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ]
            ]
        ]
    ],
    'types' => [
        '1' => [
            'showitem' => 'entity, day, calendar, --linebreak--, prizes, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, starttime, endtime'
        ],
    ],
    'palettes' => [],
];
