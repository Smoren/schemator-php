<?php


namespace Smoren\Schemator\Exceptions;


use Smoren\ExtendedExceptions\BaseException;

class SchematorException extends BaseException
{
    const FILTER_NOT_FOUND = 1;
    const FILTER_ERROR = 2;
}
