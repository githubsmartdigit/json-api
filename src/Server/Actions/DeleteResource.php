<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 12/2/15
 * Time: 9:38 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Actions;

use Xooxx\Laravel\Access\Facades\Gate;
use Exception;
use Xooxx\JsonApi\JsonApiSerializer;
use Xooxx\JsonApi\Server\Actions\Traits\ResponseTrait;
use Xooxx\JsonApi\Server\Errors\Error;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Xooxx\JsonApi\Server\Errors\NotFoundError;
use Xooxx\JsonApi\Server\Actions\Exceptions\ForbiddenException;
/**
 * Class DeleteResource.
 */
class DeleteResource
{
    use ResponseTrait;
    /**
     * @var \Xooxx\JsonApi\Server\Errors\ErrorBag
     */
    protected $errorBag;
    /**
     * @var JsonApiSerializer
     */
    protected $serializer;
    /**
     * @param JsonApiSerializer $serializer
     */
    public function __construct(JsonApiSerializer $serializer)
    {
        $this->serializer = $serializer;
        $this->errorBag = new ErrorBag();
    }
    /**
     * @param          $id
     * @param          $className
     * @param callable $findOneCallable
     * @param callable $deleteCallable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($id, $className, callable $findOneCallable, callable $deleteCallable)
    {
        try {
            $data = $findOneCallable();
            if (empty($data)) {
                $mapping = $this->serializer->getTransformer()->getMappingByClassName($className);
                return $this->resourceNotFound(new ErrorBag([new NotFoundError($mapping->getClassAlias(), $id)]));
            }
            Gate::authorize('delete' ,$data);
            $deleteCallable();
            return $this->resourceDeleted();
        } catch (Exception $e) {
            return $this->getErrorResponse($e);
        }
    }
    /**
     * @param Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getErrorResponse(Exception $e)
    {
        switch (get_class($e)) {
            case ForbiddenException::class:
                $response = $this->forbidden(new ErrorBag([new Error('Forbidden', $e->getMessage())]));
                break;
            default:
                $response = $this->errorResponse(new ErrorBag([new Error('Bad Request', 'Request could not be served.')]));
        }
        return $response;
    }

    /**
     * @return ErrorBag
     */
    public function getErrorBag(){
        return $this->errorBag;
    }
}