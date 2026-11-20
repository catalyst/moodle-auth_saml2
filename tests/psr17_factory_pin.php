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
 * Pin Slim PSR-17 factory discovery to Guzzle for tests.
 *
 * SimpleSAML 2.4 bundles Nyholm/psr7; once loaded, Slim's PSR-17 discovery
 * picks it over Guzzle and breaks core router tests in the same process.
 *
 * @package    auth_saml2
 * @copyright  2026 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (class_exists(\Slim\Factory\Psr17\Psr17FactoryProvider::class)) {
    \Slim\Factory\Psr17\Psr17FactoryProvider::setFactories([
        \Slim\Factory\Psr17\GuzzlePsr17Factory::class,
    ]);
}
