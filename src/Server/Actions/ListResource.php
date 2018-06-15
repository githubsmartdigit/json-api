<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/28/15
 * Time: 8:03 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Actions;

use Xooxx\Laravel\Access\Facades\Gate;
use Exception;
use Xooxx\JsonApi\Http\PaginatedResource;
use Xooxx\JsonApi\Http\Request\Parameters\Fields;
use Xooxx\JsonApi\Http\Request\Parameters\Included;
use Xooxx\JsonApi\Http\Request\Parameters\Page;
use Xooxx\JsonApi\Http\Request\Parameters\Sorting;
use Xooxx\JsonApi\JsonApiSerializer;
use Xooxx\JsonApi\Server\Actions\Traits\RequestTrait;
use Xooxx\JsonApi\Server\Actions\Traits\ResponseTrait;
use Xooxx\JsonApi\Server\Errors\Error;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Xooxx\JsonApi\Server\Errors\OufOfBoundsError;
use Xooxx\JsonApi\Server\Query\QueryException;
use Xooxx\JsonApi\Server\Query\QueryObject;
use Xooxx\JsonApi\Server\Actions\Exceptions\ForbiddenException;
/**
 * Class ListResource.
 */
class ListResource
{
    use RequestTrait;
    use ResponseTrait;
    /**
     * @var \Xooxx\JsonApi\Server\Errors\ErrorBag
     */
    protected $errorBag;
    /**
     * @var Page
     */
    protected $page;
    /**
     * @var Fields
     */
    protected $fields;
    /**
     * @var Sorting
     */
    protected $sorting;
    /**
     * @var Included
     */
    protected $included;
    /**
     * @var array
     */
    protected $filters;
    /**
     * @var JsonApiSerializer
     */
    protected $serializer;
    /**
     * @param JsonApiSerializer $serializer
     * @param Page              $page
     * @param Fields            $fields
     * @param Sorting           $sorting
     * @param Included          $included
     * @param array             $filters
     */
    public function __construct(JsonApiSerializer $serializer, Page $page, Fields $fields, Sorting $sorting, Included $included, $filters)
    {
        $this->serializer = $serializer;
        $this->errorBag = new ErrorBag();
        $this->page = $page;
        $this->fields = $fields;
        $this->sorting = $sorting;
        $this->included = $included;
        $this->filters = $filters;
    }
    /**
     * @param callable $totalAmountCallable
     * @param callable $resultsCallable
     * @param string   $route
     * @param string   $className
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(callable $totalAmountCallable, callable $resultsCallable, $route, $className)
    {
        try {
            Gate::authorize('view', $className);
            QueryObject::assert($this->serializer, $this->fields, $this->included, $this->sorting, $this->errorBag, $className);
            $totalAmount = $totalAmountCallable();
            if ($totalAmount > 0 && $this->page->size() > 0 && $this->page->number() > ceil($totalAmount / $this->page->size())) {
                return $this->resourceNotFound(new ErrorBag([new OufOfBoundsError($this->page->number(), $this->page->size())]));
            }
            $links = $this->pagePaginationLinks($route, $this->page->number(), $this->page->size(), $totalAmount, $this->fields, $this->sorting, $this->included, $this->filters);
            $results = $resultsCallable($this->filters);
            $paginatedResource = new PaginatedResource($this->serializer->serialize($results, $this->fields, $this->included), $this->page->number(), $this->page->size(), $totalAmount, $links);
            $response = $this->response($paginatedResource);
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e);
        }
        return $response;
    }
    /**
     * @param string   $route
     * @param int      $pageNumber
     * @param int      $pageSize
     * @param int      $totalPages
     * @param Fields   $fields
     * @param Sorting  $sorting
     * @param Included $included
     * @param array    $filters
     *
     * @return array
     */
    protected function pagePaginationLinks($route, $pageNumber, $pageSize, $totalPages, Fields $fields, Sorting $sorting, Included $included, $filters)
    {
        $next = $pageNumber + 1;
        $previous = $pageNumber - 1;
        $last = $pageSize == 0 ? 0 : ceil($totalPages / $pageSize);
        $links = array_filter(['self' => $pageNumber, 'first' => 1, 'next' => $next <= $last ? $next : null, 'previous' => $previous >= 1 ? $previous : null, 'last' => $last]);
        foreach ($links as &$numberedLink) {
            $numberedLink = $this->pagePaginatedRoute($route, $numberedLink, $pageSize, $fields, $sorting, $included, $filters);
        }

        return $links;
    }
    /**
     * Build the URL.
     *
     * @param string   $route
     * @param int      $pageNumber
     * @param int      $pageSize
     * @param Fields   $fields
     * @param Sorting  $sorting
     * @param Included $included
     * @param array    $filters
     *
     * @return string
     */
    protected function pagePaginatedRoute($route, $pageNumber, $pageSize, Fields $fields, Sorting $sorting, Included $included, $filters)
    {
        $fieldKeys = [];
        if (false === $fields->isEmpty()) {
            $fieldKeys = $fields->get();
            foreach ($fieldKeys as &$v) {
                $v = implode(',', $v);
            }
        }

        $queryParams = urldecode(http_build_query(array_filter(['page' => array_filter(['number' => $pageNumber, 'size' => $pageSize]), 'fields' => $fieldKeys, 'filter' => $filters, 'sort' => $sorting->get(), 'include' => $included->get()])));
        $expression = $route[strlen($route) - 1] === '?' || $route[strlen($route) - 1] === '&' ? '%s%s' : '%s?%s';
        return sprintf($expression, $route, $queryParams);
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