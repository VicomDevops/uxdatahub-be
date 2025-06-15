<?php

namespace App\Repository;

use App\Entity\FailedJob;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

class FailedJobRepository
{
    private $receiver;
    public function __construct(ListableReceiverInterface $receiver){
        $this->receiver = $receiver;
    }
    public function find(string $id): FailedJob{
        return new FailedJob($this->receiver->find($id));

    }
    /**
     * @return FailedJob[] array
     */
    public function findAll(){
//        return $this->receiver->all();
        return array_map(function(Envelope $envelope){
             return new FailedJob($envelope);},
            iterator_to_array($this->receiver->all()));

    }
    public function reject( string $id){
        $this->receiver->reject($this->receiver->find($id));
    }

}