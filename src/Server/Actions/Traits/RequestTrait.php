<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/14/15
 * Time: 11:46 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Actions\Traits;

use Xooxx\JsonApi\Http\Request\Parameters\Fields;
use Xooxx\JsonApi\Http\Request\Parameters\Included;
use Xooxx\JsonApi\JsonApiSerializer;
use Xooxx\JsonApi\Server\Errors\InvalidParameterError;
use Xooxx\JsonApi\Server\Errors\InvalidParameterMemberError;
use Xooxx\Api\Mapping\Mapping;
/**
 * Class RequestTrait.
 */
trait RequestTrait
{
    /**
     * @var \Xooxx\JsonApi\Server\Errors\Error[]
     */
    protected $queryParamErrorBag = [];
    /**
     * @return \Xooxx\JsonApi\Server\Errors\Error[]
     */
    protected function getQueryParamsErrors()
    {
        return $this->queryParamErrorBag;
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param Fields $fields
     * @param Included $included
     *
     * @return bool
     * @throws \ReflectionException
     */
    protected function hasValidQueryParams(JsonApiSerializer $serializer, Fields $fields, Included $included)
    {
        $this->validateFieldsQueryParams($serializer, $fields, 'Fields');
        $this->validateIncludeQueryParamsTypes($serializer, $included, 'Include');
        return empty($this->queryParamErrorBag);
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param Fields $fields
     * @param string $paramName
     * @throws \ReflectionException
     */
    protected function validateFieldsQueryParams(JsonApiSerializer $serializer, Fields $fields, $paramName)
    {
        if (false === $fields->isEmpty()) {
            $validateFields = $fields->types();
            foreach ($validateFields as $key => $field) {
                $mapping = $serializer->getTransformer()->getMappingByAlias($field);
                if (null !== $mapping) {
                    $properties = $this->getPropertiesFromMapping($mapping);
                    $invalidProperties = array_diff($fields->members($field), $properties);
                    $this->addInvalidParameterMemberErrorsToErrorBag($invalidProperties, $paramName, $field);
                    unset($validateFields[$key]);
                }
            }
            $this->addInvalidParameterErrorsToErrorBag($paramName, $validateFields);
        }
    }
    /**
     * @param Mapping $mapping
     *
     * @return array
     */
    protected function getPropertiesFromMapping(Mapping $mapping)
    {
        $properties = array_merge(array_combine($mapping->getProperties(), $mapping->getProperties()), $mapping->getAliasedProperties());
        return $properties;
    }
    /**
     * @param array  $invalidProperties
     * @param string $paramName
     * @param string $field
     */
    protected function addInvalidParameterMemberErrorsToErrorBag(array $invalidProperties, $paramName, $field)
    {
        foreach ($invalidProperties as $extraField) {
            $this->queryParamErrorBag[] = new InvalidParameterMemberError($extraField, $field, strtolower($paramName));
        }
    }
    /**
     * @param string $paramName
     * @param array  $validateFields
     */
    protected function addInvalidParameterErrorsToErrorBag($paramName, array &$validateFields)
    {
        if (false === empty($validateFields)) {
            foreach ($validateFields as $field) {
                $this->queryParamErrorBag[] = new InvalidParameterError($field, strtolower($paramName));
            }
        }
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param Included $included
     * @param string $paramName
     * @throws \ReflectionException
     */
    protected function validateIncludeQueryParamsTypes(JsonApiSerializer $serializer, Included $included, $paramName)
    {
        if (false === $included->isEmpty()) {
            $validateFields = array_keys($included->get());
            foreach ($validateFields as $key => $field) {
                $mapping = $serializer->getTransformer()->getMappingByAlias($field);
                if (null !== $mapping) {
                    $properties = $this->getPropertiesFromMapping($mapping);
                    $invalidProperties = array_diff($included->get()[$field], $properties);
                    $this->addInvalidParameterMemberErrorsToErrorBag($invalidProperties, $paramName, $field);
                    unset($validateFields[$key]);
                }
            }
            $this->addInvalidParameterErrorsToErrorBag($paramName, $validateFields);
        }
    }
}