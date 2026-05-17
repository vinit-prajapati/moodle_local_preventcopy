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

use core\hook\output\before_standard_footer_html_generation;

/**
 * Hook callbacks handler for Prevent Copy plugin.
 */
class hook_callbacks {

    /**
     * Hook callback for before_standard_footer_html_generation.
     */
    public static function before_standard_footer_html_generation(before_standard_footer_html_generation $hook) {
        global $USER, $PAGE;

        self::log('Hook triggered');

        try {
            // Skip site admin.
            if (is_siteadmin()) {
                self::log('Skipped - site admin');
                return;
            }

            // Check if protection applies.
            if (!self::should_prevent_copy()) {
                self::log('Skipped - restrictions not matched');
                return;
            }

            // Determine user roles.
            $roles = get_user_roles($PAGE->context, $USER->id);

            $isstudent = false;
            $isstaff = false;

            foreach ($roles as $role) {
                if ($role->shortname === 'student') {
                    $isstudent = true;
                 } else if ($role->shortname !== 'teacher') {
                    $isstaff = true;
                }
            }

            // Role-based settings.
            $studentroleenabled = get_config('local_preventcopy', 'studentrole');
            $staffroleenabled = get_config('local_preventcopy', 'nonstudentrole');

            self::log(
                'Role check - Student=' . ($isstudent ? 'YES' : 'NO') .
                ' Staff=' . ($isstaff ? 'YES' : 'NO')
            );

            $allowinject = (
                ($studentroleenabled && $isstudent) ||
                ($staffroleenabled && $isstaff)
            );

            if (!$allowinject) {
                self::log('Skipped - role not enabled');
                return;
            }

            // Get JS from settings.
            $jscript = trim((string)get_config('local_preventcopy', 'preventcopyjs'));

            if (empty($jscript)) {
                self::log('Skipped - JS config empty');
                return;
            }

            $hook->add_html($jscript);

            self::log('✓ JS injected successfully');

        } catch (\Throwable $e) {
            debugging(
                'PREVENTCOPY ERROR: ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }
    }

    /**
     * Main decision method.
     *
     * Priority:
     * 1. Whole site
     * 2. Category
     * 3. Course
     * 4. Module
     *
     * @return bool
     */
    private static function should_prevent_copy() {

        // Category restriction.
        if (self::check_category_restriction()) {
            self::log('✓ Matched - Category restriction');
            return true;
        }

        // Course restriction.
        if (self::check_course_restriction()) {
            self::log('✓ Matched - Course restriction');
            return true;
        }

        // Module restriction.
        if (self::check_module_restriction()) {
            self::log('✓ Matched - Module restriction');
            return true;
        }

        return false;
    }

    /**
     * Category restriction.
     *
     * @return bool
     */
    private static function check_category_restriction() {

        $config = get_config('local_preventcopy', 'categoryids');

        if (empty($config)) {
            return false;
        }

        $categoryid = self::get_category_id();
        error_log("*** Category Id in check_category_restriction: " . ($categoryid ?? 'N/A'));

        if ($categoryid === null) {
            return false;
        }

        $result = self::value_in_list($categoryid, $config);

        self::log(
            'Category check - categoryid=' . $categoryid .
            ', config=' . $config .
            ', result=' . ($result ? 'YES' : 'NO')
        );

        return $result;
    }

    /**
     * Course restriction.
     *
     * @return bool
     */
    private static function check_course_restriction() {

        $config = get_config('local_preventcopy', 'courseids');

        if (empty($config)) {
            return false;
        }

        $courseid = self::get_course_id();

        if ($courseid === null) {
            return false;
        }

        $result = self::value_in_list($courseid, $config);

        self::log(
            'Course check - courseid=' . $courseid .
            ', config=' . $config .
            ', result=' . ($result ? 'YES' : 'NO')
        );

        return $result;
    }

    /**
     * Module restriction.
     *
     * @return bool
     */
    private static function check_module_restriction() {

        $cmid = self::get_cmid();

        if ($cmid === null) {
            return false;
        }

        // Excluded CMIDs.
        $excludedcmids = get_config('local_preventcopy', 'excludedcmids');

        if (!empty($excludedcmids) && self::value_in_list($cmid, $excludedcmids)) {
            self::log('Module check - cmid excluded=' . $cmid);
            return false;
        }

        // Included CMIDs.
        $includedcmids = get_config('local_preventcopy', 'includedcmids');

        if (!empty($includedcmids) && self::value_in_list($cmid, $includedcmids)) {
            self::log('Module check - cmid explicitly included=' . $cmid);
            return true;
        }

        $moduletype = self::get_module_type($cmid);

        $configmap = [
            'quiz' => 'quizcmids',
            'assign' => 'assigncmids',
            'forum' => 'forumcmids',
            'lesson' => 'lessoncmids',
            'page' => 'pagecmids',
            'book' => 'bookcmids',
        ];

        $configkey = $configmap[$moduletype] ?? 'commoncmids';

        $configvalue = get_config('local_preventcopy', $configkey);

        if (empty($configvalue)) {
            return false;
        }

        $result = self::value_in_list($cmid, $configvalue);

        self::log(
            'Module check - cmid=' . $cmid .
            ', moduletype=' . $moduletype .
            ', configkey=' . $configkey .
            ', result=' . ($result ? 'YES' : 'NO')
        );

        return $result;
    }

    /**
     * Check value in config list.
     * Supports:
     * - * wildcard
     * - comma-separated values
     * - newline-separated values
     *
     * @param int $value
     * @param string $configvalue
     * @return bool
     */
    private static function value_in_list($value, $configvalue) {

        $configvalue = trim($configvalue);  

        // Wildcard.
        if ($configvalue === '*') {
            return true;
        }

        if (empty($configvalue)) {
            return false;
        }

        $list = self::parse_config_value($configvalue);

        return in_array((int)$value, $list, true);
    }

    /**
     * Parse config values.
     *
     * @param string $configvalue
     * @return array
     */
    private static function parse_config_value($configvalue) {

        static $cache = [];

        if (isset($cache[$configvalue])) {
            return $cache[$configvalue];
        }

        $values = preg_split(
            "/\s*[\r\n,]+\s*/",
            $configvalue,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $result = [];

        foreach ($values as $value) {

            $value = trim($value);

            if (is_numeric($value)) {
                $result[] = (int)$value;
            }
        }

        $cache[$configvalue] = $result;

        return $result;
    }

    /**
     * Get category ID.
     *
     * @return int|null
     */
    private static function get_category_id() {
        global $PAGE;
        error_log("*** Category Id: " . ($PAGE->course ? $PAGE->course->category : 'N/A'));

         if (!empty($PAGE->course->category)) {
            return (int)$PAGE->course->category;
        }

        return null;
    }

    /**
     * Get course ID.
     *
     * @return int|null
     */
    private static function get_course_id() {
        global $PAGE;

        if (!empty($PAGE->course->id) &&
            (int)$PAGE->course->id !== (int)SITEID
        ) {
            $cid = (int)$PAGE->course->id;
            return $cid;
        }

        return null;
    }

    /**
     * Get CMID.
     *
     * @return int|null
     */
    private static function get_cmid() {
        global $PAGE;

        if (!empty($PAGE->cm->id)) {
            return (int)$PAGE->cm->id;
        }

        return null;
    }

    /**
     * Get module type.
     *
     * @param int $cmid
     * @return string|null
     */
    private static function get_module_type($cmid) {
        global $PAGE, $DB;

        // Fast path.
        if (!empty($PAGE->cm->modname)) {
            return $PAGE->cm->modname;
        }

        static $modulecache = [];

        if (isset($modulecache[$cmid])) {
            return $modulecache[$cmid];
        }

        try {

            $module = $DB->get_record(
                'course_modules',
                ['id' => $cmid],
                'module'
            );

            if (!$module) {
                return null;
            }

            $modulename = $DB->get_field(
                'modules',
                'name',
                ['id' => $module->module]
            );

            $modulecache[$cmid] = $modulename ?: null;

            return $modulecache[$cmid];

        } catch (\Throwable $e) {

            debugging(
                'PREVENTCOPY: Module lookup error - ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }

        return null;
    }

    /**
     * Debug logger.
     *
     * @param string $message
     */
    private static function log($message) {

        if (debugging('', DEBUG_DEVELOPER)) {
            debugging('PREVENTCOPY: ' . $message, DEBUG_DEVELOPER);
        }
    }
}
