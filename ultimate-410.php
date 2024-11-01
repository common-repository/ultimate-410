<?php
/**
 * Plugin Name: Ultimate 410
 * Description: Ultimate 410 HTTP Status Code plugin.
 * Version: 1.1.7
 * Author: tinyweb, 7iebenschlaefer, alpipego
 * Author URI: https://tinyweb.com/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: ultimate-410
 */

use TinyWeb\Ultimate410\AdminPlugin;
use TinyWeb\Ultimate410\CustomTable;
use TinyWeb\Ultimate410\InputOption;
use TinyWeb\Ultimate410\InputSection;
use TinyWeb\Ultimate410\OptionsPage;
use TinyWeb\Ultimate410\Plugin;
use TinyWeb\Ultimate410\UploadOption;
use TinyWeb\Ultimate410\UploadSection;
use TinyWeb\Ultimate410\UrlTable;

$autoloader = file_exists(__DIR__.'/vendor/autoload.php')
    ? __DIR__.'/vendor/autoload.php'
    : __DIR__.'/lib/autoload.php';

require_once $autoloader;

$customTable = new CustomTable();

add_action('wp_ajax_delete_410_entry', [$customTable, 'ajaxDeleteEntries']);
add_action('wp_ajax_delete_410_all', [$customTable, 'ajaxDeleteAllEntries']);
add_action('wp_ajax_add_410_entry', [$customTable, 'ajaxAddEntries']);

if (is_admin()) {
    $pluginPath = __DIR__;

    new AdminPlugin($customTable);

    // register page.
    $page = new OptionsPage($pluginPath);

    add_action('current_screen', function (WP_Screen $screen) use ($customTable, $page) {
        if ($screen->base !== 'settings_page_'. $page->getId()) {
            return;
        }
        $page->setTable(new UrlTable($customTable));
    });

    // register CSV upload.
    $uploadSection = new UploadSection($page, $pluginPath);
    $uploadField   = new UploadOption($page, $uploadSection, $customTable, $pluginPath);

    // register manual input.
    $inputSection = new InputSection($page, $pluginPath);
    $regexField   = new InputOption($page, $inputSection, $customTable, $pluginPath);

    add_action('plugins_loaded', [$customTable, 'create']);
}

$plugin = new Plugin($customTable);
