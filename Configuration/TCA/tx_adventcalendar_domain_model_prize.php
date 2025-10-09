<?php

// DB locallang
$ll = 'LLL:EXT:adventcalendar/Resources/Private/Language/locallang.xlf:tx_adventcalendar_domain_model_prize.';

// General locallang (TYPO3 13.4)
$llGeneral = 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:';

$GLOBALS['TCA']['tx_adventcalendar_domain_model_prize'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:adventcalendar/Resources/Private/Language/locallang.xlf:tx_adventcalendar_domain_model_prize',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'default_sortby' => 'ORDER BY crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:adventcalendar/Resources/Public/Icons/NewsContent.svg',
        'searchFields' => 'uid,name,winning_number',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
    ],
    'interface' => [
        'showRecordFieldList' => 'sorting,hidden,starttime,endtime,name,sponsor,winning_number',
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
                'foreign_table' => 'tx_adventcalendar_domain_model_prize',
                'foreign_table_where' => 'AND tx_adventcalendar_domain_model_prize.pid=###CURRENT_PID### AND tx_adventcalendar_domain_model_prize.sys_language_uid IN (-1,0)',
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
        'name' => [
            'exclude' => 0,
            'label' => $ll . 'name',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 3,
                'eval' => 'trim,required'
            ]
        ],
        'sponsor' => [
            'exclude' => 1,
            'label' => $ll . 'sponsor',
            'l10n_mode' => 'exclude',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_adventcalendar_domain_model_sponsor',
                'eval' => 'required',
                'maxitems' => 1
            ]
        ],
        'winning_number' => [
            'exclude' => 1,
            'label' => $ll . 'winning_number',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim'
            ]
        ]
    ],
    'types' => [
        '1' => [
            'showitem' => 'name, sponsor, winning_number, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, starttime, endtime'
        ],
    ],
    'palettes' => [],
];
