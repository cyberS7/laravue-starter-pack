<?php

/*
 * This file is part of jwt-auth
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Test\Validators;

use Mockery;
use Tymon\JWTAuth\Validators\PayloadValidator;

class PayloadValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->validator = new PayloadValidator();
    }

    /** @test */
    public function it_should_return_true_when_providing_a_valid_payload()
    {
        $payload = [
            'iss' => 'http://example.com',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 3600,
            'sub' => 1,
            'jti' => 'foo'
        ];

        $this->assertTrue($this->validator->isValid($payload));
    }

    /** @test */
    public function it_should_throw_an_exception_when_providing_an_expired_payload()
    {
        $this->setExpectedException('Tymon\JWTAuth\Exceptions\TokenExpiredException');

        $payload = [
            'iss' => 'http://example.com',
            'iat' => time() - 3660,
            'nbf' => time() - 3660,
            'exp' => time() - 1440,
            'sub' => 1,
            'jti' => 'foo'
        ];

        $this->validator->check($payload);
    }

    /** @test */
    public function it_should_throw_an_exception_when_providing_an_invalid_nbf_claim()
    {
        $this->setExpectedException('Tymon\JWTAuth\Exceptions\TokenInvalidException');

        $payload = [
            'iss' => 'http://example.com',
            'iat' => time() - 3660,
            'nbf' => time() + 3660,
            'exp' => time() + 1440,
            'sub' => 1,
            'jti' => 'foo'
        ];

        $this->validator->check($payload);
    }

    /** @test */
    public function it_should_throw_an_exception_when_providing_an_invalid_iat_claim()
    {
        $this->setExpectedException('Tymon\JWTAuth\Exceptions\TokenInvalidException');

        $payload = [
            'iss' => 'http://example.com',
            'iat' => time() + 3660,
            'nbf' => time() - 3660,
            'exp' => time() + 1440,
            'sub' => 1,
            'jti' => 'foo'
        ];

        $this->validator->check($payload);
    }

    /** @test */
    public function it_should_throw_an_exception_when_providing_an_invalid_payload()
    {
        $this->setExpectedException('Tymon\JWTAuth\Exceptions\TokenInvalidException');

        $payload = [
            'iss' => 'http://example.com',
            'sub' => 1
        ];

        $this->validator->check($payload);
    }

    /** @test */
    public function it_should_throw_an_exception_when_providing_an_invalid_expiry()
    {
        $this->setExpectedException('Tymon\JWTAuth\Exceptions\TokenInvalidException');

        $payload = [
            'iss' => 'http://example.com',
            'iat' => time() - 3660,
            'exp' => 'foo',
            'sub' => 1,
            'jti' => 'foo'
        ];

        $this->validator->check($payload);
    }

    /** @test */
    public function it_should_set_the_required_claims()
    {
        $payload = [
            'iss' => 'http://example.com',
            'sub' => 1
        ];

        $this->assertTrue($this->validator->setRequiredClaims(['iat', 'sub'])->isValid($payload));
    }

    /** @test */
    public function it_should_check_the_token_in_the_refresh_context()
    {
        $payload = [
            'iss' => 'http://example.com',
            'iat' => time() - 2600, // this is less than the refresh ttl at 1 hour
            'nbf' => time(),
            'exp' => time() - 1000,
            'sub' => 1,
            'jti' => 'foo'
        ];

        $this->assertTrue(
            $this->validator->setRefreshFlow()->setRefreshTTL(60)->isValid($payload)
        );
    }

    /** @test */
    public function it_should_throw_an_exception_if_the_token_cannot_be_refreshed()
    {
        $this->setExpectedException('Tymon\JWTAuth\Exceptions\TokenExpiredException');

        $payload = [
            'iss' => 'http://example.com',
            'iat' => time() - 5000, // this is MORE than the refresh ttl at 1 hour, so is invalid
            'nbf' => time(),
            'exp' => time(),
            'sub' => 1,
            'jti' => 'foo'
        ];

        $this->validator->setRefreshFlow()->setRefreshTTL(60)->check($payload);
    }
}
