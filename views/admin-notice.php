<?php
$innerDiv      = '<div class="single-410" style="margin: 12px 0;">';
$innerDivClose = '</div>';
?>
<div class="notice notice-warning is-dismissible">
    <?= $innerDiv; ?><?= implode($innerDivClose.$innerDiv, $messages); ?><?= $innerDivClose; ?>

    <script>
        (function () {
            var forms = document.getElementsByClassName('add-410-entry');
            for (var i = 0; i < forms.length; i++) {
                forms[i].addEventListener('submit', function (event) {
                    event.preventDefault();
                    var xhr = new XMLHttpRequest(),
                        row = this.closest('.single-410');

                    xhr.open('POST', window.ajaxurl);
                    xhr.onreadystatechange = function (event) {
                        var button = row.querySelector('button');
                        button.disabled = true;

                        try {
                            var readyState = event.target.readyState,
                                status = event.target.status,
                                response = JSON.parse(event.target.response);
                        } catch (e) {
                            return;
                        }

                        if (readyState < 4) {
                            return false;
                        }

                        var feedback = document.createElement('span');
                        feedback.style.verticalAlign = 'middle';

                        if (status >= 400 || (typeof response.success !== 'undefined' && !response.success)) {
                            feedback.innerHTML = '<?= __('Entry already exists.', 'ultimate-410'); ?> <span class="dashicons dashicons-no" style="color: red;"></span>';
                            button.parentNode.appendChild(feedback);
                        }

                        if (status === 200 && (typeof response.success !== 'undefined' && response.success)) {
                            feedback.innerHTML = 'OK <span class="dashicons dashicons-yes" title="%1$s" style="color: green;"></span>';
                            button.parentNode.appendChild(feedback);
                            button.parentNode.removeChild(button);
                        }
                    };
                    xhr.send(new FormData(this));
                })
            }
        })();
    </script>
</div>
