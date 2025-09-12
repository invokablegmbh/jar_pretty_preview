<?php

declare(strict_types=1);

namespace Jar\PrettyPreview\Controller;

use InvalidArgumentException;
use Jar\PrettyPreview\Utilities\PreviewUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;

class AjaxController
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param ServerRequestInterface $request 
     * @return Response 
     * @throws InvalidArgumentException 
     * @throws RuntimeException 
     */
    public function renderPreviewAction(ServerRequestInterface $request): JsonResponse
    {
        $uids = $request->getQueryParams()['uids'] ?? null;
        if ($uids === null) {
            throw new \InvalidArgumentException('Please provide uids', 1580585107);
        }
        $uids = explode(',', $uids);

        $response = [];
        foreach($uids as $uid) {
            $response['result'][$uid] = $this->getSinglePreview($uid);
        }
        
        return new JsonResponse($response);
    }

    private function getSinglePreview($uid) {
        $row = BackendUtility::getRecord('tt_content', $uid);
        return PreviewUtility::generateContentMarkup($row, 'tt_content') ?? '';
    }
}
