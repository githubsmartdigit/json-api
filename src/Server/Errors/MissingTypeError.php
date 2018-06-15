<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 11/28/15
 * Time: 1:29 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\JsonApi\Server\Errors;

/**
 * Class MissingTypeError.
 */
class MissingTypeError extends Error
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Bad Request', sprintf('Missing `type` Member at `data` level.'), 'bad_request');
        $this->setSource('pointer', '/data');
    }
}