<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Customer;
use Stripe\Stripe;

class PaiementService {
    
    private $entityManager;
    private $pk;
    private $stripe;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function PaymentIntent(Client $customer){

    }
  
     
    /**
     * StripeClientAccount
     *
     * @param  Client $client
     * @return string
     */
    public function StripeClientAccount(Client $client):?string
    {

        if($client instanceof Client){
         
                if($client->getStripeId()){
                    return $client->getStripeId();

                }else{
                   
                    $customer = Customer::create([
                        'email' => $client->getEmail()
                    ]);
                    $client->setStripeId($customer->id);
                    $this->entityManager->flush();
                    return $customer->id;
                }
            }

        return $client->getStripeId();
    }

    public function paymentLink(string $stripeId){
        $stripeClient=new \Stripe\StripeClient($this->pk);
        $price = $stripeClient->prices->create(
            [
              'currency' => 'eur',
              'unit_amount' => 50,
              'product' => 'prod_M81YuzvAqbLpRn',
            ]
          );

        $paymentLinks=$stripeClient->paymentLinks->create(
            ['line_items' => [['price' => 'price_1LXir6IbcxZf2WQsRjbbI68H', 'quantity' => 1]]]
          );
            $link=$stripeClient->paymentLinks->retrieve(
                'plink_1LXn5bIbcxZf2WQsrP97vSP5',
                 []
            );
            dd($link);
            // dd($link ->getCustomer())
    }

    private function nombreDeJoursaFacturer(Client $client){
        //calculer les jour % le 5 du mois actuel
        $today= getDate()['mday'];
        $diff=$today-5;
        if($diff<0){
            return false;
        }

    }
    
    public function sepaPaiement(Client $client)
    {
        try{
           
            $this->stripeClientAccount($client);
            $this->PaymentIntent($client);
         
            return 1;
        }catch(\Exception $e){
            return $e->getMessage();
            // return 0;
        }

    }
}