<?php

namespace App\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Mime\Email;

class FailedMessageSubscriber {

    private $mailer;
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer= $mailer;
        
    }
    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed'
        ];
    }
    public function onMessageFailed(WorkerMessageFailedEvent $event){
        $message= get_class($event->getEnvelope()->getMessage()->getError());
        $trace=serialize($event->getEnvelope());
        $email=(new Email())->from('noreply@server.fr')
        ->to('kjdidi@2m-advisory.fr')
        ->subject('Erreur Analyze local')
        ->text( $message .'<br>'. $trace  );

        $this->mailer->send($email);
    }

}