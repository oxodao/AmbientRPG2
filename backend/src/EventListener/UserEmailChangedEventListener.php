<?php

namespace App\EventListener;

use App\Entity\User;
use App\Message\EmailUpdatedNotification;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::preUpdate, method: 'onPreUpdate', entity: User::class)]
readonly class UserEmailChangedEventListener
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    public function onPreUpdate(User $user, PreUpdateEventArgs $args): void
    {
        if (!$args->hasChangedField('email')) {
            return;
        }

        $notification = new EmailUpdatedNotification($user, $args->getOldValue('email'));
        $this->bus->dispatch($notification);
    }
}
