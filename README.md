[![Build Status](https://travis-ci.org/tanakahisateru/oreore-waf-2017-spring.svg?branch=master)](https://travis-ci.org/tanakahisateru/oreore-waf-2017-spring)

# OREORE-WAF 201<del>7</del>8 S<del>pring</del>ummer

This is experimental project using <del>modern</del> regular (2018 the last year of 5.6/7.0) PHP standards/technologies.

"OREORE" means personally designed and self maintained techs in Japanese web developers culture. In the past,
a certain number of PHP users had their own easy and fragile layers to avoid the difficulty of learning
mature framework APIs.

Though early PHP didn't have enough features to design good framework especially for not matured programmers.
But after 5.x to choose popular framework product is one of best practice for PHP. Furthermore PHP has been changed
by new PSRs:
[PSR-11](https://www.php-fig.org/psr/psr-11/),
[PSR-15](https://www.php-fig.org/psr/psr-15/),
[PSR-17](https://www.php-fig.org/psr/psr-17/)
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
