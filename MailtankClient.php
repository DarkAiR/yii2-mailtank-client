<?php

namespace mailtank

class MailtankClient extends \CApplicationComponent
{
    public $host;
    public $token;
    /**
     * CURL option
     * @var int
     */
    public $timeout = 0;

    protected $headers = array();


    public function init()
    {
        $this->headers = array(
            'X-Auth-Token' => $this->token,
            'Content-Type' => 'application/json',
        );
    }

    public function sendRequest($endPoint, $fields = array(), $method = 'get', $options = array())
    {
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once 'requests/library/Requests.php';
        Yii::registerAutoloader(array('Requests', 'autoloader'));

        $options = array_merge_recursive(array('timeout' => $this->timeout), $options);
        switch ($method) {
            case 'get':
                $response = Requests::get(
                    'http://' . $this->host . $endPoint . (!empty($fields) ? '?' . http_build_query($fields) : ''),
                    $this->headers, $options
                );
                $returnedData = json_decode($response->body, true);
                break;
            case 'delete':
                $response = Requests::delete('http://' . $this->host . $endPoint, $this->headers, $options);
                $returnedData = $response->body;
                break;
            case 'patch':
                $response = Requests::patch('http://' . $this->host . $endPoint, $this->headers, $fields, $options);
                $returnedData = $response->body;
                break;
            default:
                $response = Requests::$method('http://' . $this->host . $endPoint, $this->headers, $fields, $options);
                $returnedData = json_decode($response->body, true);
                break;
        }

        spl_autoload_register(array('YiiBase', 'autoload'));

        if (!$response->success) {
            $message = @json_decode($response->body, true);
            throw new MailtankException("Request failed at url: $method {$response->url}. " . print_r($message, true), $response->status_code, $message);
        }

        if (is_null($returnedData)) {
            throw new MailtankException('answer from mailtank can\'t be decoded: ' . $response->body);
        }

        unset($response);
        return $returnedData;
    }
}
