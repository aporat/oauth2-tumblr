<?php

namespace Aporat\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class TumblrResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected array $response;

    /**
     * Creates a new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response['response']['user'] ?? [];
    }

    /**
     * Get a resource owner id (username)
     *
     * The root user object in Tumblr's API v2 doesn't have a numeric ID.
     * The 'name' (username) is the unique user-level identifier.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Get resource owner username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Get the number of blogs the user is following.
     *
     * @return int
     */
    public function getFollowingCount(): int
    {
        return (int) $this->getValueByKey($this->response, 'following');
    }

    /**
     * Get the number of posts the user has liked.
     *
     * @return int
     */
    public function getLikesCount(): int
    {
        return (int) $this->getValueByKey($this->response, 'likes');
    }

    /**
     * Get the user's blogs.
     *
     * @return array
     */
    public function getBlogs(): array
    {
        return $this->getValueByKey($this->response, 'blogs', []);
    }

    /**
     * Return all the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
