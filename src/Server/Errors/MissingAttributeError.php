<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/28/15
 * Time: 1:23 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Errors;

/**
 * Class MissingAttributeError.
 */
class MissingAttributeError extends Error
{
    /**
     * @param string $attribute
     */
    public function __construct($attribute)
    {
        parent::__construct('Missing Attribute', sprintf('Attribute `%s` is missing.', $attribute), 'unprocessable_entity');
        $this->setSource('pointer', sprintf('/data/attributes/%s', $attribute));
    }
}