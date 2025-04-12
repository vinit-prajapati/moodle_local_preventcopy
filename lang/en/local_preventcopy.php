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


$string['pluginname'] = 'Prevent Copy';
$string['preventcopy'] = 'Prevent Copy';
$string['preventcopy_desc'] = 'This plugin prevents copying for selected roles.';

$string['studentrole'] = 'Student role';
$string['studentroledesc'] = 'Disable right-click, copy, and paste for students on course pages.';

$string['nonstudentrole'] = 'Non-student role';
$string['nonstudentroledesc'] = 'Disable right-click, copy, and paste for non-student roles on course pages.';

$string['preventcopyjs'] = 'Prevent Copy JS Fragment';
$string['preventcopyjsdesc'] = 'Enter JavaScript to prevent right-click, copy/paste, text selection, developer tools, etc.';

$string['listofpages'] = 'List of pages to <b>prevent copy</b>';
$string['listofpagesdesc'] = 'List of pages to disable right-click, copy, and paste. Enter one URL pattern per line.<br> <b>Note:</b> Enter page URL patterns like: <ul><li>/mod/page</li><li>/mod/lesson</li></ul>';