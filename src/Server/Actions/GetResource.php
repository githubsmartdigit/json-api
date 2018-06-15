<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 12/2/15
 * Time: 9:37 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Actions;

use Xooxx\Laravel\Access\Facades\Gate;
use Exception;
use Xooxx\JsonApi\Http\Request\Parameters\Fields;
use Xooxx\JsonApi\Http\Request\Parameters\Included;
use Xooxx\JsonApi\Http\Request\Parameters\Sorting;
use Xooxx\JsonApi\JsonApiSerializer;
use Xooxx\JsonApi\Server\Actions\Traits\RequestTrait;
use Xooxx\JsonApi\Server\Actions\Traits\ResponseTrait;
use Xooxx\JsonApi\Server\Errors\Error;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Xooxx\JsonApi\Server\Errors\NotFoundError;
use Xooxx\JsonApi\Server\Query\QueryException;
use Xooxx\JsonApi\Server\Query\QueryObject;
use Xooxx\JsonApi\Server\Actions\Exceptions\ForbiddenException;
/**
 * Class GetResource.
 */
class GetResource
{
    use RequestTrait;
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
     * @var Fields
     */
    protected $fields;
    /**
     * @var Included
     */
    protected $included;
    /**
     * @param JsonApiSerializer $serializer
     * @param Fields            $fields
     * @param Included          $included
     */
    public function __construct(JsonApiSerializer $serializer, Fields $fields, Included $included)
    {
        $this->serializer = $serializer;
        $this->errorBag = new ErrorBag();
        $this->fields = $fields;
        $this->included = $included;
    }
    /**
     * @param string|int $id
     * @param string     $className
     * @param callable   $callable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($id, $className, callable $callable)
    {
        try {
            QueryObject::assert($this->serializer, $this->fields, $this->included, new Sorting(), $this->errorBag, $className);
            $data = $callable();
            if (empty($data)) {
                $mapping = $this->serializer->getTransformer()->getMappingByClassName($className);
                return $this->resourceNotFound(new ErrorBag([new NotFoundError($mapping->getClassAlias(), $id)]));
            }
            Gate::authorize('view', $data);
            $response = $this->response($this->serializer->serialize($data, $this->fields, $this->included));
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e);
        }
        return $response;
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
                $response = $this->forbidden($this->errorBag);
                break;
            case QueryException::class:
                $response = $this->errorResponse($this->errorBag);
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