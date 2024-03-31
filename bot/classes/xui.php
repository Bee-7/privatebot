<?php

include_once 'request.php';

class Xui
{
    private $protocol;
    private $domain;
    private $port;
    private $full_address;
    private $session;
    private $header;

    public function __construct($protocol, $domain, $port, $session)
    {
        $this->full_address = $protocol . $domain . ':' . $port;
        $this->protocol = $protocol;
        $this->session = $session;
        $this->domain = $domain;
        $this->port = $port;
        $this->header = [
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: en-US,en;q=0.9,fa;q=0.8,zh-CN;q=0.7,zh;q=0.6',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Cookie: lang=en-US; session=' . $session,
            'Host: ' . $domain . ':' . $port,
            'Origin: ' . $protocol . $domain . ':' . $port,
            'Referer: ' . $protocol . $domain . ':' . $port . '/panel/inbounds',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'X-Requested-With: XMLHttpRequest'
        ];
    }

    private function genUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    private function genSub()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz1234567890'), 0, 16);
    }

    public function getServerStatus()
    {
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/server/status')->Method('POST')->Headers($this->header)->Send();
        $response = json_decode($response, true)['obj'];
        return $response;
    }

    public function getSettings()
    {
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/setting/all')->Method('POST')->Headers($this->header)->Send();
        $response = json_decode($response, true)['obj'];
        return $response;
    }

    public function getUserSecret()
    {
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/setting/getUserSecret')->Method('POST')->Headers($this->header)->Send();
        $response = json_decode($response, true)['obj'];
        return $response;
    }

    public function getOnlines()
    {
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/onlines')->Method('POST')->Headers($this->header)->Send();
        $response = json_decode($response, true)['obj'];
        return $response;
    }

    public function getCliStatusV1($email)
    {
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/list')->Method('POST')->Headers($this->header)->Send();
        $response = json_decode($response, true);
        foreach ($response['obj'] as $inbound) {
            $clients = $inbound['clientStats'];
            foreach ($clients as $client) {
                if ($client['email'] == $email) {
                    return $client;
                }
            }
        }
        return ['success' => false, 'message' => 'email not found.'];
    }

    public function getCliStatusV2($email)
    {
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/list')->Method('POST')->Headers($this->header)->Send();
        $response = json_decode($response, true);
        foreach ($response['obj'] as $inbound) {
            $clients = json_decode($inbound['settings'], true)['clients'];
            foreach ($clients as $client) {
                if ($client['email'] == $email) {
                    return $client;
                }
            }
        }
        return ['success' => false, 'message' => 'email not found.'];
    }

    public function getCliConfig($email)
    {
        $http = new HTTPRequest();
        $response = $http->Url('http://' . $this->domain . ':' . self::getSettings()['subPort'] . self::getSettings()['subPath'] . self::getCliStatusV2($email)['subId'])->Method('GET')->Send();
        $config = explode('#', base64_decode($response))[0] . '#' . $email;
        return $config;
    }

    public function renewConfig($id, $email, $ip_limit, $volume, $expire)
    {
        $getCli = self::getCliStatusV2($email);
        $pyload = 'id=' . $id . '&settings=%7B%22clients%22%3A%20%5B%7B%0A%20%20%22id%22%3A%20%22' . $getCli['id'] . '%22%2C%0A%20%20%22flow%22%3A%20%22%22%2C%0A%20%20%22email%22%3A%20%22' . $email . '%22%2C%0A%20%20%22limitIp%22%3A%20' . $ip_limit . '%2C%0A%20%20%22totalGB%22%3A%20' . ($volume * pow(1024, 3)) . '%2C%0A%20%20%22expiryTime%22%3A%20' . (strtotime('+ ' . $expire . ' day') * 1000) . '%2C%0A%20%20%22enable%22%3A%20true%2C%0A%20%20%22tgId%22%3A%20%22%22%2C%0A%20%20%22subId%22%3A%20%22' . $getCli['subId'] . '%22%2C%0A%20%20%22reset%22%3A%200%0A%7D%5D%7D';
        $http = new HTTPRequest();
        $reset = $http->Url($this->full_address . '/panel/inbound/' . $id . '/resetClientTraffic/' . $email)->Method('POST')->Headers($this->header)->Send();
        $response = $http->Url($this->full_address . '/panel/inbound/updateClient/' . $getCli['id'])->Method('POST')->Headers($this->header)->Body($pyload)->Send();
        return $response;
    }

    public function addIpLimit($id, $email, $ip_limit)
    {
        $getCli = self::getCliStatusV2($email);
        $pyload = 'id=' . $id . '&settings=%7B%22clients%22%3A%20%5B%7B%0A%20%20%22id%22%3A%20%22' . $getCli['id'] . '%22%2C%0A%20%20%22flow%22%3A%20%22%22%2C%0A%20%20%22email%22%3A%20%22' . $email . '%22%2C%0A%20%20%22limitIp%22%3A%20' . (intval($getCli['limitIp']) + $ip_limit) . '%2C%0A%20%20%22totalGB%22%3A%20' . $getCli['totalGB'] . '%2C%0A%20%20%22expiryTime%22%3A%20' . $getCli['expiryTime'] . '%2C%0A%20%20%22enable%22%3A%20true%2C%0A%20%20%22tgId%22%3A%20%22%22%2C%0A%20%20%22subId%22%3A%20%22' . $getCli['subId'] . '%22%2C%0A%20%20%22reset%22%3A%200%0A%7D%5D%7D';
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/updateClient/' . $getCli['id'])->Method('POST')->Headers($this->header)->Body($pyload)->Send();
        return $response;
    }

    public function deleteConfig($id, $email)
    {
        $getCli = self::getCliStatusV2($email);
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/' . $id . '/delClient/' . $getCli['id'])->Method('POST')->Headers($this->header)->Send();
        return $response;
    }

    public function changeConfigLink($id, $email)
    {
        $getCli = self::getCliStatusV2($email);
        $pyload = 'id=' . $id . '&settings=%7B%22clients%22%3A%20%5B%7B%0A%20%20%22id%22%3A%20%22' . self::genUUID() . '%22%2C%0A%20%20%22flow%22%3A%20%22%22%2C%0A%20%20%22email%22%3A%20%22' . $email . '%22%2C%0A%20%20%22limitIp%22%3A%20' . $getCli['limitIp'] . '%2C%0A%20%20%22totalGB%22%3A%20' . $getCli['totalGB'] . '%2C%0A%20%20%22expiryTime%22%3A%20' . $getCli['expiryTime'] . '%2C%0A%20%20%22enable%22%3A%20true%2C%0A%20%20%22tgId%22%3A%20%22%22%2C%0A%20%20%22subId%22%3A%20%22' . self::genSub() . '%22%2C%0A%20%20%22reset%22%3A%200%0A%7D%5D%7D';
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/updateClient/' . $getCli['id'])->Method('POST')->Headers($this->header)->Body($pyload)->Send();
        return $response;
    }

    public function changeConfigStatus($id, $email, $status)
    {
        $getCli = self::getCliStatusV2($email);
        $pyload = 'id=' . $id . '&settings=%7B%22clients%22%3A%20%5B%7B%0A%20%20%22id%22%3A%20%22' . $getCli['id'] . '%22%2C%0A%20%20%22flow%22%3A%20%22%22%2C%0A%20%20%22email%22%3A%20%22' . $email . '%22%2C%0A%20%20%22limitIp%22%3A%20' . $getCli['limitIp'] . '%2C%0A%20%20%22totalGB%22%3A%20' . $getCli['totalGB'] . '%2C%0A%20%20%22expiryTime%22%3A%20' . $getCli['expiryTime'] . '%2C%0A%20%20%22enable%22%3A%20' . $status . '%2C%0A%20%20%22tgId%22%3A%20%22%22%2C%0A%20%20%22subId%22%3A%20%22' . self::genSub() . '%22%2C%0A%20%20%22reset%22%3A%200%0A%7D%5D%7D';
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/updateClient/' . $getCli['id'])->Method('POST')->Headers($this->header)->Body($pyload)->Send();
        return $response;
    }

    public function addCli($id, $email, $ip_limit, $total_flow, $expire, $start_after_use)
    {
        $res_expire = ($start_after_use == 'yes') ? ($expire * 24 * 60 * 60 * 1000 * -1) : (strtotime('+ ' . $expire . ' day') * 1000);
        $pyload = 'id=' . $id . '&settings=%7B%22clients%22%3A%20%5B%7B%0A%20%20%22id%22%3A%20%22' . self::genUuid() . '%22%2C%0A%20%20%22flow%22%3A%20%22%22%2C%0A%20%20%22email%22%3A%20%22' . $email . '%22%2C%0A%20%20%22limitIp%22%3A%20' . $ip_limit . '%2C%0A%20%20%22totalGB%22%3A%20' . $total_flow * pow(1024, 3) . '%2C%0A%20%20%22expiryTime%22%3A%20' . $res_expire . '%2C%0A%20%20%22enable%22%3A%20true%2C%0A%20%20%22tgId%22%3A%20%22%22%2C%0A%20%20%22subId%22%3A%20%22' . self::genSub() . '%22%2C%0A%20%20%22reset%22%3A%200%0A%7D%5D%7D';
        $http = new HTTPRequest();
        $response = $http->Url($this->full_address . '/panel/inbound/addClient')->Method('POST')->Headers($this->header)->Body($pyload)->Send();
        return $response;
    }
}