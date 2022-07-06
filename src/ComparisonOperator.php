<?php
declare(strict_types=1);

namespace IterTools;

/**
 * Represents a comparison operator that can be used in 'where' and where-like operations.
 */
enum ComparisonOperator: string
{
    // TODO: It's possible that more comparison operators can be used. Please check the Laravel source code.
    case Equals = '=';
    case LooseEquals = '==';
    case LooseNotEquals = '!=';
    case StrictEquals = '===';
    case StrictNotEquals = '!==';

    case GreaterThan = '>';
    case LessThan = '<';

    case GreaterThanOrEqualTo = '>=';
    case LessThanOrEqualTo = '<=';

    case In = 'in';
    case NotIn = 'not-in';
}