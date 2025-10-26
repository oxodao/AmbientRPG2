<?php

namespace App\Message;

use App\Entity\User;

class EmailUpdatedNotification extends AbstractUserNotification
{
    public function __construct(
        User $user,
        private string $oldEmail,
    ) {
        parent::__construct($user);
    }

    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }
}
