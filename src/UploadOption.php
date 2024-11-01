<?php

namespace TinyWeb\Ultimate410;

final class UploadOption extends AbstractOption
{
    const ID = 'ultimate_410_upload';
    /**
     * @var CustomTable
     */
    private $customTable;

    /**
     * UploadField constructor.
     *
     * @param OptionsPage $page
     * @param UploadSection $uploadSection
     * @param CustomTable $customTable
     * @param string $pluginPath
     */
    public function __construct(OptionsPage $page, UploadSection $uploadSection, CustomTable $customTable, $pluginPath)
    {
        $this->customTable = $customTable;
        $this->title       = __('Batch add 410 URLs', 'ultimate-410');
        $this->args        = [
            'label'           => __('Upload CSV', 'ultimate-410'),
            'delimiter_label' => __('CSV delimiter', 'ultimate-410'),
            'column_label'    => __('CSV column that holds URL', 'ultimate-410'),
            'header_label'    => __('This CSV has column headers.', 'ultimate-410'),
        ];
        add_filter('pre_update_option_'.self::ID, [$this, 'save'], 10, 2);
        add_action('wp_ajax_'.self::ID.'_prepare', [$this, 'parseCsv']);
        parent::__construct($page, $uploadSection, $pluginPath);
    }

    public function callback()
    {
        wp_enqueue_script('wp-util');
        $this->includeView();
    }

    public function parseCsv()
    {
        check_ajax_referer(self::ID.'_prepare');

        $file = reset($_FILES);
        if (empty($file['tmp_name'])) {
            wp_send_json_error(['error_message' => __('Can\'t process upload.', 'ultimate-410')], 400);
        }
        $delimiters = [
            ';'  => 0,
            ','  => 0,
            "\t" => 0,
            '|'  => 0,
        ];

        $f         = fopen($file['tmp_name'], 'r');
        $firstLine = trim(fgets($f));
        fclose($f);
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }
        $delimiter = array_search(max($delimiters), $delimiters);
        $columns   = explode($delimiter, $firstLine);

        wp_send_json_success([
            'delimiter' => $delimiter,
            'columns'   => $_POST['headers'] === 'true' ? $columns : array_map(function ($column) {
                /** Translators: %s column count */
                return sprintf(__('Column %s', 'ultimate-410'), ($column + 1));
            }, array_keys($columns)),
        ]);
    }


    public function sanitize($value, $option, $original)
    {
        if (! is_null($value)) {
            add_settings_error(static::ID, 'invalid-upload', __('Could not process upload.', 'ultimate-410'));

            return $value;
        }
        $delimiterKey = static::ID.'_delimiter';
        $columnKey    = static::ID.'_column';
        if (! array_key_exists(static::ID, $_FILES)) {
            add_settings_error(static::ID, 'invalid-upload', __('Could not process upload. Missing CSV file.', 'ultimate-410'));

            return $value;
        }

        if (! array_key_exists($delimiterKey, $_POST)) {
            add_settings_error(static::ID, 'invalid-upload', __('Could not process upload. Missing delimiter.', 'ultimate-410'));

            return $value;
        }

        if (! array_key_exists($columnKey, $_POST)) {
            add_settings_error(static::ID, 'invalid-upload', __('Could not process upload. Missing column.', 'ultimate-410'));

            return $value;
        }

        $delimiter = substr(trim(sanitize_text_field($_POST[$delimiterKey])), 0, 1);
        $column    = (int)$_POST[$columnKey];
        $file      = $_FILES[static::ID];
        $data      = [];

        $f        = fopen($file['tmp_name'], 'rb');
        $rowCount = 0;
        $value    = '';
        while (($row = fgetcsv($f, 0, $delimiter)) !== false) {
            if ($rowCount === 0) {
                // trim BOM on first line if there is one.
                $value = ltrim($value, "\xEF\xBB\xBF");
                $rowCount++;
            }

            $data[] = Plugin::sanitize($row[$column]);
        }
        if (!empty($_POST[static::ID . '_headers'])) {
            array_shift($data);
        }
        fclose($f);

        // remove all remaining fq URLs and empty values.
        $data = array_filter(array_filter($data, function ($value) {
            return ! preg_match('|^https?://|i', trim($value));
        }));

        return $data;
    }

    public function save($value, $oldValue)
    {
        $message = __('No new URLs added.', 'ultimate-410');
        if (empty($value)) {
            add_settings_error(static::ID, 'no-records', $message, 'warning');

            return $oldValue;
        }

        $inserted = $this->customTable->insertMulitple($value);
        if (! $inserted) {
            add_settings_error($this->getId(), 'not-saved', $message, 'warning');

            return $oldValue;
        }

        add_settings_error($this->getId(), 'success', sprintf(
            _n('Added %d URL.', 'Added %d URLs', $inserted, 'ultimate-410'), $inserted
        ), 'success');

        return $oldValue;
    }
}
