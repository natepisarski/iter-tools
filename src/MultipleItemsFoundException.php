<?php
declare(strict_types = 1);

namespace IterTools;

use Exception;
use Throwable;

class MultipleItemsFoundException extends Exception
{
  public function __construct(string $message = "Too many items were found matching your criteria.", int $code = 0, ?Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}