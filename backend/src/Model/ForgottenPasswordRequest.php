<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ForgottenPasswordRequest
{
    #[Assert\NotBlank(message: 'not_blank')]
    #[Assert\Email(message: 'invalid_email')]
    public string $email;
}
