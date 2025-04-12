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
 * @package     local_preventcopy
 * @copyright   2025 Vinit Prajapati <vinit4ce@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Vinit Prajapati
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
            "<script>
                document.addEventListener('contextmenu', function (e){e.preventDefault();});
                document.addEventListener('copy', function (e) {e.preventDefault();});       
                document.addEventListener('paste', function (e) {e.preventDefault();});  
            </script>"
        )
    );

    // Allow all on following pages.
    $settings->add(
        new admin_setting_configtextarea(
            'local_preventcopy/listofpages',
            get_string('listofpages', 'local_preventcopy'),
            get_string('listofpagesdesc', 'local_preventcopy'),
            ''
        )
    );

    // Disable right click for student
    $settings->add(
        new admin_setting_configcheckbox(
            'local_preventcopy/studentrole',
            get_string('studentrole', 'local_preventcopy'),
            get_string('studentroledesc', 'local_preventcopy'),
            true
        )
    );

    // Disable right click for non student i.e. teacher,manager etc
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