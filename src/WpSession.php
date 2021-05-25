<?php

namespace WpUserSession;

use Hautelook\Phpass\PasswordHash;
use Corcel\Model\Option;
use Corcel\Model\User;

class WpSession
{
    /**
     * The Wordpress logged_in cookie
     *
     * @var LoggedInCookie
     */
    protected $cookie;

    /**
     * The user logged in
     *
     * @var User|null
     */
    protected $user;

    /**
     * @param LoggedInCookie $cookie
     */
    public function __construct(LoggedInCookie $cookie)
    {
        $this->cookie = new LoggedInCookie();
        if ($this->cookie->hasUser()) {
            $this->user = User::where('user_login', $this->cookie->getUsername())->first();
        }
    }

    /**
     * Returns whether the session is valid or not
     *
     * @return bool
     */
    public function isValid()
    {
        if (null === $this->getUser()) {
            return false;
        }

        return $this->validateToken();

    }

    /**
     * Returns the session's user
     *
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Validates the session token
     *
     * @return bool
     */
    protected function validateToken()
    {
        if (! $this->getUser() instanceof User) {
            return false;
        }

        $pass_frag = substr($this->getUser()->user_pass, 8, 4);

        $salt = config('wp-user-session.logged_in_key') . config('wp-user-session.logged_in_salt');
        $data = implode('|', [
            $this->cookie->getUsername(),
            $pass_frag,
            $this->cookie->getExpiration(),
            $this->cookie->getToken()
        ]);
		$key = hash_hmac('md5', $data, $salt);

		// If ext/hash is not present, compat.php's hash_hmac() does not support sha256.
		$algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
        $data = implode('|', [
            $this->cookie->getUsername(),
            $this->cookie->getExpiration(),
            $this->cookie->getToken()
        ]);
        $hash = hash_hmac( $algo, $data, $key );

		if (! hash_equals($hash, $this->cookie->getHmac())) {
            return false;
		}

        $verifier = function_exists('hash')
            ? hash('sha256', $this->cookie->getToken())
            : sha1($this->cookie->getToken());


        return $this->hasValidSession($verifier);
    }

    /**
     * Retrieves the session token from user's metadata
     *
     * @param string $verifier
     * @return \Illuminate\Support\Collection
     */
    protected function getSessionFromMeta($verifier)
    {
        if (! $this->user instanceof User) {
            return collect([]);
        }

        return collect(unserialize($this->user->meta->session_tokens))
            ->map(function ($sess, $key) {
                return is_int($sess) ? ['expiration' => $sess] : $sess;
            })
            ->filter(function ($sess, $key) use ($verifier) {
                return $sess['expiration'] > time() && $key === $verifier;
            });
    }

    /**
     * Returns whether the user has a valid session
     *
     * @param string $verifier
     * @return bool
     */
    protected function hasValidSession($verifier)
    {
        return $this->getSessionFromMeta($verifier)->isNotEmpty();
    }
}
