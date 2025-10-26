<?php

namespace App\Model;

use App\ApiConfig\ForgottenPasswordRequestApiConfig;
use App\ApiConfig\UserApiConfig;
use App\Validator\CorrectPassword;
use Symfony\Component\Validator\Constraints as Assert;

class SetPassword
{
    #[Assert\NotBlank(message: 'not_blank', groups: [UserApiConfig::VALIDATE_PASSWORD_UPDATE])]
    #[CorrectPassword(groups: [UserApiConfig::VALIDATE_PASSWORD_UPDATE])]
    public ?string $oldPassword = null;

    #[Assert\NotBlank(message: 'not_blank', groups: [UserApiConfig::VALIDATE_PASSWORD_UPDATE, ForgottenPasswordRequestApiConfig::VALIDATE_FORGOTTEN])]
    #[Assert\NotCompromisedPassword(message: 'not_compromised', groups: [UserApiConfig::VALIDATE_PASSWORD_UPDATE, ForgottenPasswordRequestApiConfig::VALIDATE_FORGOTTEN])]
    #[Assert\Length(min: 8)]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/',
        message: 'password_requirements',
        groups: [UserApiConfig::VALIDATE_PASSWORD_UPDATE, ForgottenPasswordRequestApiConfig::VALIDATE_FORGOTTEN],
    )]
    public string $newPassword;
}
