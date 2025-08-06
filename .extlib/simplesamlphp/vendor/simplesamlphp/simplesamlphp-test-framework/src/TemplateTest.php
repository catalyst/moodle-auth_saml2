<?php

declare(strict_types=1);

namespace SimpleSAML\TestUtils;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\Module;
use SimpleSAML\XHTML\Template;
use Twig\Error\SyntaxError;
use Twig\Source;

/**
 * Simple test for syntax-checking Twig-templates.
 *
 * @author Tim van Dijen <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 *
 * @psalm-suppress InternalMethod
 */
class TemplateTest extends TestCase
{
    /**
     * @return void
     */
    public function testSyntax()
    {
        $config = Configuration::loadFromArray([
            'module.enable' => array_fill_keys(Module::getModules(), true),
        ]);
        Configuration::setPreLoadedConfig($config);

        $basedir = $config->getBaseDir() . 'templates';
        if (file_exists($basedir)) {
            $files = array_diff(scandir($basedir), ['.', '..']);

            // Base templates
            foreach ($files as $file) {
                if (preg_match('/.twig$/', $file)) {
                    $t = new Template($config, $file);

                    $source = new Source(file_get_contents($basedir . DIRECTORY_SEPARATOR . $file), $file);

                    try {
                        $t->getTwig()->tokenize($source);
                        $this->addToAssertionCount(1);
                    } catch (SyntaxError $e) {
                        $this->fail($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                    }
                }
            }
        }

        // See if this is the base repository or a module. If module, skip
        if (strpos($basedir, 'vendor') === false) {
            // Module templates

            foreach (Module::getModules() as $module) {
                $basedir = Module::getModuleDir($module) . DIRECTORY_SEPARATOR . 'templates';

                if (file_exists($basedir)) {
                    $files = array_diff(scandir($basedir), ['.', '..']);
                    foreach ($files as $file) {
                        if (preg_match('/.twig$/', $file)) {
                            $t = new Template($config, $module . ':' . $file);

                            $source = new Source(file_get_contents($basedir . DIRECTORY_SEPARATOR . $file), $file);

                            try {
                                $t->getTwig()->tokenize($source);
                                $this->addToAssertionCount(1);
                            } catch (SyntaxError $e) {
                                $this->fail($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                            }
                        }
                    }
                }
            }
        }
    }
}
