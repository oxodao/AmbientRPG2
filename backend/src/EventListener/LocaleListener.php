<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\Translation\LocaleAwareInterface;

#[AsEventListener(event: LoginSuccessEvent::class)]
readonly class LocaleListener
{
    public function __construct(
        private LocaleAwareInterface $translator,
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        // If the user is authenticated
        // the language they selected in their profile is
        // applied INSTEAD OF THE ACCEPT-LANGUAGE
        if ($user instanceof User) {
            $locale = $user->getLanguage()->getLocale();
            $event->getRequest()->setLocale($locale);
            $this->translator->setLocale($locale);
        }
    }
}
