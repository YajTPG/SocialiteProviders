<?php

namespace SocialiteProviders\UFS;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'UFS';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getBaseUri().'/authorization', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getBaseUri().'/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getBaseUri().'/usuario', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => null,
            'nickname' => $user['login'],
            'name'     => $user['pessoa']['nome'],
            'email'    => $user['pessoa']['email'],
            'avatar'   => $user['arquivo'],
        ]);
    }

    public static function additionalConfigKeys(): array
    {
        return ['dev_mode'];
    }

    /**
     * Get the base URI based on the environment mode.
     *
     * @return string
     */
    private function getBaseUri()
    {
        return $this->getConfig('dev_mode', false) ?
            'https://apisistemas.desenvolvimento.ufs.br/api/rest' :
            'https://www.sistemas.ufs.br/api/rest';
    }
}
