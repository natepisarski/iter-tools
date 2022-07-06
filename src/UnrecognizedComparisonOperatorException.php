<?php
declare(strict_types=1);

namespace IterTools;

use Exception;
use Throwable;

class UnrecognizedComparisonOperatorException extends Exception
{
  public function __construct(string $message = "Unrecognized comparison operator detected", int $code = 0, ?Throwable $previous = null)
  {
    // TODO: Exception message should contain the comparison operator
    parent::__construct($message, $code, $previous);
  }
}