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
use Tymon\JWTAuth\Claims\Expiration;

class ClaimTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->claim = new Expiration(123456);
    }

    /** @test */
    public function it_should_throw_an_exception_when_passing_an_invalid_value()
    {
        $this->setExpectedException('Tymon\JWTAuth\Exceptions\InvalidClaimException');

        $this->claim->setValue('foo');
    }

    /** @test */
    public function it_should_convert_the_claim_to_an_array()
    {
        $this->assertEquals(['exp' => 123456], $this->claim->toArray());
    }

    /** @test */
    public function it_should_get_the_claim_as_a_string()
    {
        $this->assertJsonStringEqualsJsonString((string) $this->claim, $this->claim->toJson());
    }

    /** @test */
    public function it_should_get_the_object_as_json()
    {
        $this->assertJsonStringEqualsJsonString(json_encode($this->claim), $this->claim->toJson());
    }
}
