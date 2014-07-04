<?php

namespace mailtank;

class MailtankException extends \Exception
{
    public $validationErrors = [];

    public function __construct($message = "", $code = 0, $validationErrors = [], \Exception $previous = null)
    {
        if (is_array($validationErrors) && $code == 400) {
            $this->validationErrors = $validationErrors;
        }
        parent::__construct($message, $code, $previous);
    }
}