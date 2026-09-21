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

namespace auth_saml2\table;

use core_table\local\filter\filterset;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;


/**
 * Filterset for auth_saml2_idps table.
 *
 * @copyright University of Graz 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class idps_table_filterset extends filterset {
    /**
     * Get the optional filters
     *
     * @return array
     */
    public function get_optional_filters(): array {
        return [
            'activeidp'        => integer_filter::class,
            'defaultidp'       => integer_filter::class,
            'name'             => string_filter::class,
            'adminidp'         => integer_filter::class,
            'allowlistenabled' => integer_filter::class,
        ];
    }
}
