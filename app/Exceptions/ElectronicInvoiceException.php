<?php

namespace App\Exceptions;

use Exception;

class ElectronicInvoiceException extends Exception
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
