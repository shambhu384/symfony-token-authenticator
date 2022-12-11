<?php

namespace App\Security;

use App\Security\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    private $wsseClient;

    public function __construct(HttpClientInterface $wsseClient) {
        $this->wsseClient = $wsseClient;
    }
        

    public function loadUserByIdentifier($identifier): UserInterface
    {
        return (new User())
            ->setEmail($identifier)
        ;
    }

    /**
     * @param User $user
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $authResponse = $this->wsseClient->request('GET', '/api/users/me', [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $user->getAccessToken())
            ]
        ]);
        try {
            $authenticatedUser = $authResponse->toArray();
        } catch (ExceptionInterface $exception) {
            throw new UnsupportedUserException('Unable to refresh user');
        }
        // dump($user);
        // dump($authenticatedUser);
        $user->setAccessToken($user->getAccessToken());

        return $user;
        
        /*(new User)
            ->setAccessToken($user->getAccessToken())
            ->setEmail($user->getEmail())
            ->setRoles($authenticatedUser['roles'])
        ;*/
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
