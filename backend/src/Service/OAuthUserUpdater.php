<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\Language;
use Oxodao\QneOAuthBundle\Behavior\OAuthUserInterface;
use Oxodao\QneOAuthBundle\Model\OAuthUserInfos;
use Oxodao\QneOAuthBundle\Service\OAuthUserUpdaterInterface;

readonly class OAuthUserUpdater implements OAuthUserUpdaterInterface
{
    public function __construct(
        private UniqueUsernameStrategy $uniqueUsernameStrategy,
    ) {
    }

    /**
     * Here we can update the user entity with the information received from the OAuth provider.
     * We NEED to setup everything required to persist the entity as it is also
     * used to create new users.
     */
    public function update(?OAuthUserInterface $user, OAuthUserInfos $infos): OAuthUserInterface
    {
        if (null === $user) {
            $user = new User();

            $uniqueUsername = $this->uniqueUsernameStrategy->generate($infos);
            if (!$uniqueUsername) {
                throw new \LogicException('Failed to generate a unique username');
            }

            // The username can only be set once, at creation time
            $user->setUsername($uniqueUsername);
        }

        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of App\Entity\User');
        }

        if ($infos->email) {
            $user->setEmail($infos->email);
        }

        if ($infos->locale) {
            $user->setLanguage(Language::fromAlpha2($infos->locale) ?? Language::AMERICAN_ENGLISH);
        }

        return $user;
    }
}
