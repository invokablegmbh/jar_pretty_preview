<?php

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;

defined('TYPO3') || die();
call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['jar_pretty_preview'] = 'EXT:jar_pretty_preview/Resources/Public/Css/style.css';

    // Fallback to Classic Hook if "fluidBasedPageModule" is deactivated
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidBasedPageModule'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['jar_pretty_preview'] = \Jar\PrettyPreview\Hooks\PreviewRendererHook::class;
    }
});
