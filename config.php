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
 * Admin config settings page
 *
 * @package    auth_saml2
 * @copyright  Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$yesno = array(get_string('no'), get_string('yes'));

?>
<table cellspacing="0" cellpadding="5" border="0" class="generaltable">
<tr valign="top">
    <?php $field = 'idpname' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><input type="text" size="40" name="<?php echo $field ?>" value="<?php print $config->$field ?>"><br>
        <?php if (isset($err[$field])) { echo $OUTPUT->notification($err[$field], 'notifyfailure'); } ?>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'idpmetadata' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><textarea columns="80" rows="10" name="<?php echo $field ?>" style="width: 100%"
    ><?php print $config->$field ?></textarea><br>
        <?php if (isset($err[$field])) { echo $OUTPUT->notification($err[$field], 'notifyfailure'); } ?>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'debug' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><?php echo html_writer::select($yesno, $field, $config->$field, false) ?>
        <?php if (isset($err[$field])) { echo $OUTPUT->notification($err[$field], 'notifyfailure'); } ?>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'spmetadata' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><?php print_string($field.'_help', 'auth_saml2', array(
        "meta" => "$CFG->wwwroot/auth/saml2/sp/metadata.php",
        "debug" => "$CFG->wwwroot/auth/saml2/debug.php",
    )) ?>
        <?php if (isset($err[$field])) { echo $OUTPUT->notification($err[$field], 'notifyfailure'); } ?>
    </td>
</tr>
<tr valign="top">
    <?php $field = 'duallogin' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><?php echo html_writer::select($yesno, $field, $config->$field, false) ?>
        <?php if (isset($err[$field])) { echo $OUTPUT->notification($err[$field], 'notifyfailure'); } ?>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'anyauth' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><?php echo html_writer::select($yesno, $field, $config->$field, false) ?>
        <?php if (isset($err[$field])) { echo $OUTPUT->notification($err[$field], 'notifyfailure'); } ?>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
</table>

