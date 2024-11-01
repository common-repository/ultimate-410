<?php
/**
 * @var array $args
 * @var UrlTable $entries
 */

use TinyWeb\Ultimate410\UrlTable;

use function TinyWeb\Ultimate410\do_settings_section;

?>
<div class="wrap">
    <h1><?= $args['title']; ?></h1>

    <?php if (count($args['forms']) > 0) : ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach ($args['forms'] as $id => $form) : ?>
                <a
                    href="?page=<?= $args['id']; ?>&tab=<?= $id; ?>"
                    class="nav-tab <?= $args['active_tab'] === $id ? 'nav-tab-active' : ''; ?>">
                    <?= $form['title']; ?>
                </a>
            <?php endforeach; ?>
        </h2>
        <?php if (! empty($args['active_tab'])) : ?>
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php
                do_settings_section($args['id'], $args['active_tab']);
                settings_fields($args['active_tab']);
                ?>
                <div style="padding-left: 220px;">
                    <?php submit_button($args['forms'][$args['active_tab']]['submit'], 'primary large', 'submit', false); ?>
                </div>
                <script>
                    (function () {
                        var submit = document.getElementById('submit');
                        submit.disabled = true;
                    })();
                </script>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <?php $entries->display(); ?>
    <?php if ($entries->has_items()) : ?>
        <script>
            (function () {
                const deleted = row => {
                    row.style.backgroundColor = 'rgba(30,138,55,.3)';
                    setTimeout(function () {
                        row.parentNode.removeChild(row);
                    }, 200);
                };
                const xhrOnReadyStateChange = (xhr, rows) => {
                    xhr.onreadystatechange = event => {
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
                            rows.forEach(deleted);
                            setTimeout(() => {
                                if (document.getElementById('the-list').querySelector('tr') === null) {
                                    window.location.reload();
                                }
                            }, 250);
                        }
                    }
                }
                const deletes = document.querySelectorAll('.delete-410-entry');
                for (const deleteForm of deletes) {
                    deleteForm.addEventListener('submit', function (event) {
                        event.preventDefault();
                        const xhr = new XMLHttpRequest(),
                            row = this.closest('tr');
                        xhr.open('POST', window.ajaxurl);
                        xhrOnReadyStateChange(xhr, [row]);
                        xhr.send(new FormData(this));
                    });
                }

                document.getElementById('ultimate-410-bulk-delete').addEventListener('submit', event => {
                    event.preventDefault();

                    const toDelete = [],
                        rows = [],
                        formData = new FormData(event.target);

                    for (const cb of document.querySelectorAll('[name="delete_ultimate_410_rules[]"]:checked')) {
                        const row = cb.closest('tr');
                        rows.push(row);
                        formData.append(formData.get('action') + '[]', row.querySelector('.delete-410-entry [name="delete_410_entry[]"]').value);
                    }
                    if (!formData.get(formData.get('action') + '[]').length) {
                        return;
                    }

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', window.ajaxurl);
                    xhrOnReadyStateChange(xhr, rows);
                    xhr.send(formData);
                });

                document.getElementById('ultimate-410-delete-all').addEventListener('submit', event => {
                    event.preventDefault();

                    if (!window.confirm('<?php esc_html_e('Delete all rules? This action is not reversible.', 'ultimate-410'); ?>')) {
                        return false;
                    }
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', window.ajaxurl);
                    xhrOnReadyStateChange(xhr, document.getElementById('the-list').querySelectorAll('tr'));
                    xhr.send(new FormData(event.target));
                });
            })();
        </script>
    <?php endif; ?>
</div>
