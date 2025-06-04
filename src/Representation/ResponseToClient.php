<?php

namespace App\Representation;

class ResponseToClient
{
    /**
     * @var array $header
     */
    private $header;

    /**
     * @var mixed $response
     */
    private $response;

    private $code;

    /**
     * ResponseToClient constructor.
     * @param mixed $response
     * @param integer $code
     * @param string $message
     */
    public function __construct($response = null, $code = 200, $message = 'Ok')
    {
        $this->response = $response;

        $this->code = $code;

        $this->header = [
            'code' => $code,
            'message' => $message
        ];
    }

    /**
     * @return array|null
     */
    public function getHeader(): ?array
    {
        return $this->header;
    }

    /**
     * @param array $header
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    /**
     * @return mixed|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @param array $codes
     * @return bool
     */
    public function checkIsSuccessResponse(array $codes): bool
    {
        return in_array($this->code, $codes);
    }
}