<?php

defined('TYPO3_MODE') || die();

call_user_func(function () {
    if (TYPO3_MODE === 'BE') {
        $GLOBALS['TBE_STYLES']['skins']['jar_pretty_preview'] = [
            'name' => 'jar_pretty_preview',
            'stylesheetDirectories' => [
                'css' => 'EXT:jar_pretty_preview/Resources/Public/Css/'
            ]
        ];

        // Fallback to Classic Hook if "fluidBasedPageModule" is deactivated
        if (!$GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['fluidBasedPageModule']) {            
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['jar_pretty_preview'] = \Jar\PrettyPreview\Hooks\PreviewRendererHook::class;
        }
    }
});
