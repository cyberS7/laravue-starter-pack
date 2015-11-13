<?php

/*
 * This file is part of jwt-auth
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth;

use Tymon\JWTAuth\Support\Utils;
use Tymon\JWTAuth\Contracts\Providers\Storage;

class Blacklist
{
    /**
     * @var \Tymon\JWTAuth\Contracts\Providers\Storage
     */
    protected $storage;

    /**
     * The grace period when a token is blacklisted. In seconds
     *
     * @var integer
     */
    protected $gracePeriod = 0;

    /**
     * Number of minutes from issue date in which a JWT can be refreshed.
     *
     * @var integer
     */
    protected $refreshTTL = 20160;

    /**
     * The unique key held within the blacklist
     *
     * @var  string
     */
    protected $key = 'jti';

    /**
     * @param \Tymon\JWTAuth\Contracts\Providers\Storage  $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Add the token (jti claim) to the blacklist
     *
     * @param  \Tymon\JWTAuth\Payload  $payload
     *
     * @return boolean
     */
    public function add(Payload $payload)
    {
        // if there is no exp claim then add the jwt to
        // the blacklist indefinitely
        if (! $payload->hasKey('exp')) {
            return $this->addForever($payload);
        }

        $this->storage->add(
            $this->getKey($payload),
            ['valid_until' => $this->getGraceTimestamp()],
            $this->getMinutesUntilExpired($payload)
        );

        return true;
    }

    /**
     * Get the number of minutes until the token expiry
     *
     * @param   Payload  $payload
     *
     * @return  int
     */
    protected function getMinutesUntilExpired(Payload $payload)
    {
        $exp = Utils::timestamp($payload['exp']);
        $refreshExp = Utils::timestamp($payload['iat'])->addMinutes($this->refreshTTL);

        // get the latter of the two expiration dates
        $lastExp = $exp->max($refreshExp);

        // find the number of minutes until the expiration date,
        // plus 1 minute to avoid overlap
        return $lastExp->diffInMinutes(Utils::now()->subMinute());
    }

    /**
     * Add the token (jti claim) to the blacklist indefinitely
     *
     * @param  Payload  $payload
     *
     * @return boolean
     */
    public function addForever(Payload $payload)
    {
        $this->storage->forever($this->getKey($payload), 'forever');

        return true;
    }

    /**
     * Determine whether the token has been blacklisted
     *
     * @param  \Tymon\JWTAuth\Payload  $payload
     *
     * @return boolean
     */
    public function has(Payload $payload)
    {
        $val = $this->storage->get($this->getKey($payload));

        // exit early if the token was blacklisted forever
        if ($val === 'forever') {
            return true;
        }

        // check whether the expiry + grace has past
        return ! (is_null($val) || Utils::timestamp($val['valid_until'])->isFuture());
    }

    /**
     * Remove the token (jti claim) from the blacklist
     *
     * @param  \Tymon\JWTAuth\Payload  $payload
     *
     * @return boolean
     */
    public function remove(Payload $payload)
    {
        return $this->storage->destroy($this->getKey($payload));
    }

    /**
     * Remove all tokens from the blacklist
     *
     * @return boolean
     */
    public function clear()
    {
        $this->storage->flush();

        return true;
    }

    /**
     * Get the timestamp when the blacklist comes into effect
     * This defaults to immediate (0 seconds)
     *
     * @return integer
     */
    protected function getGraceTimestamp()
    {
        return (int) Utils::now()->addSeconds($this->gracePeriod)->format('U');
    }

    /**
     * Set the grace period
     *
     * @param  integer
     *
     * @return Blacklist
     */
    public function setGracePeriod($gracePeriod)
    {
        $this->gracePeriod = (int) $gracePeriod;

        return $this;
    }

    /**
     * Get the unique key held within the blacklist
     *
     * @param   Payload  $payload
     *
     * @return  mixed
     */
    public function getKey(Payload $payload)
    {
        return $payload->get($this->key);
    }

    /**
     * Set the unique key held within the blacklist
     *
     * @param  string  $key
     */
    public function setKey($key)
    {
        $this->key = value($key);

        return $this;
    }

    /**
     * Set the refresh time limit
     *
     * @param  integer
     *
     * @return Blacklist
     */
    public function setRefreshTTL($ttl)
    {
        $this->refreshTTL = (int) $ttl;

        return $this;
    }
}
