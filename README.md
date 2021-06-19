# WP User Session

This package allows you to share a user's Wordpress session with your Laravel application.

## Requirements

* [`jgrossi/corcel`](https://packagist.org/packages/jgrossi/corcel) package installed and configured
* Wordpress and Laravel application on the same domain (Wordpress cookies must be available from Laravel app)

## Installation

Use composer:

`composer require arcesilas/wp-user-session`

Publish the configuration file (required!):

`php artisan vendor:publish --provider="WpUserSession\WpUserSessionServiceProvider"`

## Configuration

[Configure Corcel according to the documentation](https://github.com/corcel/corcel#database-setup).

This packages needs the LOGGED_IN_SALT and LOGGED_IN_KEY from your Wordpress configuration. They are stored in your `wp-config` file at the root of your Wordpress installation. Copy them to the `config/wp-user-session.php` configuration file.

We could have loaded the `wp-config` file, but it also includes `wp-settings` and all that stuff, and implies the declaration of Wordpress' `__()` translation function, which collides with the one from Laravel. It's much easier to just copy/paste 2 strings...

## Subdomains

You need to have your Laravel app and Wordpress on the same domain, to allow Laravel to access the cookies. But you may use subdomains, for example `app.maindomain.tld` and `blog.maindomain.tld`. You just need to configure Wordpress to change the cookie domain.

Open `wp-config.php` file and at the bottom of the file, before `define( 'WP_DEBUG', false );` add:

```
define( 'COOKIE_DOMAIN', 'maindomain.tld' );
```

This will prevent Wordpress from setting cookies on `blog.maindomain.tld`.

Be careful! If you have other subdomains, they will be able to read the Wordpress cookies. It could not be what you want depending on what you use them for.

## Usage

You have nothing to do. The package pushes the `WpSessionMiddleware` to the `web` routes group. You are free to add it to any route/group of your choice, obviously.

When you log in to Wordpress, Laravel will automatically authenticate you in your app with the same user. Remember that it's a Corcel User (or any class of your choice in your Corcel configuration).

At the moment of writing this package, Corcel does not support users roles. There is a [PR](https://github.com/corcel/corcel/pull/352) on the way, but not merged yet.

Wordpress User's roles are stored in the user's metadata, so you can still get it with the `$user->meta->wp_capabilities` (need unserialization) and `$user->meta->wp_user_level`.
