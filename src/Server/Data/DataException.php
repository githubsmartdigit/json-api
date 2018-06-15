<?php

/**
* Author: Xooxx <xooxx.dev@gmail.com>
* Date: 11/27/15
* Time: 10:00 PM.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Xooxx\JsonApi\Server\Data;

use Xooxx\JsonApi\Server\Errors\ErrorBag;
use Exception;

/**
 * Class DataException.
 */
class DataException extends \InvalidArgumentException
{
    /**
     * MessageBag errors.
     *
     * @var \Xooxx\JsonApi\Server\Errors\ErrorBag
     */
    protected $errors;

    /**
     * Create a new data exception instance.
     *
     * @param string $message
     * @param \Xooxx\JsonApi\Server\Errors\ErrorBag|array $errors
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = null, $errors = null, $code = 0, Exception $previous = null)
    {
        if (is_null($errors)) {
            $this->errors = new ErrorBag();
        } else {
            $this->errors = is_array($errors) ? new ErrorBag($errors) : $errors;
        }
        parent::__construct($message, $code, $previous);
    }
    /**
     * Get the errors message bag.
     *
     * @return ErrorBag|array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    /**
     * Determine if message bag has any errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (bool) $this->errors->count();
    }
}