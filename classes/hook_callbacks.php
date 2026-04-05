<?php
namespace local_preventcopy;

use core\check\performance\debugging;
use core\hook\output\before_standard_footer_html_generation;

class hook_callbacks {

    /**
     * Hook callback for before_standard_footer_html_generation
     * Fires before HTTP headers are sent to browser
     */
    public static function before_standard_footer_html_generation(before_standard_footer_html_generation $hook) {
        global $USER, $PAGE;
        
        debugging('PREVENTCOPY: Hook triggered');
        
        try {
            // Skip for admin users
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

            // Get user roles
            $roles = get_user_roles($PAGE->context, $USER->id);
            debugging('PREVENTCOPY: User roles: ' . print_r($roles, true)); 
            $isstudent = false;
            $isteacher = false;

            foreach ($roles as $role) {
                if ($role->shortname === 'student') {
                    debugging('PREVENTCOPY: User has student role');
                    $isstudent = true;
                } else if ($role->shortname !== 'teacher') {
                    debugging('PREVENTCOPY: User has teacher role');
                    $isteacher = true;
                }
            }

            // Check role permissions
            $studentrole_enabled = get_config('local_preventcopy', 'studentrole');
            $teacherrole_enabled = get_config('local_preventcopy', 'nonstudentrole');

            debugging('PREVENTCOPY: Student=' . ($isstudent ? 'YES' : 'NO') . ' Teacher=' . ($isteacher ? 'YES' : 'NO'));

            $allowinject = ($studentrole_enabled && $isstudent) || ($teacherrole_enabled && $isteacher);

            if (!$allowinject) {
                debugging('PREVENTCOPY: Skipped - role not enabled');
                return;
            }

            debugging('PREVENTCOPY: ✓ INJECTING JS');
            $jsfragment = get_config('local_preventcopy', 'preventcopyjs');
            debugging('PREVENTCOPY: Config JS: ' . $jsfragment);
            $PAGE->requires->js_call_amd('local_preventcopy/inject', 'init', [$jsfragment]);
            debugging('PREVENTCOPY: ✓ Injection complete');

        } catch (\Exception $e) {
            error_log('PREVENTCOPY: ERROR - ' . $e->getMessage());
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
        debugging('PREVENTCOPY: Checking URL against list of pages' . print_r($arraylistofpages));
        $path = parse_url($pageurl, PHP_URL_PATH);
        foreach ($arraylistofpages as $line) {
            debugging('PREVENTCOPY: Checking if URL: '. $path. ' contains: ' . $line );
            if (strpos($pageurl, $line) !== false) {
                return true;
            }
        }
        return false;
    }

}
