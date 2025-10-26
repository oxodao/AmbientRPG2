<?php

namespace App\Service;

use App\Repository\UserRepository;
use Oxodao\QneOAuthBundle\Model\OAuthUserInfos;

readonly class UniqueUsernameStrategy
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function generate(OAuthUserInfos $infos): ?string
    {
        $baseUsername = $infos->username ?? '';
        if (\strlen($baseUsername) > 0) {
            $user = $this->userRepository->findOneByUsername($baseUsername);
            if (null === $user) {
                return $baseUsername;
            }
        }

        if (\strlen($baseUsername) > 0) {
            $tries = 0;

            while ($tries < 20) {
                $randomSuffix = \random_int(1, 99999);

                $username = \sprintf('%s_%05d', $baseUsername, $randomSuffix);
                $user = $this->userRepository->findOneByUsername($username);
                if (null === $user) {
                    return $username;
                }

                ++$tries;
            }
        }

        return null;
    }
}
