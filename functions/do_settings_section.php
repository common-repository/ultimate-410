<?php

namespace TinyWeb\Ultimate410;

function do_settings_section($page, $currentSection)
{
    global $wp_settings_sections, $wp_settings_fields;

    if (! isset($wp_settings_sections[$page])) {
        return;
    }

    foreach ((array) $wp_settings_sections[$page] as $section) {
        if ($section['id'] !== $currentSection) {
            continue;
        }
        if ($section['title']) {
            echo "<h2>{$section['title']}</h2>\n";
        }

        if ($section['callback']) {
            call_user_func($section['callback'], $section);
        }

        if (! isset($wp_settings_fields) || ! isset($wp_settings_fields[$page]) || ! isset($wp_settings_fields[$page][$section['id']])) {
            continue;
        }
        echo '<table class="form-table" role="presentation">';
        do_settings_fields($page, $section['id']);
        echo '</table>';
    }
}
