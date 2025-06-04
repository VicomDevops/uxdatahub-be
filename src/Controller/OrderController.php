<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Licence;
use App\Repository\ClientRepository;
use App\Repository\LicenceTypeRepository;
use App\Service\LicenceService;
use App\Service\PaiementService;
use App\Service\StripeClient;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\InvoiceItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/paiement")
 */
class OrderController extends AbstractController
{

    private $paiementService;
    public function __construct(PaiementService $paiementService){
        $this->paiementService = $paiementService;
    }

     /**
     * @Route("/create_stripe_account", name="stripe_account", methods={"GET"})
     */
    public function stripeAccount()
    {
        $client= $this->getUser();
        if($client instanceof Client){
            try{
                return new Response($this->paiementService->StripeClientAccount($client)) ;
                
            }catch(\Exception $e){
                return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }else{
            return $this->json(["message" => "l/'utilisateur actuel n'est pas un client"], Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @Route("/intent", name="stripe_paiement", methods={"GET"})
     * @IsGranted("ROLE_CLIENT")
     */
    public function stripePaiement()
    {
        $client= $this->getUser();
        try{
            if($client->getStripeId()){
                $customer_id = $client->getStripeId();
            }else{
                $customer_id = $this->paiementService->StripeClientAccount($client) ;
            }
            $result = $this->paiementService->PaymentIntent($client);
//            $invoiceItem = InvoiceItem::create([
//                'customer' => $customer_id,
//                'price' => 'price_1LPlhAIbcxZf2WQsGYGWyUnk',
//            ]);
//            // Create an Invoice
//            $invoice = \Stripe\Invoice::create([
//                'customer' => $customer_id,
//                'collection_method' => 'send_invoice',
//                'days_until_due' => 30,
//            ]);
//
//            // Send the Invoice
//            $invoice->sendInvoice();
            return new Response($result,Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }


    }
     /**
     * @Route("/link", name="stripe_link", methods={"GET"})
     * @IsGranted("ROLE_CLIENT")
     */
    public function stripelink()
    {       
        $client=$this->getUser();
            dd($this->paiementService->paymentLink($client->getStripeId()));
    }

}
