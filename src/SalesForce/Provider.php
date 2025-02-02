<?php

namespace SocialiteProviders\SalesForce;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'SALESFORCE';

    public const PROVIDER_NAME = 'salesforce';

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getInstanceURL().'/services/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getInstanceURL().'/services/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->getInstanceURL().'/services/oauth2/userinfo',
            [
                RequestOptions::HEADERS => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => Arr::get($user, 'user_id'),
            'name'     => Arr::get($user, 'name'),
            'email'    => Arr::get($user, 'email'),
            'avatar'   => Arr::get($user, 'picture'),
            'nickname' => Arr::get($user, 'nickname'),
        ]);
    }

    /**
     * Get the instance URL from config
     * If not available default to production.
     *
     * @return string Salesforce base URL
     */
    private function getInstanceURL()
    {
        return $this->getConfig('instance_url', 'https://login.salesforce.com');
    }

    public static function additionalConfigKeys(): array
    {
        return ['instance_url'];
    }
}
