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
 * Class idps_filter
 *
 * @package    auth_saml2
 * @copyright  2026 University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_saml2\output;

use core\output\datafilter;
use renderer_base;
use stdClass;

/**
 * Filter renderable for the SAML2 IdPs table.
 *
 * @package    auth_saml2
 */
class idps_filter extends datafilter {
    /**
     * Describe all available filter types for this table.
     *
     * @return stdClass[]
     */
    protected function get_filtertypes(): array {
        $filtertypes = [];

        // Filter: name (string / text search for defaultname and displayname).
        $filtertypes[] = $this->get_filter_object(
            'name',
            get_string('name'),
            true,
            true,
            'core/datafilter/filtertypes/keyword',
            [],
            true
        );

        $binaryvalues = [
            (object)[
                'value' => 1,
                'title' => get_string('yes'),
            ],
            (object)[
                'value' => 0,
                'title' => get_string('no'),
            ],
        ];

        // Filter: activeidp (binary: yes/no).
        $filtertypes[] = $this->get_filter_object(
            'activeidp',
            get_string('multiidp:label:active', 'auth_saml2'),
            false,
            false,
            'core/datafilter/filtertypes/binary',
            $binaryvalues,
            false
        );

        // Filter: defaultidp (binary: yes/no).
        $filtertypes[] = $this->get_filter_object(
            'defaultidp',
            get_string('multiidp:label:defaultidp', 'auth_saml2'),
            false,
            false,
            'core/datafilter/filtertypes/binary',
            $binaryvalues,
            false
        );

        // Filter: adminidp (binary: yes/no).
        $filtertypes[] = $this->get_filter_object(
            'adminidp',
            get_string('multiidp:label:admin', 'auth_saml2'),
            false,
            false,
            'core/datafilter/filtertypes/binary',
            $binaryvalues,
            false
        );

        // Filter: whether the ip list feature is used (binary: yes/no).
        $filtertypes[] = $this->get_filter_object(
            'allowlistenabled',
            get_string('allowlistenabled', 'auth_saml2'),
            false,
            false,
            'core/datafilter/filtertypes/binary',
            $binaryvalues,
            false
        );

        return $filtertypes;
    }

    /**
     * Export data for the core/datafilter/filter template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'filtertypes'   => $this->get_filtertypes(),
            'showallbutton' => false,
            'tableregionid' => $this->tableregionid,
            // 'courseid'      => 0,
            'uniqid'        => $this->tableregionid,
        ];
    }
}
