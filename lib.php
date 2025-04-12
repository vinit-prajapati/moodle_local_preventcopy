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



defined('MOODLE_INTERNAL') || die();

/**
 * Inject JavaScript at the start of the body tag.
 */
function local_preventcopy_before_standard_html_head() {
    global $PAGE, $USER;

    if (is_siteadmin()) {
        return; // Skip if the user is an admin.
    }

    // Page URL
    if(!should_prevent_copy($PAGE->url->out())){
        return; // Skip if the page URL is not in the list.
    }
  
    // Get the current context (e.g., course or activity context).
    $context = $PAGE->context; 

    // Get the roles of the logged-in user in the current context.
    $roles = get_user_roles( $context, $USER->id);

    // Check if the user has a specific role (e.g., student or teacher).
    $isstudent = false;
    $isteacher = false;

    foreach ($roles as $role) {
        if ($role->archetype === 'student') {
            $isstudent = true;
        } else {
            $isteacher = true;
        }
    }

    // pageurl contains /mod/page or /mod/lesson
    if (    (get_config('local_preventcopy', 'studentrole') == 1  && ($isstudent==1)
                || 
            (get_config('local_preventcopy', 'nonstudentrole') == 1) && ($isteacher==1))
        ) {        
        $PAGE->requires->js_call_amd('local_preventcopy/inject', 'init', [get_config('local_preventcopy', 'preventcopyjs')]);
    }
}


/**
 * Check if the page URL should prevent copy.
 *
 * @param string $pageurl The page URL to check.
 * @return bool True if the page URL should prevent copy, false otherwise.
 */
function should_prevent_copy($pageurl){
    $listofpages = get_config('local_preventcopy', 'listofpages');
    $arraylistofpages = preg_split("/\r\n|\n|\r/", $listofpages);

    foreach ($arraylistofpages as $line) {
        if (strpos($pageurl, $line) !== false) {
            return true;
        }
    }
    return false;
}



