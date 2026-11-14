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
 * Register custom filter controls to a Moodle dynamic table using core_table/dynamic.
 *
 * @module     auth_saml2/manage_idps_filters
 * @copyright  2026 University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import CoreFilter from 'core/datafilter';
import * as DynamicTable from 'core_table/dynamic';
import Selectors from 'core/datafilter/selectors';
import Notification from 'core/notification';
import Pending from 'core/pending';

export const init = (filterRegionId) => {
    const filterSet = document.getElementById(filterRegionId);
    if (!filterSet) {
        return;
    }

    const tableRegionId = filterSet.dataset.tableRegion;
    if (!tableRegionId) {
        return;
    }

    const onFilterChange = (filters, pendingPromise) => {
        const joinField = filterSet.querySelector(Selectors.filterset.fields.join);
        const jointype = joinField ? parseInt(joinField.value, 10) : 2;

        DynamicTable.setFilters(
            DynamicTable.getTableFromId(tableRegionId),
            {
                jointype,
                filters,
            }
        )
            .then((result) => {
                pendingPromise.resolve();
                return result;
            })
            .catch((error) => {
                pendingPromise.resolve();
                Notification.exception(error);
            });
    };

    const coreFilter = new CoreFilter(filterSet, onFilterChange);
    coreFilter.init();

    const tableRoot = DynamicTable.getTableFromId(tableRegionId);
    const initialFilters = DynamicTable.getFilters(tableRoot);

    if (initialFilters) {
        const initialFilterPromise = new Pending('auth_saml2/filter:setFilterFromConfig');
        initialFilterPromise.resolve();
    }
};
