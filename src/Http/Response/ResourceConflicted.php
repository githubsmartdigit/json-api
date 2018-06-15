<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 7/29/15
 * Time: 12:52 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Http\Response;

class ResourceConflicted extends AbstractErrorResponse
{
    /**
     * @var int
     */
    protected $httpCode = 409;
    /**
     * @var string
     */
    protected $errorCode = 'Conflict';
}