# Simple CRM RestAPI

A Simple CRM RestAPI using :

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## Requirements

Simple CRM RestAPI is currently extended with the following requirements.  
Instructions on how to use them in your own application are linked below.
| Requirement | Version   |
|-------------|-----------|
| PHP         |   8.3.^   |
| MySQL       |   8.0.40  |
| Laravel     |   11.^.^  |

## Installation

Make sure all requirements are installed on the system.
Clone the project and install dependencies:

```bash
$ git clone https://github.com/wisnuuakbr/simple-api-crm.git
$ cd simple-api-crm
$ composer install
```

## Configuration

Copy the .env.example file and rename it to .env  
Change the config for your local server

```bash
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=simple_crm_db_v1
DB_USERNAME=root
DB_PASSWORD=password
```

## Migration & Seeder

Run the migrations table:

```bash
$ php artisan migrate
```

Run the seeder:

```bash
$ php artisan db:seed
```

## Run Application

Run the application:

```bash
$ php artisan serve
```

Run the UnitTest:

```bash
$ php artisan test
```
