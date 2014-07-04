<?php

namespace mailtank;

class MailtankException extends CException
{
    public $validationErrors = array();

    public function __construct($message = "", $code = 0, $validationErrors = array(), Exception $previous = null)
    {
        if (is_array($validationErrors) && $code == 400) {
            $this->validationErrors = $validationErrors;
        }
        parent::__construct($message, $code, $previous);
    }
}