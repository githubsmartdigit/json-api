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
use Xooxx\JsonApi\JsonApiSerializer;
use Xooxx\JsonApi\Server\Actions\Traits\ResponseTrait;
use Xooxx\JsonApi\Server\Data\DataException;
use Xooxx\JsonApi\Server\Data\DataObject;
use Xooxx\JsonApi\Server\Errors\Error;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Xooxx\JsonApi\Server\Actions\Exceptions\ForbiddenException;
/**
 * Class CreateResource.
 */
class CreateResource
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
     * @param array    $data
     * @param          $className
     * @param callable $callable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(array $data, $className, callable $callable)
    {
        try {
            DataObject::assertPost($data, $this->serializer, $className, $this->errorBag);
            $values = DataObject::getAttributes($data, $this->serializer);
            Gate::authorize('create', $className);
            $model = $callable($data, $values, $this->errorBag);
            $response = $this->resourceCreated($this->serializer->serialize($model));
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e, $this->errorBag);
        }
        return $response;
    }
    /**
     * @param Exception $e
     * @param ErrorBag  $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getErrorResponse(Exception $e, ErrorBag $errorBag)
    {
        switch (get_class($e)) {
            case ForbiddenException::class:
                $response = $this->forbidden($errorBag);
                break;
            case DataException::class:
                $response = $this->unprocessableEntity($errorBag);
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