<?php

namespace WpUserSession;

use Corcel\Model\Option;

class LoggedInCookie
{
    /**
     * Cookie name
     *
     * @var string
     */
    protected $name;

    /**
     * Cookie raw value (unserialized)
     *
     * @var string
     */
    protected $rawValue;

    /**
     * The user's login
     *
     * @var string
     */
    protected $username;

    /**
     * Session expiration date
     *
     * @var int
     */
    protected $expiration;

    /**
     * Session token
     *
     * @var string
     */
    protected $token;

    /**
     * Session hash
     *
     * @var string
     */
    protected $hmac;

    public function __construct()
    {
        $this->name = 'wordpress_logged_in_' . $this->siteHash();
        $this->rawValue = $_COOKIE[$this->name] ?? '';
        $this->parse();
    }

    /**
     * Calculates the site hash based on Wordpress `siteurl` config
     *
     * @return string
     */
    protected function siteHash()
    {
        return md5(Option::get('siteurl'));
    }

    /**
     * Returns the cookie name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the user's login
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the session token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns the session hash
     *
     * @return string
     */
    public function getHmac()
    {
        return $this->hmac;
    }

    /**
     * Returns the session expiration date
     *
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Returns whether the user exists or not
     *
     * @return bool
     */
    public function hasUser()
    {
        return ! empty($this->getUsername());
    }

    /**
     * Parses the cookie and returns the elements of the session
     *
     * @return void
     */
    protected function parse()
    {
        $elements = explode('|', $this->rawValue);
        if (count($elements) !== 4) {
            return;
        }
        list($this->username, $this->expiration, $this->token, $this->hmac) = $elements;
    }
}
