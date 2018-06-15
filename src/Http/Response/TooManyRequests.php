<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/21/15
 * Time: 1:13 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Http\Response;

class TooManyRequests extends AbstractErrorResponse
{
    /**
     * @var int
     */
    protected $httpCode = 429;
    /**
     * @var string
     */
    protected $errorCode = 'Too Many Requests';
}