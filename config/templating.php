<?php
use Aura\Di\Container;
use My\Web\Lib\View\TemplateEngine;

/** @var Container $di */
/** @var TemplateEngine $engine */

$engine->setDirectory(__DIR__ . '/../templates');
