# Symfony Starter Pack

This is a simple project to quickly start a new Symfony application as I'm fed up to do this all again everytime I have a silly project idea that will inevitably go nowhere.

**This is for my personal projects, you should not expect, and it should not be used in a entreprise-level production standard but can be a starting point (Except for OAuth, do not use that its really ugly).**

It contains:

- A dev ready frankenphp setup
- Linters (php-cs-fixer, phpstan & biomejs)
- A prod-ready Dockerfile
- Two Github Actions workflow
  - lint: On each PR this run php-cs-fixer, phpstan, phpunit and biomejs
  - release: On tag starting with the "v" letter (e.g. v0.1.0) this builds the prod docker image and push it to GHCR

For the backend:
- PHP 8.4 / Symfony 7.3 / Api Platform 4.2
- Doctrine with a Postgres database
- Cache, Messenger, Rate limiter components preconfigured with Redis (with unit-test setup for all those components, check out ForgottenPasswordApiTest.php)
- Mercure for real-time features
- Sentry
- API translatable enums
- Translated validation errors
- A simple user class preconfigured with LexikJWTAuthenticationBundle & GesdinetRefreshTokenBundle
- A custom normalizer to add mercure grants to the token
- A nice Makefile to handle common commands
- Unit test with an helper browser class tailored to Api Platform and basic User unit tests
- Email-setup with an exemple "forgotten password" one and a email-updated one.
- Basic OAuth login
  - Caution: This was only tested with keycloak, not sure if I rely on some Keycloak specific behavior
  - Caution 2: The roles are synchronized with Keycloak, once a user logged in through OAuth, you should no longer manage their roles in your app.
  - If you want to remove this you need to:
    - Remove the `Service\{OAuthUserUpdater.php, UniqueUsernameStrategy.php}` files.
    - Remove the interface `OAuthUserInterface` and the `OAuthUserTrait` from the User entity.
    - Remove the `config/{packages,routes}/qne_oauth.yaml` files.
    - Remove the two user checker from the given bundle in `config/packages/security.yaml`.
    - Remove the `Qne\OAuthBundle` from the `config/bundles.php`.
    - Finally, remove the `oxodao/qne-oauth-bundle` dependency with composer.

For the frontend:
- A React app
- Tailwind + shadcn with a default theme
- Tanstack Router + Tanstack Query
- React Hook Form
- Base setup to easily use Api Platform with Typescript / RHF
- Base setup to call the API properly (sdk-ish)
- Authentication (password + oauth)
- Mercure
- A simple demo route: User profile with an edit form
- Password forgotten flow

## Usage

1. Fork (or clone + replace the upstream) this repository.
2. Search & replace all occurence of "my_project" by your project name (in snake_case, but also myproject and MY_PROJECT accordingly).
3. Also search & replace "my_name" with your Github username
4. Commit
5. Copy and fill the `.env.local.dist` to `.env.local`
6. Run `make && make reset-db`
7. Have fun

**Note**: Most setup you'll see online will open multiple ports: 80/443/8080/8443 for php and 5173 for the frontend.

Here that's not the case. For ease of use, and to have SSL (Mercure or some special browser APIs) we proxy the frontend through Caddy.

In practice, this mean you can see your project at https://localhost with the API being located at https://localhost/api.

Note that this also let you use those special API on your local network (e.g. you want to test on your phone).

**TIPS**: If you define a custom domain on your DNS (router settings or idk how your stuff is configured) to something like `my_project.local`, you can set the SERVER_NAME to this value and modify the `.env` to set DEFAULT_URI so that your OAuth works on your lan!

There is a Bruno collection to test your API.

The default fixtures provide an "admin" user, a "user" user, and multiple randomly generated users.

All passwords are "password".

## Setup - PHPStorm

This guide is for Linux.

If you are running under WSL, it is highly advised to use the "Remote WSL" feature of PHPStorm so that you're running the "native" Phpstorm Linux backend (File > Remote Development > WSL > Open your project from here).

No clue about OSX, it should probably work the same as linux but I don't have a Mac to confirm.

First we need to setup the interpreter. Go into the settings (Ctrl + Alt + S), in PHP you should see `CLI Interpreter`.
Click the "..." button and add a new one from docker. Select the docker-compose radio box and choose the `app` service.
Click `Ok` and then in the main CLI Interpreter window, on your newly created one, ensure that the "Connect to existing container" is selected
instead of "Always start a new container".

Next, go into the settings, in PHP you'll have the `Server` page. Create a new one and name it `my_project` (The name you changed everywhere).
Fill the host with `localhost` and let the port `80` we don't care. Check the "Use mapping" checkbox and add one that goes to the `backend` folder
and wire it to `/app`.

Finally, while we're at it, in the settings, PHP, Quality tools check "PHP CS Fixer" as the external formatter, and in the sub-pages for 
php-cs-fixer and phpstan ensure they are both turned on and using the newly created interpreter and their config files points to the
`/your/host/path/backend/.php-cs-fixer.dist.php` and `/your/host/path/backend/phpstan.dist.neon` respectively. Be sure they are enabled too.

In the settings, PHP, Test Framework you will also need to setup `PHPUnit Remote Interpreter` and select the one we just created
if you want to use the PHPUnit integration in PHPStorm. Be sure to specify the configuration file with the path `/app/phpunit.dist.xml`.
It should auto-detect phpunit. If that's not the case, ensure that its using the `phpunit.phar` with the path `/app/vendor/bin/phpunit`.

Ensure the IDE is listening (in the top right of the window you have a "Start Listening for PHP Debug Connections" button), place a breakpoint in your code and it should just work. Hopefully.

Usage in CLI:
```shell
$ XDEBUG_TRIGGER=1 bin/console user:jwt:gen admin
```

Usage in bruno:
```
GET /my-endpoint?XDEBUG_TRIGGER=1
```

## OAuth

In a perfect world, you want to use the OAuth token to authenticate to the API directly, but we don't want that because of Mercure (I DO NOT want to deal with this manually even if you theorically could with custom claims + using the KC key as the subscriber key).

Set up your Keycloak instance as told by the [bundle's readme](https://github.com/oxodao/qne-oauth-bundle), let's configure Symfony.

In your `.env.local` fill the variables:
  - OAUTH_BASE_URL=https://YOUR_KEYCLOAK_URL/auth/realms/master/protocol/openid-connect
  - OAUTH_CLIENT_ID=my_project
  - OAUTH_CLIENT_SECRET=the_secret_found_on_keycloak
