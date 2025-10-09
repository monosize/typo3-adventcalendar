<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "adventcalendar".
 *
 * Auto generated 30-05-2018 12:30
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Advent Calendar',
    'description' => 'Displays an Advent calendar with winnings per day.',
    'category' => 'fe',
    'version' => '1.0.0',
    'state' => 'stable',
    'clearcacheonload' => true,
    'author' => 'Frank Rakow',
    'author_email' => 'frank.rakow@gestalten.de',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'php' => '8.3.0-8.3.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],

    'uploadfolder' => true,
    'createDirs' => null,
    'author_company' => null,
];

