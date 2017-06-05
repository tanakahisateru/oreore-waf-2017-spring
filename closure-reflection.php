<?php
$closure = function ($a, $b) {
    return $a + $b;
};

$reflection = new ReflectionFunction($closure);

var_dump($reflection->invokeArgs([1, 2]));
