<?php

defined('TYPO3_MODE') or die('Access denied.');

return [
    'prettypreview-load-preview-content' => [
        'path' => '/prettypreview/load-preview-content',
        'target' => \Jar\PrettyPreview\Controller\AjaxController::class . '::renderPreviewAction',
    ]
];