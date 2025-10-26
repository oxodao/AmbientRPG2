# General infos

This project is [Explain your project]

## General notes

The user KNOWS the stack. Do NOT explain what is Symfony, React, Docker, etc...

Do not explain how to install dependencies or setting up the dev environment.

Don't do weird hacks try to make everything work with the standard Symfony / React practicies.

WE ARE IN FUCKING 2025 DO NOT USE STUPID 2016-ASS DATAPROVIDER IN API PLATFORM USE THE FUCKING STATE PROVIDER/PROCESSOR

## Backend

The backend is located in the `backend` folder and runs the following technologies:
- PHP 8.4
- Symfony 7.3
- Api Platform 4.2
- Doctrine with a Postgres database
- Redis

To execute ANY command related to the backend use the following pattern:
```bash
docker compose exec app [YOUR COMMAND]
```

The project is linted by php-cs-fixer and phpstan. You should follow their configurations.

### Testing

If you want to run a specific test only, do one use the File::method arg but instead the `--filter` parameter of phpunit.

## Frontend

The frontend is located in the `frontend` folder and runs the following technologies:
- React
- Tailwind + shadcn
- Tanstack Router + Tanstack Query
- React Hook Form

The frontend is linted by biomejs. You should follow its configuration.

## Database

The project uses a Postgres database. The database is automatically created when you start the project with docker-compose.

The `make reset-db` will reset the database and load some fixtures.

If you need to access the database directly, you can use the following command:
```bash
docker compose exec db psql -U ambientrpg -d ambientrpg
```

## Other stuff
If at any point you need to lint the project, run the following command that will do php-cs-fixer, phpstan and biomejs checks:
```bash
make lint
```

The docker folder contains everything related to building the images.

The contrib folder is only used by users to host the app themself and should NOT BE USED during development.

When giving urls to the user, keep in mind that EVERYTHING is running behind the Caddy proxy. Check the compose file to know the app's port if the user has changed it. Do not forget to use https, and if the ports are the default ones (80/443) DO NOT APPEND THEM to the URL.