<?php

declare(strict_types=1);

namespace Jar\PrettyPreview\Renderer;

use Jar\PrettyPreview\Utilities\PreviewUtility;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of the JAR/PrettyPreview project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */


/** @package Jar\PrettyPreview\Renderer */
class PreviewRenderer extends StandardContentPreviewRenderer
{

    /**
     * Dedicated method for rendering preview header HTML for
     * the page module only. Receives $item which is an instance of
     * GridColumnItem which has a getter method to return the record.
     *
     * @param GridColumnItem
     * @return string
     */
    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {        
        return PreviewUtility::generateHeaderMarkup($item->getRecord(), 'tt_content');
    }

    /**
     * Dedicated method for rendering preview body HTML for
     * the page module only.
     *
     * @param GridColumnItem $item
     * @return string
     */
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        return '<div class="exampleContent">' . PreviewUtility::generateContentMarkupWithAjax($item->getRecord(), 'tt_content') . '</div>';
    }

    /**
     * Dedicated method for wrapping a preview header and body HTML.
     *
     * @param string $previewHeader
     * @param string $previewContent
     * @param GridColumnItem $item
     * @return string
     */
    public function wrapPageModulePreview($previewHeader, $previewContent, GridColumnItem $item): string
    {
        return '<div>' . $previewHeader . $previewContent . '</div>';
    }
}
