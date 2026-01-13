<?php

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;

defined('TYPO3') || die();
call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['jar_pretty_preview'] = 'EXT:jar_pretty_preview/Resources/Public/Css/style.css';
});
