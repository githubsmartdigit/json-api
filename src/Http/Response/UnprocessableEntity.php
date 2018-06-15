<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/21/15
 * Time: 1:15 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Http\Response;

class UnprocessableEntity extends AbstractErrorResponse
{
    /**
     * @var int
     */
    protected $httpCode = 422;
    /**
     * @var string
     */
    protected $errorCode = 'Unprocessable Entity';
}