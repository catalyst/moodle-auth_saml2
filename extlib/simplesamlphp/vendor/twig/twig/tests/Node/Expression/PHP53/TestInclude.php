<?php

namespace Twig\Tests\Node\Expression\PHP53;

$env = new \Twig\Environment(new \Twig\Loader\ArrayLoader([]));
$env->addTest(new \Twig\TwigTest('anonymous', function () {}));

return $env;
