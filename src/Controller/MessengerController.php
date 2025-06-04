<?php

namespace App\Controller;

use App\Repository\FailedJobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class MessengerController extends AbstractController
{
    /**
     * @Route("/messenger", name="messenger")
     */
    public function messenger(FailedJobRepository $failedJobRepository)
    {
        return $this->render('index.html.twig',[
            'jobs'=>$failedJobRepository->findAll()
        ]);
    }
    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(int $id, FailedJobRepository $failedJobRepository){

        $failedJobRepository->reject($id);
        $this->addFlash('success',' la tache a bien ete supprimée');
        return $this->redirectToRoute('messenger');
    }
    /**
     * @Route("/retry/{id}", name="retry", methods={"POST"})
     */
    public function retry(int $id, FailedJobRepository $failedJobRepository, MessageBusInterface $messageBus){
        $message= $failedJobRepository->find($id)->getMessage();
        $messageBus->dispatch($message);
        $failedJobRepository->reject($id);
        $this->addFlash('success',' la tache a bien ete reesayé');
        return $this->redirectToRoute('messenger');
    }

}