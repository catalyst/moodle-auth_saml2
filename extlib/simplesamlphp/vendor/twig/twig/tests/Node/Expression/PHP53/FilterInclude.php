<?php

namespace Twig\Tests\Node\Expression\PHP53;

$env = new \Twig\Environment(new \Twig\Loader\ArrayLoader([]));
$env->addFilter(new \Twig\TwigFilter('anonymous', function () {}));

return $env;
