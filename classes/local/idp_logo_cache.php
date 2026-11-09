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

namespace auth_saml2\local;

use core\url as moodle_url;
use core\context\system;
use file_exception;

/**
 * Class idp_logo_cache
 *
 * @package    auth_saml2
 * @copyright  2026 University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class idp_logo_cache {
    const COMPONENT = 'auth_saml2';
    const FILEAREA = 'idplogo';
    const FILEPATH = '/';

    /**
     * Cache remote idp icon to local plugin filearea.
     *
     * @param string $remoteurl The remote origin URL of the icon/logo to cache.
     * @param int    $itemid    The id of the idp in the auth_saml2_idps table.
     * @return \stored_file|false
     */
    public static function cache_logo($remoteurl, $itemid) {
        $fs = get_file_storage();

        $fileinfo = [
            'contextid' => system::instance()->id,
            'component' => self::COMPONENT,
            'filearea'  => self::FILEAREA,
            'itemid'    => $itemid,
            'filepath'  => self::FILEPATH,
            'filename'  => self::get_filename($remoteurl),
        ];

        try {
            $file = $fs->create_file_from_url($fileinfo, url: $remoteurl);
            return $file;
        } catch (file_exception $e) {
            return false;
        }
    }

    /**
     * Delete a cached logo.
     *
     * @param int $itemid The id of the idp. `itemid` of the file is the `auth_saml2_idps.id`.
     * @return void
     */
    public static function delete_cached_logo($itemid) {
        $fs = get_file_storage();
        $fs->delete_area_files(system::instance()->id, self::COMPONENT, self::FILEAREA, $itemid);
    }

    /**
     * Get the URL of a cached logo, only if the file exists.
     *
     * @param object $idp
     * @return null|moodle_url
     */
    public static function get_cached_logo($idp) {
        if (empty($idp->logo)) {
            return null;
        }

        $context   = system::instance();
        $component = self::COMPONENT;
        $filearea  = self::FILEAREA;
        $itemid    = $idp->id;
        $filepath  = self::FILEPATH;
        $filename  = self::get_filename($idp->logo);

        if ($filename === '' || $filename === null) {
            return null;
        }

        $fs   = get_file_storage();
        $file = $fs->get_file(
            $context->id,
            $component,
            $filearea,
            $itemid,
            $filepath,
            $filename
        );

        if (!$file) {
            return null;
        }

        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            false
        );
    }


    /**
     * Generate a unique filename via hashing the remote source URL and taking the original file extension.
     *
     * @param mixed $remoteurl
     * @return string
     */
    private static function get_filename($remoteurl): string {
        $hash = md5($remoteurl);
        // Try to preserve extension from URL, default to png.
        $basename = basename(parse_url($remoteurl, PHP_URL_PATH) ?? '');
        $ext = pathinfo($basename, PATHINFO_EXTENSION) ?: 'png';
        return "{$hash}.{$ext}";
    }
}
