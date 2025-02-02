<?php

namespace SocialiteProviders\LaravelPassport;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'LARAVELPASSPORT';

    protected $scopeSeparator = ' ';

    public static function additionalConfigKeys(): array
    {
        return [
            'host',
            'authorize_uri',
            'token_uri',
            'userinfo_uri',
            'userinfo_key',
            'user_id',
            'user_nickname',
            'user_name',
            'user_email',
            'user_avatar',
            'guzzle',
        ];
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getLaravelPassportUrl('authorize_uri'), $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getLaravelPassportUrl('token_uri');
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getLaravelPassportUrl('userinfo_uri'), [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\User
     */
    protected function mapUserToObject(array $user)
    {
        $key = $this->getConfig('userinfo_key');
        $data = ($key === null) === true ? $user : Arr::get($user, $key, []);

        return (new User)->setRaw($data)->map([
            'id'       => $this->getUserData($data, 'id'),
            'nickname' => $this->getUserData($data, 'nickname'),
            'name'     => $this->getUserData($data, 'name'),
            'email'    => $this->getUserData($data, 'email'),
            'avatar'   => $this->getUserData($data, 'avatar'),
        ]);
    }

    protected function getLaravelPassportUrl($type)
    {
        return rtrim($this->getConfig('host'), '/').'/'.ltrim($this->getConfig($type, Arr::get([
            'authorize_uri' => 'oauth/authorize',
            'token_uri'     => 'oauth/token',
            'userinfo_uri'  => $this->getConfig('userinfo_uri', 'api/user'),
        ], $type)), '/');
    }

    protected function getUserData($user, $key)
    {
        return Arr::get($user, $this->getConfig('user_'.$key, $key));
    }
}
