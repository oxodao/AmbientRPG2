<?php

namespace App\Message\Handler;

use App\Message\PasswordUpdatedNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @extends AbstractUserMessageHandler<PasswordUpdatedNotification>
 */
#[AsMessageHandler]
class PasswordUpdatedMessageHandler extends AbstractUserMessageHandler
{
    public function getTemplate($message): string
    {
        return 'password_updated';
    }

    public function getContext($message): array
    {
        return [];
    }

    // Because FUCK PHP GIVE US REAL FUCKING GENERICS
    public function __invoke(PasswordUpdatedNotification $message): void
    {
        $this->do($message);
    }
}
