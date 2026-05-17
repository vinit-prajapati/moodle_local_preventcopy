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
 * Library file for the Prevent Copy plugin.
 *
 * @package   local_preventcopy
 * @copyright 2025 Vinit Prajapati <vinit4ce@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Vinit Prajapati
 */



/**
 * Settings definition
 *
 * @package     local_preventcopy
 * @copyright   2025 Vinit Prajapati <vinit4ce@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Vinit Prajapati
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_preventcopy', get_string('preventcopy', 'local_preventcopy'));

    // Allow all on following pages.
    $settings->add(
        new admin_setting_configtextarea(
            'local_preventcopy/preventcopyjs',
            get_string('preventcopyjs', 'local_preventcopy'),
            get_string('preventcopyjsdesc', 'local_preventcopy'),
            "
            <script>
            document.addEventListener('contextmenu', function (e) { e.preventDefault(); return false; });
            document.addEventListener('copy', function (e) { e.preventDefault(); return false; });
            document.addEventListener('paste', function (e) { e.preventDefault(); return false; });
            document.addEventListener('cut', function (e) { e.preventDefault(); return false; });
            document.addEventListener('selectstart', function (e) { e.preventDefault(); return false; });
            document.addEventListener('selectall', function (e) { e.preventDefault(); return false; });
            </script>
            "
        )
    );

    /////////////////////////////////////////////////////////////////////////////////////////////

    // Course settings.
    $settings->add(
        new admin_setting_heading(
            'local_preventcopy/courseheading',
            'Course Restrictions',
            'Configure protection for specific courses or course categories.'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/categoryids',
            'Course Category IDs',
            'Enter Course Category IDs separated by commas. Example: 1,3,7. Use * to protect all categories.',
            ''
        )
    );


    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/courseids',
            'Course IDs',
            'Enter Course IDs separated by commas. Example: 2,5,10. Use * to protect all courses.',
            ''
        )
    );

    // Activity settings.
    $settings->add(
        new admin_setting_heading(
            'local_preventcopy/activityheading',
            'Activity Restrictions',
            'Configure protection for Moodle activities. Use Course Module IDs (CMIDs). Use * to block all instances.'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/quizcmids',
            'Quiz',
            'Enter Quiz CMIDs separated by commas. Example: 12,15,20. Use * to block all quizzes.',
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/assigncmids',
            'Assignment',
            'Enter Assignment CMIDs separated by commas. Example: 8,11. Use * to block all assignments.',
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/forumcmids',
            'Forum',
            'Enter Forum CMIDs separated by commas. Example: 21,25. Use * to block all forums.',
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/lessoncmids',
            'Lesson',
            'Enter Lesson CMIDs separated by commas. Example: 21,25. Use * to block all lessons.',
            ''
        )
    );

    // Resource settings.
    $settings->add(
        new admin_setting_heading(
            'local_preventcopy/resourceheading',
            'Resource Restrictions',
            'Configure protection for Moodle resources. Use Course Module IDs (CMIDs). Use * to block all instances.'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/pagecmids',
            'Page',
            'Enter Page CMIDs separated by commas. Example: 30,35. Use * to block all pages.',
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/bookcmids',
            'Book',
            'Enter Book CMIDs separated by commas. Example: 40,45. Use * to block all books.',
            ''
        )
    );

    // Other settings.
    $settings->add(
        new admin_setting_heading(
            'local_preventcopy/otherheading',
            'Other Restrictions',
            'Configure common protection rules.'
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_preventcopy/commoncmids',
            'Common Module ID',
            'Enter CMIDs separated by commas to apply protection regardless of activity/resource type. Example: 50,55,60. Use * to block all modules.',
            ''
        )
    );

    /////////////////////////////////////////////////////////////////////////////////////////////

    // Disable right click for student.
    $settings->add(
        new admin_setting_configcheckbox(
            'local_preventcopy/studentrole',
            get_string('studentrole', 'local_preventcopy'),
            get_string('studentroledesc', 'local_preventcopy'),
            true
        )
    );

    // Disable right click for non student i.e. teacher,manager etc!
    $settings->add(
        new admin_setting_configcheckbox(
            'local_preventcopy/nonstudentrole',
            get_string('nonstudentrole', 'local_preventcopy'),
            get_string('nonstudentroledesc', 'local_preventcopy'),
            false
        )
    );

    $ADMIN->add('localplugins', $settings);
}
