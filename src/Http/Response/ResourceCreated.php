<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 7/29/15
 * Time: 12:48 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Http\Response;

class ResourceCreated extends AbstractResponse
{
    protected $httpCode = 201;
    /**
     * @param string $json
     */
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($json)
    {
        $data = \json_decode($json, true);
        if (null !== $data && !empty($data['data']['links']['self'])) {
            $data = $data['data']['links']['self'];
            $this->headers['Location'] = !empty($data['href']) ? $data['href'] : $data;
        }
        $this->response = self::instance($json, $this->httpCode, $this->headers);
    }
}