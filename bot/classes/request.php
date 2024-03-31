<?php

class HTTPRequest
{
    private $ch;
    private $options = [];

    public function __construct()
    {
        $this->ch = curl_init();
        $this->setDefaults();
    }

    public function Headers($headers)
    {
        $this->options[CURLOPT_HTTPHEADER] = $headers;
        return $this;
    }

    public function Option($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
        return $this;
    }

    public function Timeout($timeout)
    {
        $this->Option(CURLOPT_TIMEOUT, $timeout);
        return $this;
    }

    public function Url($url)
    {
        $this->Option(CURLOPT_URL, $url);
        return $this;
    }

    public function Method($method)
    {
        $this->Option(CURLOPT_CUSTOMREQUEST, strtoupper($method));
        return $this;
    }

    public function Body($body)
    {
        $this->Option(CURLOPT_POSTFIELDS, $body);
        return $this;
    }

    public function Send()
    {
        curl_setopt_array($this->ch, $this->options);
        $response = curl_exec($this->ch);
        if ($response === false) {
            throw new \Exception(curl_error($this->ch), curl_errno($this->ch));
        }
        return $response;
    }

    public function getHeaders()
    {
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_NOBODY, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$headers) {
            $trimmedHeader = trim($header);
            if (!empty($trimmedHeader)) {
                $headers[] = $trimmedHeader;
            }
            return strlen($header);
        });
        curl_exec($this->ch);
        return $headers ?? [];
    }

    public function getStatus()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    public function Encoding($encodings)
    {
        if (is_array($encodings)) {
            $encodings = implode(',', $encodings);
        }
        $this->Option(CURLOPT_ENCODING, $encodings);
        return $this;
    }

    public function MaxRedirects($maxRedirects)
    {
        $this->Option(CURLOPT_MAXREDIRS, $maxRedirects);
        return $this;
    }

    public function VerifyPeer($verify)
    {
        $this->Option(CURLOPT_SSL_VERIFYPEER, $verify);
        return $this;
    }

    public function Proxy($proxy)
    {
        $this->Option(CURLOPT_PROXY, $proxy);
        return $this;
    }

    private function setDefaults()
    {
        $defaults = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
        curl_setopt_array($this->ch, $defaults);
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }
}
