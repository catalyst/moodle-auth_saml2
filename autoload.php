<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Common setup, class loaders etc.
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

spl_autoload_register(
    function($className) {
        $classPath = explode('_', $className);
        if ($classPath[0] != 'SimpleSAML') {
            $classPath = explode('\\', $className);
            if ($classPath[0] != 'SimpleSAML') {
                return;
            }
        }
        $filePath = dirname(__FILE__) . '/extlib/simplesamlphp/lib/' . implode('/', $classPath) . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }
);

spl_autoload_register(
    function($className) {
        $classPath = explode('_', $className);
        if ($classPath[0] != 'sspmod') {
            $classPath = explode('\\', $className);
            if ($classPath[0] != 'sspmod') {
                return;
            }
        }
        array_shift($classPath);
        $module = array_shift($classPath);
        $filePath = dirname(__FILE__) . "/extlib/simplesamlphp/modules/$module/lib/" . implode('/', $classPath) . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }
);

spl_autoload_register(
    function($className) {
        $classPath = explode('_', $className);
        if ($classPath[0] != 'SAML2') {
            $classPath = explode('\\', $className);
            if ($classPath[0] != 'SAML2') {
                return;
            }
        }
        $filePath = dirname(__FILE__) . "/extlib/saml2/src/" . implode('/', $classPath) . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }
);

spl_autoload_register(
    function($className) {
        $classPath = explode('_', $className);
        if ($classPath[0] != 'Psr') {
            $classPath = explode('\\', $className);
            if ($classPath[0] != 'Psr') {
                return;
            }
        }
        $filePath = dirname(__FILE__) . "/extlib/php-fig-log/" . implode('/', $classPath) . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }
);

