<?php
namespace App\DamaServices;

class DamaRequest
{
    private $apiUrl;
    private $facilityCode;
    private $password;
    private $startDate;
    private $endDate;

    public function __construct($apiUrl, $facilityCode, $password, $startDate, $endDate)
    {
        $this->apiUrl = $apiUrl;
        $this->facilityCode = $facilityCode;
        $this->password = $password;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function fetchData()
    {
        $data = array(
            'facilityCode' => $this->facilityCode,
            'password' => $this->password,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        );

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}
