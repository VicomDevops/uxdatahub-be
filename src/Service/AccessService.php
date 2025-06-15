<?php

namespace App\Service;

use App\Entity\Client;

class AccessService {

    public function __construct()
    {
        
    }
    public function availableLicence(Client $client){
        if (is_null($client->getLicence())){
            return false;
        }else return true;
    }

    public function ValidLicencePeriod(Client $client){
        $date = new \DateTime('now');
        if($client->getContracts()->getEndAt()- $date->getTimestamp() >0){
            return true;
        }else return false;
    }

    public function CreateScenario(Client $client){
        if(! $this->availableLicence($client)){
            return false;
        }else return $this->ValidLicencePeriod($client);
    }

    public function ShowStatistics(Client $client){

    }

}