<?php

namespace Xooxx\JsonApi\Server\Actions\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class ForbiddenException.
 */
class ForbiddenException extends HttpException
{
    private $title = 'Forbidden';
    /**
     * @param string    $message
     * @param \Exception $previous
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct(403, $message, $previous);
    }
    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function getTitle()
    {
        return $this->title;
    }
}
