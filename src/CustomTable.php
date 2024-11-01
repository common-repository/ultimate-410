<?php

namespace TinyWeb\Ultimate410;

use wpdb;
use Exception;

final class CustomTable
{
    const NAME = 'ultimate_410';
    const VERSION = '4';

    /**
     * @var wpdb
     */
    protected $db;
    /**
     * @var string
     */
    private $tableName;

    public function __construct()
    {
        $this->db        = $GLOBALS['wpdb'];
        $this->tableName = $this->db->get_blog_prefix().static::NAME;
    }

    public function create()
    {
        if (! $this->needsUpdate()) {
            return;
        }
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $errors = array_filter(dbDelta($this->getSchema()), function ($query) {
            return strpos($query, 'database error') !== false;
        });
        if (! empty($errors)) {
            throw new Exception(implode('\n', $errors));
        }
        $this->saveVersion();
    }

    public function tableExists()
    {
        return ! empty($this->db->query($this->db->prepare('SHOW TABLES LIKE %s', $this->tableName)));
    }

    protected function needsUpdate()
    {
        return ! $this->tableExists() || static::VERSION !== get_option(static::NAME.'_db_version');
    }

    /** @noinspection SqlNoDataSourceInspection */
    public function getSchema()
    {
        return "CREATE TABLE {$this->tableName} (
            id INT(11) AUTO_INCREMENT,
            request VARCHAR(512) NOT NULL,
            regex TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY request (request(186))
        ) {$this->db->get_charset_collate()};";
    }

    protected function saveVersion()
    {
        return update_option(static::NAME.'_db_version', static::VERSION);
    }

    public function getAllRules()
    {
        return $this->db->get_results("SELECT * from {$this->tableName};");
    }

    public function insert($value, $regex = false)
    {
        return $this->db->query($this->db->prepare("INSERT IGNORE INTO {$this->tableName} (request, regex) VALUES ('%s', %d);", $value, $regex));
    }

    public function insertMulitple($data)
    {
        return $this->db->query(
            "INSERT IGNORE INTO {$this->tableName} (request) VALUES " . implode(
                ',',
                array_map(function ($value) {
                    return sprintf('("%s")', esc_sql($value));
                }, $data)
            )
        );
    }

    public function deleteMultiple($entries)
    {
        return $this->db->query(
            "DELETE FROM {$this->tableName} WHERE request in ( " . implode(
                ',',
                array_map(function ($entry) {
                    return sprintf('"%s"', esc_sql(Plugin::sanitize($entry)));
                }, $entries)
            ) . ');'
        );
    }

    public function ajaxDeleteEntries()
    {
        $action = sanitize_text_field($_POST['action']);
        check_ajax_referer($action);

        if ($this->deleteMultiple(array_map('wp_unslash', $_POST[$action]))) {
            wp_send_json_success();
        }

        wp_send_json_error(null, 400);
    }

    public function ajaxDeleteAllEntries()
    {
        $action = sanitize_text_field($_POST['action']);
        check_ajax_referer($action);

        if ($this->deleteAll()) {
            wp_send_json_success();
        }

        wp_send_json_error(null, 400);
    }

    public function ajaxAddEntries()
    {
        $action = sanitize_text_field($_POST['action']);
        check_ajax_referer($action);

        $entries = $_POST[$action];

        if (!is_array($entries)) {
            $entries = [$entries];
        }

        array_walk($entries, function (&$value) {
            $value = Plugin::sanitize($value);
        });
        $added = $this->insertMulitple($entries);

        if ($added) {
            wp_send_json_success();
        }

        wp_send_json_error(null, 400);
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE {$this->tableName};");
        delete_option(static::NAME . '_db_version');
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getAllRulesDisplay($limit = 0, $offset = 0)
    {
        $limitString = '';
        if ($limit) {
            $limitString = sprintf('LIMIT %d', $limit);
            if ($offset) {
                $limitString .= sprintf(', %d', $offset);
            }
        }

        return [
            'total' => $this->db->get_var("SELECT COUNT(*) FROM {$this->tableName};"),
            'rules' => $this->db->get_results("SELECT * FROM {$this->tableName} {$limitString};"),
        ];
    }

    private function deleteAll()
    {
        /** @noinspection SqlWithoutWhere -- we want to delete all entries */
        return $this->db->query("DELETE FROM {$this->tableName};" );
    }
}
