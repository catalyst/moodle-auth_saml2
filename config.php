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
<table cellspacing="0" cellpadding="5" border="0">
<tr valign="top">
    <?php $field = 'entityid' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><input type="text" size="80" name="<?php echo $field ?>" value="<?php print $config->$field ?>"><br>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'ssourl' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><input type="text" size="80" name="<?php echo $field ?>" value="<?php print $config->$field ?>"><br>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'slourl' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><input type="text" size="80" name="<?php echo $field ?>" value="<?php print $config->$field ?>"><br>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'certfingerprint' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><textarea columns="80" rows="8" name="<?php echo $field ?>" style="width: 100%"><?php print $config->$field ?></textarea><br>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
<tr valign="top">
    <?php $field = 'debug' ?>
    <td align="right"><label for="<?php echo $field ?>"><?php print_string($field, 'auth_saml2') ?></label></td>
    <td><?php echo html_writer::select($yesno, $field, $config->$field, false) ?>
        <?php print_string($field.'_help', 'auth_saml2') ?></td>
</tr>
</table>

