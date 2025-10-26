<?php

namespace App\Message\Handler;

use App\Message\EmailUpdatedNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @extends AbstractUserMessageHandler<EmailUpdatedNotification>
 */
#[AsMessageHandler]
class EmailUpdatedMessageHandler extends AbstractUserMessageHandler
{
    public function getTemplate($message): string
    {
        return 'email_updated';
    }

    public function getContext($message): array
    {
        return ['new_email' => $message->getEmail()];
    }

    // Because FUCK PHP GIVE US REAL FUCKING GENERICS
    public function __invoke(EmailUpdatedNotification $message): void
    {
        // We send the email to the new address
        $this->do($message);

        // Then one to the old
        $this->mailer->send($this->buildMessage($message)->to($message->getOldEmail()));
    }
}
