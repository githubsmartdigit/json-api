<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 8/18/15
 * Time: 11:19 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Actions\Traits;

use Xooxx\JsonApi\Http\PaginatedResource;
use Xooxx\JsonApi\Http\Response\BadRequest;
use Xooxx\JsonApi\Http\Response\ResourceConflicted;
use Xooxx\JsonApi\Http\Response\ResourceCreated;
use Xooxx\JsonApi\Http\Response\ResourceDeleted;
use Xooxx\JsonApi\Http\Response\ResourceNotFound;
use Xooxx\JsonApi\Http\Response\ResourceProcessing;
use Xooxx\JsonApi\Http\Response\ResourceUpdated;
use Xooxx\JsonApi\Http\Response\Response;
use Xooxx\JsonApi\Http\Response\UnprocessableEntity;
use Xooxx\JsonApi\Http\Response\UnsupportedAction;
use Xooxx\JsonApi\Http\Response\Forbidden;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
trait ResponseTrait
{
    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function errorResponse(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new BadRequest($errorBag)));
    }
    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function addHeaders($response)
    {
        return $response;
    }
    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceCreated($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceCreated($json)));
    }
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceDeleted()
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceDeleted()));
    }
    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceNotFound(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceNotFound($errorBag)));
    }
    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceConflicted(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceConflicted($errorBag)));
    }
    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceProcessing($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceProcessing($json)));
    }
    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceUpdated($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceUpdated($json)));
    }
    /**
     * @param string|PaginatedResource $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function response($json)
    {
        if ($json instanceof PaginatedResource) {
            $json = json_encode($json);
        }
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new Response($json)));
    }
    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unsupportedAction(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new UnsupportedAction($errorBag)));
    }
    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unprocessableEntity(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new UnprocessableEntity($errorBag)));
    }
    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function forbidden(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new Forbidden($errorBag)));
    }
}
