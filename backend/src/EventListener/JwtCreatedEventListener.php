<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: Events::JWT_CREATED, method: 'onJwtCreated')]
class JwtCreatedEventListener
{
    public function onJwtCreated(JWTCreatedEvent $e): void
    {
        $data = $e->getData();
        $user = $e->getUser();

        if (!$user instanceof User) {
            return;
        }

        $data['id'] = $user->getId();
        $data['language'] = $user->getLanguage();

        $data['mercure'] = [
            'publish' => [],
            'subscribe' => [
                \sprintf('/users/%s{?topic}', $user->getId()),
            ],
            'payload' => [],
        ];

        $e->setData($data);
    }
}
