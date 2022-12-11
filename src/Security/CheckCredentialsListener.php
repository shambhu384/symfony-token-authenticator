<?php

namespace App\Security;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class CheckCredentialsListener implements EventSubscriberInterface
{
    private $wsseClient;

    public function __construct(HttpClientInterface $wsseClient)
    {
        $this->wsseClient = $wsseClient;
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if ($passport->hasBadge(PasswordCredentials::class)) {
            /** @var User */
            $user = $passport->getUser();
            /** @var PasswordCredentials $badge */
            $badge = $passport->getBadge(PasswordCredentials::class);
            if ($badge->isResolved()) {
                return;
            }

            $authResponse = $this->wsseClient->request('GET', '/api/login_check', [
                'json' => [
                    'username' => $user->getEmail(),
                    'password' => $badge->getPassword()
                ]
            ]);
            try {
                $decodedPayload = $authResponse->toArray();
                $user->setRoles(['ROLE_USER']);
                $user->setAccessToken($decodedPayload['token']);
                $badge->markResolved();

            } catch (ExceptionInterface $exception) {
                dump($exception);
                throw new BadCredentialsException('The presented password is invalid.');
            }
            return;
        }
    }
    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => ['checkPassport', 10]];
    }
}