<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Tester;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Order;

class StripeClient
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createCustomer(Client $client, $paymentToken)
    {
        $customer = \Stripe\Customer::create([
            'email' => $client->getEmail(),
            'source' => $paymentToken
        ]);

        $client->setStripeId($customer->id);
        $this->entityManager->flush();
        return $customer;
    }

    public function updateCustomerCard(Client $client, $paymentToken)
    {
        $customer = \Stripe\Customer::retrieve($client->getStripeId());
        $customer->source = $paymentToken;
        $customer->save();
    }

    public function createInvoiceItem($amount, Client $client, $description)
    {
        return \Stripe\InvoiceItem::create(array(
            "amount" => $amount,
            "currency" => "eur",
            "customer" => $client->getStripeId(),
            "description" => $description
        ));
    }

    public function createInvoice(Client $client, $payImmediately = true)
    {
        $invoice = \Stripe\Invoice::create(array(
            "customer" => $client->getStripeId()
        ));
        if ($payImmediately) {
            $invoice->pay();
        }
        return $invoice;
    }

    public function chargeCustomer($amount, Client $client)
    {
        return \Stripe\Charge::create(array(
            "amount" => $amount,
            "currency" => "eur",
            "customer" => $client->getStripeId(),
        ));
    }

    public function payoutTester($amount, User $user)
    {
        return \Stripe\Payout::create([
            "amount" => $amount,
            "currency" => "eur",
            "customer" => $user->getStripeId(),
        ]);
    }

    
}