<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'DFKI.dfki_staff_db',
    'Dfkistaffplugin',
    [
        'Staff' => 'list, list2, thumbs, show',
        'Publication' => 'list, show',
    ],
    // non-cacheable actions
    [
        'Staff' => '',
        'Publication' => 'list, show',
    ]
);