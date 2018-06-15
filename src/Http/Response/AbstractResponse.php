<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 7/28/15
 * Time: 1:20 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Http\Response;

/**
 * Class AbstractResponse.
 */
abstract class AbstractResponse extends \Xooxx\Api\Http\Message\AbstractResponse
{
    /**
     * @var array
     */
    protected $headers = ['Content-type' => 'application/vnd.api+json', 'Cache-Control' => 'protected, max-age=0, must-revalidate'];
}