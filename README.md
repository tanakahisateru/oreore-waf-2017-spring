# OREORE-WAF 2017 Spring

This is experimental project using modern (2017 spring) PHP standards/technologies.

"OREORE" means personally designed and self maintained techs in Japanese web developers culture. In the past,
a certain number of PHP users had their own easy and fragile layers to avoid the difficulty of learning
mature framework APIs.

Though early PHP didn't have enough features to design good framework especially for not matured programmers.
But after 5.x to choose popular framework product is one of best practice for PHP. Furthermore PHP is changing
by new PSRs: PSR-11, PSR-15, PST-17 which focus covering whole of application.

This "OREORE" framework describes how library interoperability realized by PSRs and suggests some ideas
to design framework free well connected components by small implementations. 

## Installation / Usage

```bash
composer install
npm install
node_modules/.bin/gulp
node_modules/.bin/gulp vendor-debugbar
php -S 0.0.0.0:8080 -t web
```

```bash
vendor/bin/codecept build
vendor/bin/codecept run
```

You can check css/js minification and revisioning:
```bash
node_modules/.bin/gulp build
node_modules/.bin/gulp dist
```

## Features

- HTTP Middleware (PSR-7/15/17) based design using Zend Diactoros/Stratigility
- Aura Di (PSR-11) dependency injection container
- Aura Router routing
- Aura Dispatcher with controller dependency injection
- PHP script oriented config
- Inheritance free controllers
- View context to aggregate templates/routes/assets
- Plate template engine enhanced by Zend Escaper
- Asset manager works better with Gulp tasks
- Zend EventManager (PSR-14 like) with exception based event chain interceptor
- Monolog (PSR-3)
- PHP DebugBar to display Monolog result
- Whoops error page
- Codeception functional test support for generic middleware app
