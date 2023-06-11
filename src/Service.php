<?php

namespace CaptchaEU;

class Service
{
    public string $endPoint;
    public string $restKey;

    public function __construct($endPoint, $restKey)
    {
        $this->endPoint = $endPoint;
        $this->restKey = $restKey;
    }
    public function validate($solution)
    {
        $url = $this->endPoint;
        $restKey = $this->restKey;
        $url = $url . '/validate';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $solution);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Rest-Key: ' . $restKey));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $resultObject = json_decode($result);
        if ($resultObject->success) {
            return true;
        }
        return false;
    }
}
