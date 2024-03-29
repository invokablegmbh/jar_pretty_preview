<?php

declare(strict_types=1);

namespace Jar\PrettyPreview\Utilities;

use FluidTYPO3\Vhs\ViewHelpers\DebugViewHelper;
use InvalidArgumentException;
use Jar\PrettyPreview\Renderer\PreviewRenderer;
use Jar\Utilities\Services\ReflectionService;
use Jar\Utilities\Utilities\BackendUtility;
use Jar\Utilities\Utilities\IteratorUtility;
use Jar\Utilities\Utilities\LocalizationUtility;
use Jar\Utilities\Utilities\StringUtility;
use Jar\Utilities\Utilities\TcaUtility;
use ReflectionException;
use TYPO3\CMS\Backend\Utility\BackendUtility as CoreBackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\TooDirtyException;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/*
 * This file is part of the JAR/PrettyPreview project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */


/** @package Jar\PrettyPreview\Utilities */
class PreviewUtility
{

	/**
	 * Register a Content Element for pretty preview
	 *
	 * @param string $cType
	 * @param array $configuration
	 * @return void
	 */
	public static function registerContentElement(string $cType, array $configuration = []): void
	{
		$GLOBALS['TCA']['tt_content']['types'][$cType]['previewRenderer'] = PreviewRenderer::class;
		$GLOBALS['TCA']['tt_content']['types'][$cType]['prettyPreviewConfiguration'] = $configuration;
	}

	/**
	 * @param array $row 
	 * @param string $table 
	 * @return string 
	 */
	public static function generateHeaderMarkup(array $row, string $table): string
	{
		$labelColumn = TcaUtility::getLabelFieldOfTable($table);
		$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
		$label = $row[$labelColumn];
		$title = empty($label) ? '' : ' <em>„' . $label . '“</em>';
		$title .= ' <span class="title-editicon">' . $iconFactory->getIcon('actions-open', Icon::SIZE_SMALL)->getMarkup() . '</span>';
		$ceName = BackendUtility::getWizardInformations($row['CType'])['title'];

		$headerContent = '<p class="j77contenttitle"><strong>' . BackendUtility::getWrappedEditLink($table, $row['uid'], $ceName . $title) . '</strong></p>';
		return $headerContent;
	}


	private static function generateCacheHashForRow(array $row): string
	{
		$cacheString = [
			'isAdmin' => $GLOBALS['BE_USER']->isAdmin() ? 1 : 0,
			'contentUid' => $row['uid'],
			'langUid' => $row['sys_language_uid'],
			'tstamp' => $row['tstamp'],
			'belang' => $GLOBALS['LANG']->lang ?? 'default'
		];

		return sha1(implode('+', $cacheString));
	}


