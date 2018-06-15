<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/28/15
 * Time: 12:12 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Query;

use Xooxx\JsonApi\Http\Request\Parameters\Fields;
use Xooxx\JsonApi\Http\Request\Parameters\Included;
use Xooxx\JsonApi\Http\Request\Parameters\Sorting;
use Xooxx\JsonApi\JsonApiSerializer;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Xooxx\JsonApi\Server\Errors\InvalidParameterError;
use Xooxx\JsonApi\Server\Errors\InvalidParameterMemberError;
use Xooxx\JsonApi\Server\Errors\InvalidSortError;
/**
 * Class QueryObject.
 */
class QueryObject
{
    /**
     * @param JsonApiSerializer $serializer
     * @param Fields $fields
     * @param Included $included
     * @param Sorting $sort
     * @param ErrorBag $errorBag
     * @param string $className
     *
     * @throws QueryException
     * @throws \ReflectionException
     */
    public static function assert(JsonApiSerializer $serializer, Fields $fields, Included $included, Sorting $sort, ErrorBag $errorBag, $className)
    {
        self::validateQueryParamsTypes($serializer, $fields, 'Fields', $errorBag);
        self::validateIncludeParams($serializer, $included, 'include', $errorBag);
        if (!empty($className) && false === $sort->isEmpty()) {
            self::validateSortParams($serializer, $className, $sort, $errorBag);
        }
        if ($errorBag->count() > 0) {
            throw new QueryException();
        }
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param Fields $fields
     * @param                   $paramName
     * @param ErrorBag $errorBag
     * @throws \ReflectionException
     */
    protected static function validateQueryParamsTypes(JsonApiSerializer $serializer, Fields $fields, $paramName, ErrorBag $errorBag)
    {
        if (false === $fields->isEmpty()) {
            $transformer = $serializer->getTransformer();
            $validateFields = $fields->types();
            foreach ($validateFields as $key => $type) {
                $mapping = $transformer->getMappingByAlias($type);
                if (null !== $mapping) {
                    $members = array_merge(array_combine($mapping->getProperties(), $mapping->getProperties()), $mapping->getAliasedProperties());
                    $invalidMembers = array_diff($fields->members($type), $members);
                    foreach ($invalidMembers as $extraField) {
                        $errorBag->offsetSet(null, new InvalidParameterMemberError($extraField, $type, strtolower($paramName)));
                    }
                    unset($validateFields[$key]);
                }
            }
            if (false === empty($validateFields)) {
                foreach ($validateFields as $type) {
                    $errorBag->offsetSet(null, new InvalidParameterError($type, strtolower($paramName)));
                }
            }
        }
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param Included $included
     * @param string $paramName
     * @param ErrorBag $errorBag
     * @throws \ReflectionException
     */
    protected static function validateIncludeParams(JsonApiSerializer $serializer, Included $included, $paramName, ErrorBag $errorBag)
    {
        $transformer = $serializer->getTransformer();
        foreach ($included->get() as $resource => $data) {
            if ($transformer->getMappingByAlias($resource)->isNull()) {
                $errorBag->offsetSet(null, new InvalidParameterError($resource, strtolower($paramName)));
                continue;
            }
            if (is_array($data)) {
                foreach ($data as $subResource) {
                    if ($transformer->getMappingByAlias($subResource)->isNull()) {
                        $errorBag->offsetSet(null, new InvalidParameterError($subResource, strtolower($paramName)));
                    }
                }
            }
        }
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param string $className
     * @param Sorting $sorting
     * @param ErrorBag $errorBag
     * @throws \ReflectionException
     */
    protected static function validateSortParams(JsonApiSerializer $serializer, $className, Sorting $sorting, ErrorBag $errorBag)
    {
        if (false === $sorting->isEmpty()) {
            if ($mapping = $serializer->getTransformer()->getMappingByClassName($className)) {
                $aliased = (array) $mapping->getAliasedProperties();
                $sortsFields = str_replace(array_values($aliased), array_keys($aliased), $sorting->fields());
                $invalidProperties = array_diff($sortsFields, $mapping->getProperties());
                foreach ($invalidProperties as $extraField) {
                    $errorBag->offsetSet(null, new InvalidSortError($extraField));
                }
            }
        }
    }
}