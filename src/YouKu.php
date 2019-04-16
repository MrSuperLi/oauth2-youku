<?php namespace MrSuperLi\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Token\AccessTokenInterface;

class YouKu extends AbstractProvider
{
	use BearerAuthorizationTrait;

	/**
	 * @var string
	 */
	public $domain = "https://openapi.youku.com";

	/**
	 * Get authorization url to begin OAuth flow
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl ()
	{
		return $this->domain . '/v2/oauth2/authorize';
	}

	/**
	 * Get access token url to retrieve token
     *
	 * @param array $params
	 * @return string
	 */
	public function getBaseAccessTokenUrl (array $params)
	{
		return $this->domain . '/router/rest.json';
	}

	public function getRefreshTokenUrl()
    {
        return $this->domain . '/router/rest.json';
    }

	/**
	 * Get provider url to fetch user details
	 * @param AccessToken $token
	 * @return string
	 */
	public function getResourceOwnerDetailsUrl (AccessToken $token)
	{
        $params = $this->buildQueryString([
            'client_id' => $this->clientId,
            'access_token' => $token->getToken()
        ]);

        return $this->domain . '/v2/users/myinfo.json?' . $params;
	}

    /**
     * get accesstoken
     *
     * The Content-type of server's returning is 'text/html;charset=utf-8'
     * so it has to be rewritten
     *
     * @param mixed $grant
     * @param array $options
     * @return AccessTokenInterface
     * @throws IdentityProviderException
     */
	public function getAccessToken ($grant, array $options = [])
	{
		$grant = $this->verifyGrant($grant);

		$params = [
            'action' => 'youku.user.authorize.token.get',
			'client_id'     => $this->clientId,
            'format' => 'json',
            'timestamp' => time(),
            'version' => '3.0',
		];

		$params   = $grant->prepareRequestParameters($params, $options);
        unset($params['grant_type']);
		$params['sign'] = $this->getSign($params);
		$code = $params['code'];
		unset($params['code']);

        $params = [
            'opensysparams' => json_encode($params),
            'code' => $code
        ];

		$request  = $this->getAccessTokenRequest($params);
		$response = $this->getParsedResponse($request);

		$prepared = $this->prepareAccessTokenResponse($response);
		$token    = $this->createAccessToken($prepared, $grant);

		return $token;
	}

    /**
     * @param $refreshToken
     * @return mixed
     * @throws IdentityProviderException
     */
	public function refreshToken($refreshToken)
    {

        if ($refreshToken instanceof AccessToken) {
            $refreshToken = $refreshToken->getRefreshToken();
        }

        $params = [
            'action' => 'youku.user.authorize.token.refresh',
            'client_id'     => $this->clientId,
            'format' => 'json',
            'timestamp' => time(),
            'version' => '3.0',
            'refreshToken' => $refreshToken
        ];

        $params['sign'] = $this->getSign($params);
        unset($params['refreshToken']);

        $params = [
            'opensysparams' => json_encode($params),
            'refreshToken' => $refreshToken
        ];

        $request  = $this->getRequest(
            'POST',
            $this->getRefreshTokenUrl(),
            [
                'headers' => ['content-type' => 'application/x-www-form-urlencoded'],
                'body' => $this->buildQueryString($params)
            ]
        );

        $response = $this->getParsedResponse($request);

        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $this->verifyGrant('authorization_code'));

        return $token;
    }

	/**
	 * Check a provider response for errors.
	 *
	 * @throws IdentityProviderException
	 * @param  ResponseInterface $response
	 * @param  string $data Parsed response data
	 * @return void
	 */
	protected function checkResponse (ResponseInterface $response, $data)
	{
        if (isset($data['errno']) && $data['errno'] != 0) {
            throw new IdentityProviderException($data['errText'], $data['errno'], $response);
        }
	}

	/**
	 * Get the default scopes used by this provider.
	 *
	 * This should not be a complete list of all scopes, but the minimum
	 * required for the provider user interface!
	 *
	 * @return array
	 */
	protected function getDefaultScopes ()
	{
		return null;
	}

	/**
	 * Generate a user object from a successful user details request.
	 * @param array $response
	 * @param AccessToken $token
	 * @return YouKuResourceOwner
	 */
	protected function createResourceOwner (array $response, AccessToken $token)
	{
		return new YouKuResourceOwner($response);
	}


    /**
     * Generate a signature for request
     * @param array $params
     * @return string
     */
	protected function getSign(array $params)
    {
        ksort($params);

        $str = '';
        foreach ($params as $key => $value ) {
            $str .= $key . $value;
        }

        return md5(urlencode($str) . $this->clientSecret);
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param  array $response
     * @param  AbstractGrant $grant
     * @return AccessTokenInterface
     */
    protected function createAccessToken(array $response, AbstractGrant $grant)
    {
        $token = $response['token'];

        $data = [
            'access_token' => $token['accessToken'],
            'refresh_token' => $token['refreshToken'],
            'expires_in' => $token['expireTime'] - 86400,
            'resource_owner_id' => isset($token['openId']) ? $token['openId'] : '',
        ];

        return parent::createAccessToken($data, $grant);
    }
}