	/**
	 * @param array $row 
	 * @param string $table 
	 * @return string 
	 */
	public static function generateContentMarkupWithAjax(array $row, string $table): string
	{
		$cacheSystem = GeneralUtility::makeInstance(CacheManager::class)->getCache('pretty_preview_content');
		$hash = static::generateCacheHashForRow($row);

		// If Preview allready exist in Cache dont load via Ajax
		if (($content = $cacheSystem->get($hash)) === false) {
			$uid = ((int) $row['uid']);
			$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
			$content = '
			<div data-pretty-ajax-loader-uid="' . $uid . '">
				<div class="pretty-spinner">' .  $iconFactory->getIcon('spinner-circle-dark', Icon::SIZE_SMALL)->render() . '</div>
				<script>				
					window.addEventListener("load", function() {
						setTimeout(function() {				
							(async function() {
								const request = await fetch(TYPO3.settings.ajaxUrls[\'prettypreview-load-preview-content\'] + \'&uid=' . $uid . '\');
								const data = await request.json();
								document.querySelector(\'[data-pretty-ajax-loader-uid="' . $uid . '"]\').outerHTML = data.result;
								' . self::imageLoadedJavascript($uid) . '
							})();
						}, 50);
					});
				</script>
			</div>';
		}

		return $content;
	}

	/**
	 * @param array $row 
	 * @param string $table 
	 * @return string 
	 */
	public static function generateContentMarkup(array $row, string $table): string
	{
		$cacheSystem = GeneralUtility::makeInstance(CacheManager::class)->getCache('pretty_preview_content');

		$hash = static::generateCacheHashForRow($row);

		if (($content = $cacheSystem->get($hash)) === false) {

			$reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
			
			// initialy, just load visible fields (f.e. no fields which a hidden through displayCond ..)
			$whitelist = [
				$table => TcaUtility::getVisibleColumnsByRow($table, $row),
			];

			$blacklist = [
				$table => [TcaUtility::getLabelFieldOfTable($table)],
			];

			$configuration = $GLOBALS['TCA'][$table]['types'][$row[TcaUtility::getTypeFieldOfTable($table)]]['prettyPreviewConfiguration'] ?? [];
			if(!empty($configuration)) {
				if(isset($configuration['tableColumnWhitelist'])) {
					$whitelist = $configuration['tableColumnWhitelist'];
				}
				if (isset($configuration['tableColumnBlacklist'])) {
					$blacklist = $configuration['tableColumnBlacklist'];
				}
			}
			
			$reflectionService->setTableColumnWhitelist($whitelist);			
			$reflectionService->setTableColumnBlacklist($blacklist);

			$buildingConfiguration = [
				'file' => [
					'showDetailedInformations' => true,
					'processingConfigurationForCrop' => [
						'desktop' => [
							'width' => '150c',
							'height' => '150c',
						],
					]
				]
			];
			$buildingConfiguration['file']['processingConfigurationForCrop']['medium']
				= $buildingConfiguration['file']['processingConfigurationForCrop']['tablet']
				= $buildingConfiguration['file']['processingConfigurationForCrop']['mobile']
				= $buildingConfiguration['file']['processingConfigurationForCrop']['desktop'];

			$reflectionService->setBuildingConfiguration($buildingConfiguration);


			$nestingDepth = ((int) ($configuration['nestingDepth'] ?? 2));


			$values = reset($reflectionService->buildArrayByRows([$row], $table, $nestingDepth));
			$definitions = $reflectionService->getTcaFieldDefinition();

			$icon = '';
			$iconIdentfier = BackendUtility::getWizardInformations($row['CType'])['iconIdentifier'];
			if (!empty($iconIdentfier)) {
				$iconFactory = GeneralUtility::makeInstance(IconFactory::class); 
				$icon = '<figure class="j77contenticonbg">' . $iconFactory->getIcon($iconIdentfier, Icon::SIZE_OVERLAY)->getMarkup() . '</figure>';
			}
			$content = '<div data-preview-ctype="' . $row['CType'] . '" data-pretty-content-uid="' . $row['uid'] .'">' . $icon . static::generateContentTable($table, $values, $definitions) . '</div>';
			$content .= '<script>'. self::imageLoadedJavascript($row['uid']) .'</script>';
			$cacheSystem->set($hash, $content, [], strtotime('+1week'));
		}

		return $content;
	}


	/**
	 * @param int $uid 
	 * @return string 
	 */
	protected static function imageLoadedJavascript(int $uid):string {
		if(empty($uid)) {
			return '';
		}
		return "
			(function() {				
				var images = document.querySelectorAll('[data-pretty-content-uid=\"" . $uid . "\"] .j77preview-image img');				
				if(images.length) {
					images.forEach(function(image) {
						var imageLoadedHandler = function() {
							image.classList.add('loaded');
						};
						if (image.complete) {
							imageLoadedHandler();
						} else {
							image.addEventListener('load', imageLoadedHandler);
							image.addEventListener('error', imageLoadedHandler);
						}
					});
				}				
			})();
		";
	}


	/**
	 * @param string $table 
	 * @param array $values Values of the content element
	 * @param array $definitions TCA definitions of the content element 
	 * @return string 
	 * @throws TooDirtyException 
	 * @throws ReflectionException 
	 */
	protected static function generateContentTable(string $table, array $values, array $definitions): string
	{
		if (empty($values)) {
			return '';
		}

		$result = '';
		$image = null;
		$iconFactory = GeneralUtility::makeInstance(IconFactory::class);

		$visibleFields = TcaUtility::getVisibleColumnsByRow($table, CoreBackendUtility::getRecord($table, $values['uid']));

		$subclass = '';
		foreach ($values as $column => $value) {
			$definition = $definitions[$table][$column] ?? [];
			$config = $definition['config'] ?? null;

			// skip empty and unused fields
			if (empty($config) || !$value || !in_array($column, $visibleFields)) {
				continue;
			}

			$label = LocalizationUtility::localize($definition['label']);
			if (empty($label)) {
				continue;
			}

			$content = '';
			$eval = GeneralUtility::trimExplode(',', strtolower($config['eval'] ?? ''));
			switch ($config['type']) {
				case 'passthrough':
				case 'slug':
				case 'flex':
					break;
				case 'input':
				case 'text':
				case 'link':
					$config['renderType'] = $config['type'] == 'link' ? 'inputLink' : $config['renderType'] ?? '';
					switch ($config['renderType'] ?? '') {
						case 'inputLink':
							$content = $iconFactory->getIcon('actions-link', Icon::SIZE_SMALL)->getMarkup() . ' ' .  htmlspecialchars($value['text']) . ' <em>(' . htmlspecialchars($value['url']) . ')</em>';
							break;
						case 'inputDateTime':
							if (in_array('time', $eval)) {
								// Time
								$content = $iconFactory->getIcon('actions-clock', Icon::SIZE_SMALL)->getMarkup() . ' ' .  $value['formatedTime'];
							} else  if (in_array('datetime', $eval)) {
								// DateTime
								$content = $iconFactory->getIcon('actions-calendar', Icon::SIZE_SMALL)->getMarkup() . ' ' .   $value['formatedDate'] . ' ' . $value['formatedTime'];
							} else  if (in_array('date', $eval)) {
								// Date 
								$content = $iconFactory->getIcon('actions-calendar-alternative', Icon::SIZE_SMALL)->getMarkup() . ' ' . $value['formatedDate'];
							}
							break;
						default:
							if (in_array('email', $eval)) {
								// E-Mail
								$content = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><g class="icon-color"><path d="M1 3.5v9c0 .3.2.5.5.5h13c.3 0 .5-.2.5-.5v-9c0-.3-.2-.5-.5-.5h-13c-.3 0-.5.2-.5.5zM8 10l1.6-1.3 3.8 3.2H2.7l3.8-3.2L8 10zm0-1.3L2.7 4.1h10.7L8 8.7zM2 4.9L5.7 8 2 11.1V4.9zm12 6.2L10.3 8 14 4.9v6.2z"/></g></svg> ' .  htmlspecialchars($value);
							} else  if (in_array('int', $eval)) {
								// Int
								$content = '<strong>#</strong> ' . htmlspecialchars((string) $value);
							} else {
								// All other Texts							
								$content = $iconFactory->getIcon('actions-viewmode-list', Icon::SIZE_SMALL)->getMarkup() . ' ' .   htmlspecialchars((string) str_replace(['&nbsp;', '&shy;'], [' ', ''], StringUtility::crop(StringUtility::ripTags($value))));
							}
					}
					break;
				case 'check':
					$content = $iconFactory->getIcon('actions-check-square', Icon::SIZE_SMALL)->getMarkup() . '&nbsp;';
					break;
				case 'radio':
				case 'select':
				case 'group':
					$foreignTable = ($config['type'] === 'group' && $config['internal_type'] === 'db') ? $config['allowed'] : ($config['foreign_table'] ?? null);
					$contentItems = [];
					if (empty($foreignTable)) {
						// not related to other Tables
						$remappedItems = [];
						foreach (($config['items'] ?? []) as $item) {
							$remappedItems[$item['label']] = LocalizationUtility::localize((string) $item['value']);
						}
						$valueList = is_array($value) ? $value : [$value];
						foreach ($valueList as $valueListItem) {
							if (!empty($remappedItems[$valueListItem])) {
								$contentItems[] = $iconFactory->getIcon('actions-check-square', Icon::SIZE_SMALL)->getMarkup() . ' ' . htmlspecialchars((string) $remappedItems[$valueListItem]);
							}
						}
					} else {
						// related to other Tables
						$value = IteratorUtility::filter($value, function ($item) {
							return !!$item;
						});
						if (empty($value)) {
							break;
						}
						$labelColumn = TcaUtility::getLabelFieldOfTable($foreignTable);
						foreach ($value as $valueListItem) {
							$icon = $iconFactory->getIconForRecord($foreignTable, $valueListItem, Icon::SIZE_SMALL)->getMarkup() ?? '';
							$title = $valueListItem[$labelColumn] ?? '';
							$contentItems[] = $icon . ' ' . $title;
						}
					}
					$content = implode(', ', $contentItems);
					break;
				case 'inline':
				case 'file':
					if ($config['foreign_table'] === 'sys_file_reference' || $config['type'] == 'file') {
						$contentItems = [];
						foreach ($value as $file) {
							if (empty($file)) {
								continue;
							}
							if (strpos($file['mimetype'], 'image') !== false) {
								// Image Handling
								if (!empty($image)) {
									// just use the first image as preview image
									continue;
								}
								if (isset($file['cropped'])) {
									$image = reset($file['cropped']);
								} else {
									$image = $file['url'];
								}
							} else {
								$contentItems[] = $iconFactory->getIconForFileExtension($file['extension'], Icon::SIZE_SMALL)->getMarkup() . ' ' . htmlspecialchars((string) $file['name']);
							}
						}
						$content = implode(', ', $contentItems);
					} else {
						$subclass = 'relation';
						foreach ($value as $item) {
							if (empty($item)) {
								continue;
							}
							$content .= static::generateContentTable($config['foreign_table'], $item, $definitions);
						}
					}
					break;
				default:
					$content = 'NOT DEFINED: ' . (string) (isset($value[0]['uid']) ? $value[0]['uid'] : $value);
			}

			if (empty(trim(strip_tags($content)))) {
				continue;
			}

			$result .= '<tr><th>' . $label . '</th><td><div class="'.$subclass.'">' . $content . '</div></td></tr>';
		}

		if (!empty(trim(strip_tags($result)))) {
			$result = '<table  class="table table-striped table-sm j77content-table"><tbody>' . $result . '</tbody></table>';
		}

		if (!empty($image)) {
			if(strpos($image, 'http') !== 0 && strpos($image, '/') !== 0) {
				$image = '/' . $image;
			}
			$result = '<ul class="j77content-preview-withimage"><li class="j77preview-image"><figure>
				<img src="' . $image . '" alt="">
				<span class="icon icon-size-default icon-state-default icon-spin">
    <span class="icon-markup">
        <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 16 16"><g fill="#212121"><path d="M8 15c-3.86 0-7-3.141-7-7 0-3.86 3.14-7 7-7 3.859 0 7 3.14 7 7 0 3.859-3.141 7-7 7zM8 3C5.243 3 3 5.243 3 8s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" opacity=".3"/><path d="M14 9a1 1 0 0 1-1-1c0-2.757-2.243-5-5-5a1 1 0 0 1 0-2c3.859 0 7 3.14 7 7a1 1 0 0 1-1 1z"/></g></svg>
    </span>
</span>
				</figure></li><li class="j77preview-tablecontainer">' . $result . '</li></ul>';
		}


		if (!empty(trim(strip_tags($result)))) {
			$result = '<div class="j77preview-outputcontainer">' . $result . '</div>';
		}

		return $result;
	}
}
