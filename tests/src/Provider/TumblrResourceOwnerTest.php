<?php

namespace Aporat\OAuth2\Client\Test\Provider;

use Aporat\OAuth2\Client\Provider\TumblrResourceOwner;
use PHPUnit\Framework\TestCase;

class TumblrResourceOwnerTest extends TestCase
{
    public function testCanCreateUserAndReadProperties(): void
    {
        $username = 'testuser';
        $likes = 123;
        $following = 456;

        // This is the mock response structure from /v2/user/info
        $mockResponse = [
            'meta' => [
                'status' => 200,
                'msg' => 'OK'
            ],
            'response' => [
                'user' => [
                    'name' => $username,
                    'likes' => $likes,
                    'following' => $following,
                    'blogs' => [
                        ['name' => 'testuser', 'title' => 'My Blog'],
                        ['name' => 'another-blog', 'title' => 'Side Blog']
                    ]
                ]
            ]
        ];

        $user = new TumblrResourceOwner($mockResponse);

        // Test the methods from our TumblrResourceOwner class
        $this->assertEquals($username, $user->getId());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($likes, $user->getLikesCount());
        $this->assertEquals($following, $user->getFollowingCount());
        $this->assertCount(2, $user->getBlogs());
        $this->assertEquals($mockResponse['response']['user'], $user->toArray());
    }
}
