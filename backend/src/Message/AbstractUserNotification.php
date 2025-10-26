<?php

namespace App\Message;

use App\Entity\User;

abstract class AbstractUserNotification
{
    protected string $username;
    protected string $email;
    protected string $language;

    public function __construct(User $user)
    {
        $this->username = $user->getUsername();
        $this->email = $user->getEmail();
        $this->language = $user->getLanguage()->value;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
