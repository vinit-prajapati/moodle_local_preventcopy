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
 * Hook callbacks for Prevent Copy plugin.
 *
 * @package   local_preventcopy
 * @copyright 2025 Vinit Prajapati <vinit4ce@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Vinit Prajapati
 */

namespace local_preventcopy;

use core\check\performance\debugging;
use core\hook\output\before_standard_footer_html_generation;

/**
 * Hook callbacks handler for Prevent Copy plugin.
 *
 * @package   local_preventcopy
 * @copyright 2025 Vinit Prajapati <vinit4ce@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Hook callback for before_standard_footer_html_generation
     * Fires before HTTP headers are sent to browser
     */
    public static function before_standard_footer_html_generation(before_standard_footer_html_generation $hook) {
        global $USER, $PAGE;

        debugging('PREVENTCOPY: Hook triggered');

        try {
            // Skip for admin users.
            if (is_siteadmin()) {
                debugging('PREVENTCOPY: Skipped - admin user');
                return;
            }

            $pageurl = $PAGE->url->out();
            debugging('PREVENTCOPY: URL: ' . $pageurl);

            // Check if the page URL should prevent copy.
            if (!self::local_preventcopy_should_prevent_copy($pageurl)) {
                debugging('PREVENTCOPY: Skipped - URL not in list');
                return; // Skip if the page URL is not in the list.
            }

            // Get user roles.
            $roles = get_user_roles($PAGE->context, $USER->id);
            $isstudent = false;
            $isteacher = false;

            foreach ($roles as $role) {
                if ($role->shortname === 'student') {
                    $isstudent = true;
                } else if ($role->shortname !== 'teacher') {
                    $isteacher = true;
                }
            }

            // Check role permissions.
            $studentroleenabled = get_config('local_preventcopy', 'studentrole');
            $teacherroleenabled = get_config('local_preventcopy', 'nonstudentrole');

            debugging('PREVENTCOPY: Student=' . ($isstudent ? 'YES' : 'NO') . ' Teacher=' . ($isteacher ? 'YES' : 'NO'));
            $allowinject = ($studentroleenabled && $isstudent) || ($teacherroleenabled && $isteacher);

            if (!$allowinject) {
                debugging('PREVENTCOPY: Skipped - role not enabled');
                return;
            }

            // Generate JS based on settings.
            $jscript = get_config('local_preventcopy', 'preventcopyjs');
            $hook->add_html($jscript);

            debugging('PREVENTCOPY: ✓ Injection complete');

        } catch (\Exception $e) {
            debugging('PREVENTCOPY: ERROR - ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Check if the page URL should prevent copy.
     *
     * @param string $pageurl The page URL to check.
     * @return bool True if the page URL should prevent copy, false otherwise.
     */
    private static function local_preventcopy_should_prevent_copy($pageurl) {
        $listofpages = get_config('local_preventcopy', 'listofpages');
        $arraylistofpages = preg_split("/\r\n|\n|\r/", $listofpages);
        $path = parse_url($pageurl, PHP_URL_PATH);
        foreach ($arraylistofpages as $line) {
            if (strpos($pageurl, $line) !== false) {
                return true;
            }
        }
        return false;
    }


}
