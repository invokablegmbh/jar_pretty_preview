<?php

declare(strict_types=1);

namespace Jar\PrettyPreview\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Jar\PrettyPreview\Renderer\PreviewRenderer;
use Jar\PrettyPreview\Services\RegisterService;
use Jar\PrettyPreview\Utilities\PreviewUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PreviewRendererHook implements PageLayoutViewDrawItemHookInterface
{

    /**
     * Preprocesses the preview rendering of a content element of type "My new content element"
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
     * @param bool $drawItem Whether to draw the item using the default functionality
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     *
     * @return void
     */
    public function preProcess(
        PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ) {
        $tcaTypeDefinition = $GLOBALS['TCA']['tt_content']['types'][$row['CType']];
        // use the same settings like used in "fluidBasedPageModule"
        if(!empty($tcaTypeDefinition['previewRenderer']) && $tcaTypeDefinition['previewRenderer'] === PreviewRenderer::class) {
            $headerContent = PreviewUtility::generateHeaderMarkup($row, 'tt_content');
            $itemContent = PreviewUtility::generateContentMarkupWithAjax($row, 'tt_content');
            $drawItem = false;
        }
    }
}