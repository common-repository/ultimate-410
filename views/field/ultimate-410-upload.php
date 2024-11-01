<?php
/**
 * @var array $args
 */
?>
<input type="file" accept="text/csv" name="<?= $args['id']; ?>" id="<?= $args['id']; ?>">
<label style="display: block">
    <input type="checkbox" name="<?= $args['id']; ?>_headers" id="<?= $args['id']; ?>_headers" checked>
    <?= $args['header_label']; ?>
</label>

<div id="<?= $args['id']; ?>_additional" style="padding-top: 20px;padding-bottom: 20px;">
    <label style="display: inline-block; margin-right: 20px;">
        <?= $args['delimiter_label']; ?><br>
        <input type="text" name="<?= $args['id']; ?>_delimiter" id="<?= $args['id']; ?>_delimiter" maxlength="1" size="1" style="width: 100%;" required>
    </label>
    <label style="display: inline-block">
        <?= $args['column_label']; ?><br>
        <select name="<?= $args['id']; ?>_column" id="<?= $args['id']; ?>_column" style="width: 100%;" required>
        </select>
    </label>
</div>

<script>
    (function () {
        var uploadField = document.getElementById("<?= $args['id']; ?>"),
            delimiter = document.getElementById('<?= $args['id']; ?>_delimiter'),
            column = document.getElementById('<?= $args['id']; ?>_column'),
            headers = document.getElementById('<?= $args['id']; ?>_headers'),
            wrapper = document.getElementById('<?= $args['id']; ?>_additional');

        wrapper.style.display = 'none';

        $parseCsv = function () {
            var xhr = new XMLHttpRequest(),
                formData = new FormData();
            formData.append('action', '<?= $args['id']; ?>_prepare');
            formData.append('file', uploadField.files[0]);
            formData.append('headers', headers.checked);
            formData.append('_wpnonce', '<?= wp_create_nonce($args['id'].'_prepare'); ?>');
            xhr.open('POST', window.ajaxurl);
            xhr.onreadystatechange = function (event) {
                try {
                    var readyState = event.target.readyState,
                        status = event.target.status,
                        response = JSON.parse(event.target.response);
                } catch (e) {
                    return;
                }

                if (status >= 400 || (typeof response.success !== 'undefined' && !response.success)) {
                    console.log(response);
                    console.log('show error message');
                }
                if (readyState === 4 && status === 200 && (typeof response.success !== 'undefined' && response.success)) {
                    column.innerHTML = '';
                    column.disabled = false;
                    for (var i in response.data.columns) {
                        var option = document.createElement('option');
                        option.value = i;
                        option.innerText = response.data.columns[i];
                        if (response.data.columns[i].toLowerCase() === 'url') {
                            option.selected = true;
                        }
                        column.appendChild(option);
                    }

                    delimiter.value = response.data.delimiter;

                    document.getElementById('submit').disabled = false;

                    if (response.data.columns.length === 1) {
                        column.firstChild.selected = true;
                        wrapper.style.display = 'none';
                        return;
                    }

                    wrapper.style.display = 'block';
                }
            };
            xhr.send(formData);
        };
        uploadField.addEventListener('change', $parseCsv);
        headers.addEventListener('change', function () {
            if (uploadField.files.length) {
                $parseCsv();
            }
        });
    })();
</script>
