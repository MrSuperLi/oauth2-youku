<?php namespace MrSuperLi\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class YouKuResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId()
    {
        return isset($this->response['id']) ? $this->response['id'] : null;
    }

    /**
     * Get resource owner nickname
     *
     * @return mixed|null
     */
    public function getNickname()
    {
        return isset($this->response['name']) ? $this->response['name'] : null;
    }

    /**
     * Get resource owner avatar
     *
     * @return mixed|null
     */
    public function getAvatar()
    {
        return isset($this->response['avatar']) ? $this->response['avatar'] : null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
