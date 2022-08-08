<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;

class ArrayNestedAccessorException extends BaseException
{
    const KEY_NOT_FOUND = 1;
}
