<?php

namespace App\Message\Handler;

use App\Message\ForgottenPasswordNotification;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @extends AbstractUserMessageHandler<ForgottenPasswordNotification>
 */
#[AsMessageHandler]
class ForgottenPasswordMessageHandler extends AbstractUserMessageHandler
{
    public function getTemplate($message): string
    {
        return 'forgotten_password';
    }

    public function getContext($message): array
    {
        return [
            'link' => \sprintf(
                '%s/reset-password/%s',
                $this->baseUrl,
                $message->getCode(),
            ),
        ];
    }

    // Because FUCK PHP GIVE US REAL FUCKING GENERICS
    public function __invoke(ForgottenPasswordNotification $message): void
    {
        $this->do($message);
    }
}
