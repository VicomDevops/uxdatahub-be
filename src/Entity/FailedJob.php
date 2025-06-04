<?php

namespace App\Entity;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

class FailedJob
{
    private $envelope;
    public function __construct(Envelope $envelope){
        $this->envelope = $envelope;
    }

    public function getMessage():object{
        return $this->envelope->getMessage();
    }
    public function getId():string{
        $stamps= $this->envelope->all(TransportMessageIdStamp::class);
        return end($stamps)->getId();
    }

    public function getTitle(){
        return $this->envelope->getMessage();
    }
    /** @var RedeliveryStamp[] $stamps */
    public function getTrace(): string{
        $stamps = $this->envelope->all(RedeliveryStamp::class);
        return $stamps[0]->getExceptionMessage();
    }
}