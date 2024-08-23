# Detective Domain API (Laravel)

## PHP version

Laravel 11.x requires a minimum PHP version of 8.2.

## Project Setup

```sh
composer installl
```

### Generate Application Key
```sh
php artisan key:generate
```

### Run the application to your local
```sh
php artisan serve
```

### Note
Put the backend.env file that provided on the root folder and remove the 'backend' on the name of the file to make it .env. No need for database since this project is for retrieving data from whoisxmlapi API only. If prompt to run migrate or create migration just say NO.
