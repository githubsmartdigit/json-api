<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 7/29/15
 * Time: 12:44 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Http\Response;

class ResourceDeleted extends AbstractResponse
{
    /**
     * @var int
     */
    protected $httpCode = 204;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        $this->response = self::instance('', $this->httpCode, $this->headers);
    }
}