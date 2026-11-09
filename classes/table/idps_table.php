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

use core_table\sql_table;

use core_table\dynamic;

/**
 * Idps Table.
 *
 * @copyright University of Graz 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class idps_table extends sql_table implements dynamic {
    /**
     * Set a default unique ID in the constructor.
     */
    public function __construct($uniqueid) {
        // Define columns.
        $this->define_columns(['id', 'name', 'entityid', 'actions']);
        $this->define_headers([
            'ID',
            get_string('name'),
            'Entity ID',
            get_string('actions'),
        ]);

        // Make sortable.
        $this->sortable(true, 'defaultname', SORT_ASC);

        // Set pagination.
        $this->pageable(true);
        $this->set_default_per_page(25);

        // Set base URL.
        $this->define_baseurl(new \moodle_url('/auth/saml2/manageidps.php'));

        // Create filterset.
        $filterset = new \auth_saml2\table\idps_table_filterset();
        $this->set_filterset($filterset);

        $this->setup();

        $this->attributes['id'] = $uniqueid;

        $this->set_sql(
            'id, metadataurl, entityid, activeidp, defaultidp, defaultname, displayname, adminidp, whitelist',
            '{auth_saml2_idps}',
            '1=1'
        );
        parent::__construct($uniqueid);
    }

    /**
     * Summary of has_capability
     * @return bool
     */
    public function has_capability(): bool {
        return has_capability('moodle/site:config', \context_system::instance());
    }

    /**
     * Context is system context.
     *
     * @return \core\context\system
     */
    public function get_context(): \context {
        return \context_system::instance();
    }

    /**
     * Base URL is the settings page.
     *
     * @return void
     */
    public function guess_base_url(): void {
        if (empty($this->baseurl)) {
            $this->baseurl = (new \moodle_url('/auth/saml2/manageidps.php'))->out();
        }
    }

    /**
     * Get the SQL WHERE fragment and parameters for the current filters.
     *
     * @return array{0:string,1:array}
     */
    public function get_sql_where(): array {
        global $DB;

        [$where, $params] = parent::get_sql_where();
        $filterset = $this->get_filterset();

        if (!$filterset) {
            return [$where, $params];
        }

        $customconditions = [];
        $customparams = [];

        // Handle name filter (text search on two fields).
        if ($filterset->has_filter('name')) {
            $filter = $filterset->get_filter('name');
            $values = $filter->get_filter_values();

            if (!empty($values)) {
                $conditions = [];
                foreach (array_values($values) as $index => $value) {
                    $conditions[] = $DB->sql_like('defaultname', ':name_default_' . $index, false, false) .
                                ' OR ' .
                                $DB->sql_like('displayname', ':name_display_' . $index, false, false);
                    $customparams['name_default_' . $index] = '%' . $value . '%';
                    $customparams['name_display_' . $index] = '%' . $value . '%';
                }

                $operator = $filter->get_join_type() === $filter::JOINTYPE_ALL ? ' AND ' : ' OR ';
                $customconditions[] = '(' . implode($operator, $conditions) . ')';
            }
        }

        // Handle integer filters (activeidp, defaultidp).
        foreach (['activeidp', 'defaultidp', 'adminidp'] as $filtername) {
            if ($filterset->has_filter($filtername)) {
                $filter = $filterset->get_filter($filtername);
                $values = $filter->get_filter_values();

                if (!empty($values)) {
                    [$insql, $filterparams] = $DB->get_in_or_equal($values, SQL_PARAMS_NAMED, $filtername);

                    if ($filter->get_join_type() === $filter::JOINTYPE_NONE) {
                        $customconditions[] = "{$filtername} NOT {$insql}";
                    } else {
                        $customconditions[] = "{$filtername} {$insql}";
                    }

                    $customparams = array_merge($customparams, $filterparams);
                }
            }
        }

        // Handle allowlist filter.
        if ($filterset->has_filter('allowlistenabled')) {
            $filter = $filterset->get_filter('allowlistenabled');
            $values = $filter->get_filter_values();

            if (!empty($values)) {
                $allowlistenabled = reset($values);

                if ($allowlistenabled) {
                    // Allowlist is enabled: whitelist must contain something.
                    $customconditions[] = "COALESCE(TRIM(whitelist), '') <> ''";
                } else {
                    // Allowlist is disabled: whitelist must be empty or whitespace only.
                    $customconditions[] = "COALESCE(TRIM(whitelist), '') = ''";
                }
            }
        }

        if (!$customconditions) {
            return [$where, $params];
        }

        $customwhere = implode(' AND ', $customconditions);
        $where       = $where ? "({$where}) AND ({$customwhere})" : $customwhere;

        return [$where, array_merge($params, $customparams)];
    }

    /**
     * Render the effective IdP name and display-name editor action.
     *
     * An empty display name means that the default name is used.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_name(\stdClass $row): string {
        $displayname = trim($row->displayname ?? '');
        $name = $displayname !== '' ? $displayname : $row->defaultname;

        return \html_writer::span(
            $name,
            'auth_saml2-idp-name'
        );
    }

    /**
     * Render column for Entity ID with metadata URL tooltip.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_entityid(\stdClass $row): string {
        $entityid = $row->entityid ?? '';
        $metadataurl = $row->metadataurl ?? '';

        $title = get_string('metadataurl', 'auth_saml2', $metadataurl) . ': ' . $row->metadataurl;

        $attrs = [
            'class' => 'auth_saml2-entityid-tooltip',
            'data-bs-toggle' => 'tooltip',
            'title' => $title,
        ];

        return \html_writer::tag('span', s($entityid), $attrs);
    }

    /**
     * Render Column Whether IdP is active with action button.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_actions(\stdClass $row): string {
        global $OUTPUT;

        $isactive = !empty($row->activeidp);
        $buttonlabel = $isactive
            ? get_string('deactivateidp', 'auth_saml2')
            : get_string('activateidp', 'auth_saml2');

        $attrs = [
            'type' => 'button',
            'class' => 'btn btn-link p-0 m-0 border-0 auth_saml2-idp-active-toggle',
            'data-idp-id' => $row->id,
            'data-field' => 'activeidp',
            'data-active' => $isactive ? '1' : '0',
            'aria-pressed' => $isactive ? 'true' : 'false',
            'aria-label' => $buttonlabel,
            'label' => $buttonlabel,
            'title' => $buttonlabel,
        ];

        $iconname = $isactive ? 't/hide' : 't/show';
        $icon = $OUTPUT->pix_icon($iconname, $buttonlabel, 'moodle', ['class' => 'fa-fw']);

        $activecontrol = \html_writer::tag('button', $icon, $attrs);

        $attrs = [
            'type' => 'button',
            'class' => 'btn btn-link p-0 ml border-0 auth_saml2-idp-edit',
            'data-idp-id' => $row->id,
            'data-idp-defaultname' => $row->defaultname,
            'data-idp-entityid' => $row->entityid,
            'data-idp-whitelist' => $row->whitelist,
            'data-idp-adminidp' => $row->adminidp,
            'data-idp-defaultidp' => $row->defaultidp,
            'data-idp-displayname' => $row->displayname,
            'title' => get_string('edit'),
            'aria-label' => get_string('edit'),
        ];

        $icon       = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $editbutton = \html_writer::tag('button', $icon, $attrs);

        // Moodle Workplace - Tenant availability edit button.
        $tenantbutton = '';
        if (class_exists('\tool_tenant\local\auth\saml2\manager')) {
            $idpinfo = [
                'id' => $row->entityid,
                'name' => !empty($row->displayname) ? $row->displayname : $row->defaultname,
            ];

            $tenantbutton = component_class_callback(
                '\tool_tenant\local\auth\saml2\manager',
                'issuer_tenant_availability_button',
                [$idpinfo],
                ''
            );
        }

        return \html_writer::div("{$activecontrol}{$editbutton}{$tenantbutton}", 'auth_saml2-idp-action-container d-inline-flex');
    }
}
