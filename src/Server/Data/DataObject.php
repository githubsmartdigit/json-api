<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/27/15
 * Time: 9:58 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Data;

use Xooxx\JsonApi\JsonApiSerializer;
use Xooxx\JsonApi\JsonApiTransformer;
use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Xooxx\JsonApi\Server\Errors\InvalidAttributeError;
use Xooxx\JsonApi\Server\Errors\InvalidTypeError;
use Xooxx\JsonApi\Server\Errors\MissingAttributeError;
use Xooxx\JsonApi\Server\Errors\MissingDataError;
use Xooxx\JsonApi\Server\Errors\MissingTypeError;
/**
 * Class DataObject.
 */
class DataObject
{
    /**
     * @param array $data
     * @param JsonApiSerializer $serializer
     * @param string $className
     * @param ErrorBag $errorBag
     * @throws \ReflectionException
     */
    public static function assertPatch($data, JsonApiSerializer $serializer, $className, ErrorBag $errorBag)
    {
        DataAssertions::assert($data, $serializer, $className, $errorBag);
    }

    /**
     * @param array $data
     * @param JsonApiSerializer $serializer
     * @param string $className
     * @param ErrorBag $errorBag
     *
     * @throws DataException
     * @throws \ReflectionException
     */
    public static function assertPost($data, JsonApiSerializer $serializer, $className, ErrorBag $errorBag)
    {
        DataAssertions::assert($data, $serializer, $className, $errorBag);
        self::assertRelationshipData($data, $serializer, $errorBag);

        $missing = self::missingCreationAttributes($data, $serializer);
        if (false === empty($missing)) {
            foreach ($missing as $attribute) {
                $errorBag->offsetSet(null, new MissingAttributeError($attribute));
            }
        }
        if ($errorBag->count() > 0) {
            throw new DataException('An error with the provided data occurred.', $errorBag);
        }
    }

    /**
     * @param array $data
     * @param JsonApiSerializer $serializer
     * @param string $className
     * @param ErrorBag $errorBag
     *
     * @throws DataException
     * @throws \ReflectionException
     */
    public static function assertPut($data, JsonApiSerializer $serializer, $className, ErrorBag $errorBag)
    {
        self::assertPost($data, $serializer, $className, $errorBag);
    }

    /**
     * @param array $data
     * @param JsonApiSerializer $serializer
     *
     * @return array
     * @throws \ReflectionException
     */
    protected static function missingCreationAttributes(array $data, JsonApiSerializer $serializer)
    {
        $inputAttributes = array_keys($data[JsonApiTransformer::ATTRIBUTES_KEY]);
        $mapping = $serializer->getTransformer()->getMappingByAlias($data[JsonApiTransformer::TYPE_KEY]);
        $diff = [];
        if (null !== $mapping) {
            $required = $mapping->getRequiredProperties();
            $properties = str_replace(array_keys($mapping->getAliasedProperties()), array_values($mapping->getAliasedProperties()), !empty($required) ? $required : $mapping->getProperties());
            $properties = array_diff($properties, $mapping->getIdProperties());
            $diff = (array) array_diff($properties, $inputAttributes);
        }
        return $diff;
    }

    /**
     * @param array $data
     * @param JsonApiSerializer $serializer
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function getAttributes(array $data, JsonApiSerializer $serializer)
    {
        $mapping = $serializer->getTransformer()->getMappingByAlias($data[JsonApiTransformer::TYPE_KEY]);
        $aliases = $mapping->getAliasedProperties();
        $keys = str_replace(array_values($aliases), array_keys($aliases), array_keys($data[JsonApiTransformer::ATTRIBUTES_KEY]));
        return array_combine($keys, array_values($data[JsonApiTransformer::ATTRIBUTES_KEY]));
    }

    /**
     * @param array $data
     * @param JsonApiSerializer $serializer
     * @param ErrorBag $errorBag
     *
     * @throws DataException
     * @throws \ReflectionException
     */
    protected static function assertRelationshipData(array $data, JsonApiSerializer $serializer, ErrorBag $errorBag)
    {
        if (!empty($data[JsonApiTransformer::RELATIONSHIPS_KEY])) {
            foreach ($data[JsonApiTransformer::RELATIONSHIPS_KEY] as $relationshipData) {
                if (empty($relationshipData[JsonApiTransformer::DATA_KEY]) || !is_array($relationshipData[JsonApiTransformer::DATA_KEY])) {
                    $errorBag->offsetSet(null, new MissingDataError());
                    break;
                }
                $firstKey = key($relationshipData[JsonApiTransformer::DATA_KEY]);
                if (is_numeric($firstKey)) {
                    foreach ($relationshipData[JsonApiTransformer::DATA_KEY] as $inArrayRelationshipData) {
                        self::relationshipDataAssert($inArrayRelationshipData, $serializer, $errorBag);
                    }
                    break;
                }
                self::relationshipDataAssert($relationshipData[JsonApiTransformer::DATA_KEY], $serializer, $errorBag);
            }
        }
    }

    /**
     * @param array $relationshipData
     * @param JsonApiSerializer $serializer
     * @param ErrorBag $errorBag
     * @throws \ReflectionException
     */
    protected static function relationshipDataAssert($relationshipData, JsonApiSerializer $serializer, ErrorBag $errorBag)
    {
        //Has type member.
        if (empty($relationshipData[JsonApiTransformer::TYPE_KEY]) || !is_string($relationshipData[JsonApiTransformer::TYPE_KEY])) {
            $errorBag->offsetSet(null, new MissingTypeError());
            return;
        }
        //Provided type value is supported.
        if (null === $serializer->getTransformer()->getMappingByAlias($relationshipData[JsonApiTransformer::TYPE_KEY])) {
            $errorBag->offsetSet(null, new InvalidTypeError($relationshipData[JsonApiTransformer::TYPE_KEY]));
            return;
        }
        //Validate if attributes passed in make sense.
        if (!empty($relationshipData[JsonApiTransformer::ATTRIBUTES_KEY])) {
            $mapping = $serializer->getTransformer()->getMappingByAlias($relationshipData[JsonApiTransformer::TYPE_KEY]);
            $properties = str_replace(array_keys($mapping->getAliasedProperties()), array_values($mapping->getAliasedProperties()), $mapping->getProperties());
            foreach (array_keys($relationshipData[JsonApiTransformer::ATTRIBUTES_KEY]) as $property) {
                if (false === in_array($property, $properties, true)) {
                    $errorBag->offsetSet(null, new InvalidAttributeError($property, $relationshipData[JsonApiTransformer::TYPE_KEY]));
                }
            }
        }
    }
}