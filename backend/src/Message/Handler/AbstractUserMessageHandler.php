<?php

namespace App\Message\Handler;

use App\Message\AbstractUserNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @template T of AbstractUserNotification
 */
abstract class AbstractUserMessageHandler
{
    /** @var T */
    protected $notification;
    protected string $baseUrl;

    public function __construct(
        protected TranslatorInterface $translator,
        protected MailerInterface $mailer,
        #[Autowire(env: 'DEFAULT_URI')]
        string $baseUrl,
    ) {
        $this->baseUrl = \rtrim($baseUrl, '/');
    }

    /**
     * @param T $message
     */
    protected function buildMessage($message): TemplatedEmail
    {
        $ctx = [
            'username' => $message->getUsername(),
            'public_url' => $this->baseUrl,
            ...$this->getContext($message),
        ];

        return new TemplatedEmail()
            ->to($message->getEmail())
            ->subject(\sprintf('[AmbientRPG] %s', $this->trans('subject', $ctx)))
            ->htmlTemplate(\sprintf('emails/%s.html.twig', $this->getTemplate($message)))
            ->locale($message->getLanguage())
            ->context($ctx)
        ;
    }

    /**
     * @param T $message
     */
    abstract public function getTemplate($message): string;

    /**
     * @param T $message
     *
     * @return array<string, mixed>
     */
    abstract public function getContext($message): array;

    /**
     * @param T $message
     */
    protected function do($message): void
    {
        $this->notification = $message;
        $this->mailer->send($this->buildMessage($message));
    }

    /**
     * @param array<mixed> $parameters
     */
    protected function trans(string $id, array $parameters = [], ?string $domain = null): string
    {
        return $this->translator->trans(
            id: \sprintf('emails.%s.%s', $this->getTemplate($this->notification), $id),
            parameters: $parameters,
            domain: $domain,
            locale: $this->notification->getLanguage(),
        );
    }
}
