<?php

namespace MicroShard\JsonRpcServer;

class Response
{
    /**
     * @var string[]
     */
    protected $header = [];

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var int
     */
    protected $code = 200;

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addHeader(string $name, string $value): Response
    {
        $this->header[$name] = $value;
        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): Response
    {
        $this->addHeader("Content-Length", strlen($content));
        $this->content = $content;
        return $this;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code): Response
    {
        $this->code = $code;
        return $this;
    }

    public function send()
    {
        http_response_code($this->code);
        foreach ($this->header as $name => $value){
            header(sprintf("%s: %s", $name, $value));
        }
        echo $this->content;
    }
}