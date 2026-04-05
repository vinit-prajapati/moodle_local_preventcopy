<?php

defined('MOODLE_INTERNAL') || die();


$callbacks = [
    [
        'hook' => \core\hook\output\before_standard_footer_html_generation::class,
        'callback' => [\local_preventcopy\hook_callbacks::class, 'before_standard_footer_html_generation'],
    ],
];

