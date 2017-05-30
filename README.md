[![Build Status](https://travis-ci.org/tanakahisateru/oreore-waf-2017-spring.svg?branch=master)](https://travis-ci.org/tanakahisateru/oreore-waf-2017-spring)

# OREORE-WAF 2017 Spring

This is experimental project using modern (2017 spring) PHP standards/technologies.

"OREORE" means personally designed and self maintained techs in Japanese web developers culture. In the past,
a certain number of PHP users had their own easy and fragile layers to avoid the difficulty of learning
mature framework APIs.

Though early PHP didn't have enough features to design good framework especially for not matured programmers.
But after 5.x to choose popular framework product is one of best practice for PHP. Furthermore PHP is changing
by new PSRs:
[PSR-11](https://github.com/container-interop/fig-standards/blob/master/proposed/container.md),
[PSR-15](https://github.com/php-fig/fig-standards/tree/master/proposed/http-middleware),
[PSR-17](https://github.com/php-fig/fig-standards/tree/master/proposed/http-factory)
which focus covering whole of application.

This "OREORE" framework describes how library interoperability realized by PSRs and suggests some ideas
to design framework free well connected components by small implementations. 

## Installation / Usage

```bash
composer install
npm install
gulp
gulp vendor-debugbar
php -S 0.0.0.0:8080 -t web
```

```bash
vendor/bin/codecept build
vendor/bin/codecept run
```

You can check css/js minification and revisioning:
```bash
gulp build
gulp dist
```

## Features

- HTTP Middleware ([PSR-7](http://www.php-fig.org/psr/psr-7/)/15/17) based design using
[Zend Diactoros](https://zendframework.github.io/zend-diactoros/) /
[Zend Stratigility](https://docs.zendframework.com/zend-stratigility/)
- [Aura.Di](https://github.com/auraphp/Aura.Di) (PSR-11) dependency injection container
- [Aura.Router](https://github.com/auraphp/Aura.Router) routing
- [Aura.Dispatcher](https://github.com/auraphp/Aura.Dispatcher) with controller dependency injection
- PHP script oriented config
- Inheritance free controllers
- View context to aggregate templates/routes/assets
- [Plate](http://platesphp.com/) template engine enhanced by Zend Escaper
- Minimal asset manager that works better with [Gulp](http://gulpjs.com/) tasks
- [Zend EventManager](https://zendframework.github.io/zend-eventmanager/) ([PSR-14](https://github.com/php-fig/fig-standards/blob/master/proposed/event-manager.md) like)
- [Monolog](https://seldaek.github.io/monolog/) [PSR-3](http://www.php-fig.org/psr/psr-3/) compatible logger
- [PHP Debug Bar](http://phpdebugbar.com/) to display logger result
- [Whoops](https://filp.github.io/whoops/) error page
- [Codeception](http://codeception.com/) functional test support for generic PSR-7/15 middleware app
