<?php

declare(strict_types=1);

namespace Jar\PrettyPreview\Controller;

use InvalidArgumentException;
use Jar\PrettyPreview\Utilities\PreviewUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
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
    public function renderPreviewAction(ServerRequestInterface $request): Response
    {
        $uid = $request->getQueryParams()['uid'] ?? null;
        if ($uid === null) {
            throw new \InvalidArgumentException('Please provide a number', 1580585107);
        }

        $row = BackendUtility::getRecord('tt_content', $uid);
        $result = PreviewUtility::generateContentMarkup($row, 'tt_content');

        $data = ['result' => $result];
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($data));
        return $response;
    }
}
