<?php
/**
 * @var array $args
 */
?>

<div style="vertical-align:middle;">
    <div style="background: #e5e5e5; color: #555; border-bottom-left-radius: 4px; border-top-left-radius: 4px; height: 100%; float: left; padding: 0 8px; line-height:  2; min-height: calc(30px - 2px); border: 1px solid #7e8993; margin-right: -1px;"><?= $args['blogurl']; ?></div>

    <input type="text" class="is-regex-showing" name="<?= $args['id']; ?>_regex_delimiter" maxlength="1" style="border-radius: 0; width: 1.3em; padding-left: 2px; padding-right: 2px; border-left-width: 0; margin-right: -1px; text-align: center; display: none; float:left;" value="~">

    <input style="border-left-width: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; display: block; float:left;" type="text" name="<?= $args['id']; ?>" id="<?= $args['id']; ?>">

    <input class="is-regex-showing" name="<?= $args['id']; ?>_regex_delimiter" type="text" disabled maxlength="1" style="display: none; float:left; border-radius: 0; border: 1px solid #7e8993; width: 1.3em; padding-left: 2px; padding-right: 2px; margin-left: -4px; text-align: center; background-color: #fff; color: #32373c; border-right-width: 0;" value="~">

    <input class="is-regex-showing" style="display: none; float:left; width: 2.5em; border-bottom-left-radius: 0; border-top-left-radius: 0; margin-left: -1px;" type="text" name="<?= $args['id']; ?>_regex_modifier" maxlength="1" value="i">

    <p style="clear: both;">
        <label>
            <input type="checkbox" name="<?= $args['id']; ?>_regex" id="<?= $args['id']; ?>_regex">
            <?= __('Parse this URL as regex', 'ultimate-410'); ?>
        </label>
    </p>
</div>
<script>
    (function () {
        var input = document.getElementById('<?= $args['id']; ?>'),
            regex = document.getElementById('<?= $args['id']; ?>_regex'),
            toHide = document.querySelectorAll('.is-regex-showing'),
            delimiter = document.querySelectorAll('[name="<?= $args['id']; ?>_regex_delimiter"]');

        regex.addEventListener('input', function () {
            var on = this.checked;
            for (var i = 0; i < toHide.length; i++) {
                toHide[i].style.display = on ? 'block' : 'none';
            }
        });

        delimiter[0].addEventListener('keyup', function () {
            delimiter[1].value = this.value;
        });

        input.addEventListener('input', function () {
            document.getElementById('submit').disabled = !this.value.trim().length;
        });
    })();
</script>
