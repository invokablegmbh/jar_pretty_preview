<?php

call_user_func(function () {
    $cachingConfigurations = &$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
    if (is_array($cachingConfigurations)) {
        if (!array_key_exists('pretty_preview_content', $cachingConfigurations)) {
            $cachingConfigurations['pretty_preview_content']  = [
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                'options' => [
                    'defaultLifetime' => 804600,
                ],
                'groups' => ['system']
            ];
        }
    }
});