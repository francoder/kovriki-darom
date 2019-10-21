<?php if (!class_exists('nc_core')) { die; } ?>

<?= $this->include_view('modal', get_defined_vars()) ?>

<?php

/** @var nc_netshop_exchange_import $object */
/** @var string $file_name */
/** @var string $scope_name */
/** @var integer $phase */
/** @var integer $phases */
/** @var boolean $just_uploaded */
/** @var string $item_key */
/** @var string $new_subdivision_possible_name */
/** @var string $new_subdivision_possible_keyword */
/** @var boolean $has_goods */
/** @var array $components */
/** @var array $preview */
/** @var string $new_component_possible_name */
/** @var string $new_component_possible_keyword */
/** @var array $fields_keys */
/** @var array $fields_types */
/** @var boolean $is_component_exists */
/** @var integer $default_component_id */
/** @var boolean $is_subdivision_exists */
/** @var array $mapping */
/** @var integer $skip_rows */
/** @var boolean $show_skip_rows */
/** @var string $item_key_info */

$nc_core = nc_core::get_object();

?>

<h4>
    <?= NETCAT_MODULE_NETSHOP_EXCHANGE_PHASE; ?>
    <?= $phase; ?> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_PHASE_OUT_OF ?>
    <?= $phases; ?>&nbsp;&mdash; <?= $item_key_info; ?>
</h4>

<?php if ($just_uploaded) { ?>

    <div class="nc-alert nc--green">
        <i class="nc-icon-l nc--status-success"></i>
        <?= NETCAT_MODULE_NETSHOP_EXCHANGE_FILES_UPLOADED; ?>
    </div>

<?php } ?>

<input type="hidden" name="mapping[item_key]" value="<?= $item_key; ?>">
<input type="hidden" name="do" value="save-for-file">

<!-- ------------------------------------------------ ВЫБОР РАЗДЕЛА ------------------------------------------------ -->

<div style="display:<?= $object['format'] == nc_netshop_exchange_import::FORMAT_PRICE ? 'none' : null; ?>">

    <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECTING_SUBDIVISION; ?></h4>

    <?

    $subdivision_source = $object['format'] == nc_netshop_exchange_import::FORMAT_PRICE ? 'current' : 'new';

    ?>

    <label><input type="radio" name="mapping[subdivision_source]" value="current" <?= $subdivision_source == 'current' ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_SUBDIVISION_THAT_EXISTS; ?></label><br>
    <label><input type="radio" name="mapping[subdivision_source]" value="new" <?= $subdivision_source == 'new' ? 'checked' : null; ?>> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_CREATE_SUBDIVISION; ?></label>

    <div class="nc-margin-top-medium">
        <div class="nc-field">
            <label id="nc-netshop-exchange-subdivision-selection-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_PARENT_SUBDIVISION; ?></label><br>

            <?= $subdivisions; ?>

        </div>

        <div id="nc-netshop-exchange-subdivision-new" style="display: <?= $subdivision_source == 'current' ? 'none' : null; ?>">
            <div class="nc-field nc-field-type-string">
                <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_SUBDIVISION_NAME; ?>:</span>
                <input name="mapping[subdivision_name]" type="text" maxlength="255" size="50" value="<?= $new_subdivision_possible_name; ?>">
            </div>

            <div class="nc-field nc-field-type-string">
                <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_SUBDIVISION_KEYWORD; ?>:</span>
                <input name="mapping[subdivision_english_name]" type="text" value="<?= $new_subdivision_possible_keyword; ?>" size="50" class="input" data-type="transliterate" data-from="mapping[subdivision_name]" data-is-url="yes">
            </div>
        </div>
    </div>

</div>

<!-- ---------------------------------------------- ВЫБОР КОМПОНЕНТА ----------------------------------------------- -->

<?php if (!$has_goods) { ?>

    <input type="hidden" name="mapping[only_subdivision]" value="1">

<?php } else { ?>

    <div style="display:<?= $object['format'] == nc_netshop_exchange_import::FORMAT_PRICE ? 'none' : null; ?>">
        <br>

        <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECTING_COMPONENT; ?></h4>

        <label><input type="radio" name="mapping[component_source]" value="current"> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_COMPONENT_THAT_EXISTS; ?></label><br>
        <label><input type="radio" name="mapping[component_source]" value="new"> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_CREATE_COMPONENT_BASED_ON_ANOTHER; ?></label>

        <div class="nc-field nc-margin-top-medium">
            <label id="nc-netshop-exchange-component-selection-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_PARENT_COMPONENT; ?></label><br>

            <select name="mapping[component_id]">

                <?php $first = true; ?>
                <?php foreach ($components as $c_id => $c_data) {
                    $selected = $first ? ' selected' : null; ?>

                    <option value="<?= $c_id; ?>" <?= $selected; ?>><?= $c_data['name']; ?></option>

                    <?php $first = false; ?>
                <?php } ?>

            </select>
        </div>

        <div id="nc-netshop-exchange-component-new">
            <div class="nc-field nc-field-type-string" id="nc-netshop-exchange-component-name">
                <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_COMPONENT_NAME; ?>:</span>
                <input name="mapping[component_name]" type="text" maxlength="255" size="50" value="<?= NETCAT_MODULE_NETSHOP_EXCHANGE_GOOD; ?>: <?= $new_component_possible_name; ?>">
            </div>
            <div class="nc-field nc-field-type-string" id="nc-netshop-exchange-component-keyword">
                <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_COMPONENT_KEYWORD; ?>:</span>

                <input name="mapping[component_keyword]" type="text" maxlength="255" size="50" value="<?= $new_component_possible_keyword; ?>">
            </div>
        </div>
    </div>

    <!-- ------------------------------------------- СООТВЕТСТВИЕ ПОЛЕЙ -------------------------------------------- -->

    <?php $preview_rows_count = count($preview); ?>

    <h4 class="nc-margin-top-medium" style="display: inline-block; font-weight: bold;"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_MAPPING_FIELDS; ?></h4>

    <div style="display: <?= $show_skip_rows ? 'block' : 'none'; ?>">
        <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SKIP_FIRST; ?>
        <input type="number"
               name="mapping[skip_rows]"
               value="<?= $skip_rows; ?>"
               class="nc-netshop-exchange-input--narrow"
               min="0"
               max="10"
               step="1">
        <?= NETCAT_MODULE_NETSHOP_EXCHANGE_ROWS_IN_FILE; ?>.
    </div>

    <table class="nc-table nc--bordered nc--wide">
        <tr>
            <th class="nc--compact">#</th>
            <th class="nc--compact"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_IN_COMPONENT; ?></th>
            <th class="nc--nowrap"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIRST_STRING_IN_FILE; ?></th>

            <?php if ($preview_rows_count > 1) { ?>

                <th class="nc--nowrap"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SECOND_STRING_IN_FILE; ?></th>

            <?php } ?>

            <?php if ($preview_rows_count > 2) { ?>

                <th class="nc--nowrap"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_THIRD_STRING_IN_FILE; ?></th>

            <?php } ?>

        </tr>

        <?php for ($i = 0; $i < count($preview[0]); $i++) {
            $field_title = $preview[0][$i];
            $is_new_field = false;
            $new_field_name = $new_field_description = null;
            if ($object->can_create_new_field($field_title)) {
                $is_new_field = true;
                $new_field_data = $object->new_field_data_from_field_title($field_title);
                $new_field_name = $new_field_data['name'];
                $new_field_description = $new_field_data['description'];
            } ?>

            <tr>
                <td><?= ($i + 1); ?></td>
                <td class="nc--nowrap">
                    <select name="mapping[field_<?= $i; ?>]" class="nc-netshop-exchange-field-select" data-id="<?= $i; ?>" data-new="<?= (int)$is_new_field; ?>"></select>
                    <button type="button" class="nc-netshop-exchange-btn-edit-field" data-id="<?= $i; ?>"><i class="nc-icon nc--settings"></i></button>
                    <input type="hidden" name="mapping[field_<?= $i; ?>_key]" class="nc-netshop-exchange-new-field-input nc-netshop-exchange-new-field-input__key" value="<?= $fields_keys[$i]; ?>">
                    <input type="hidden" name="mapping[field_<?= $i; ?>_name]" class="nc-netshop-exchange-new-field-input nc-netshop-exchange-new-field-input__name" value="<?= $new_field_name; ?>" data-id="<?= $i; ?>">
                    <input type="hidden" name="mapping[field_<?= $i; ?>_description]" class="nc-netshop-exchange-new-field-input nc-netshop-exchange-new-field-input__description" value="<?= $new_field_description; ?>" data-id="<?= $i; ?>">
                    <input type="hidden" name="mapping[field_<?= $i; ?>_type]" class="nc-netshop-exchange-new-field-input nc-netshop-exchange-new-field-input__type" value="<?= NC_FIELDTYPE_STRING; ?>" data-id="<?= $i; ?>">
                    <input type="hidden" name="mapping[field_<?= $i; ?>_format]" class="nc-netshop-exchange-new-field-input nc-netshop-exchange-new-field-input__format" data-id="<?= $i; ?>">
                </td>

                <?php foreach ($preview as $j => $row) {
                    $style = $j == 0 ? "white-space: nowrap;" : "word-break: break-all;"; ?>

                    <td style="<?= $style; ?>"><?= $row[$i]; ?></td>

                <?php } ?>

            </tr>

        <?php } ?>

    </table>

    <!-- ----------------------------------------------- ВАРИАНТЫ -------------------------------------------------- -->

    <div class="nc-netshop-exchange-component-fields-selection" data-scope="variant" style="display: none;">
        <br>
        <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_VARIANTS_FIELDS; ?></h4>
        <label><input type="checkbox" name="variant[variants_source]" value="new" class="nc-netshop-exchange-component-fields-selection-toggler"> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SET_THIS_FIELDS; ?></label>

        <div class="nc-netshop-exchange-component-fields-selection-list" style="display: none;">
            <p class="nc-netshop-exchange-details"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SET_THIS_FIELDS_IN_THE_END; ?></p>

            <select name="variant[fields][]" class="nc-netshop-exchange-component-fields-select" multiple></select>
        </div>

        <br>
    </div>

    <!-- ------------------------------------------- ПОЛЯ ДЛЯ ПОИСКА ----------------------------------------------- -->

    <div class="nc-netshop-exchange-component-fields-selection" data-scope="search" style="display: none;">
        <br>
        <h4 class="nc-netshop-exchange-title"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SEARCH_FIELDS; ?></h4>
        <label><input type="checkbox" name="search[search_source]" value="new" class="nc-netshop-exchange-component-fields-selection-toggler"> <?= NETCAT_MODULE_NETSHOP_EXCHANGE_SET_THIS_FIELDS; ?></label>

        <div class="nc-netshop-exchange-component-fields-selection-list" style="display: none;">
            <p class="nc-netshop-exchange-details"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SET_THIS_FIELDS_IN_THE_END; ?></p>

            <select name="search[fields][]" class="nc-netshop-exchange-component-fields-select" multiple></select>
        </div>
    </div>

    <br>

    <!-- ------------------------------------------------ МОДАЛКА -------------------------------------------------- -->

    <div class="nc-netshop-exchange-modal">

        <div class="nc-netshop-exchange-modal-item" id="modal-field">
            <div class="nc-netshop-exchange-modal-header">
                <h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_EDIT_FIELD; ?></h2>
                <a class="nc-modal-dialog-header-close-button nc-netshop-exchange-modal-close"></a>
            </div>
            <div class="nc-netshop-exchange-modal-body">
                <div class="nc-field nc-field-type-string">
                    <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_NAME; ?>:</span>
                    <input name="modal_field_name" type="text" maxlength="255" size="50" value="" style="width: 100%;">
                    <span class="nc-netshop-exchange-field-help"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_NAME_EXAMPLE; ?></span>
                </div>
                <div class="nc-field nc-field-type-string">
                    <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_DESCRIPTION; ?>:</span>
                    <input name="modal_field_description" type="text" maxlength="255" size="50" value="" style="width: 100%;">
                    <span class="nc-netshop-exchange-field-help"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_DESCRIPTION_EXAMPLE; ?></span>
                </div>
                <div class="nc-field nc-field-type-select">
                    <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_TYPE; ?>:</span>
                    <select name="modal_field_type" style="width: 100%;">

                        <?php foreach ($fields_types as $id => $name) { ?>

                            <option value="<?= $id; ?>" <?= $id == 1 ? 'selected="selected"' : null; ?>><?= $name; ?></option>

                        <?php } ?>

                    </select>
                </div>
                <div class="nc-field nc-field-type-string nc-netshop-exchange-modal-body-field-format">
                    <span class="nc-field-caption"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_FORMAT; ?>:</span>
                    <input name="modal_field_format" type="text" maxlength="255" size="50" value="" style="width: 100%;">
                    <span class="nc-netshop-exchange-field-help"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_FORMAT_EXAMPLE; ?></span>
                </div>
            </div>
            <div class="nc-netshop-exchange-modal-footer">
                <button type="button" class="nc-btn nc--mini nc--blue nc-netshop-exchange-modal-save"><?= NETCAT_MODULE_NETSHOP_EXCHANGE_SAVE; ?></button>
            </div>
        </div>

        <div class="nc-netshop-exchange-modal-item nc-netshop-exchange-modal-item--centered" id="modal-fields-processing-progress">
            <div class="nc-netshop-exchange-modal-header">
                <h2><?= NETCAT_MODULE_NETSHOP_EXCHANGE_PLEASE_WAIT; ?></h2>
            </div>
            <div class="nc-netshop-exchange-modal-body">
                <div class="nc-netshop-exchange-modal-body-progress">
                    <div class="nc-netshop-exchange-modal-body-progress-text">0%</div>
                    <div class="nc-netshop-exchange-modal-body-progress-background"></div>
                </div>
            </div>
        </div>

    </div>

<?php } ?>

<script>
(function() {
    // ---------------------------------- ВЫБОР ПОЛЕЙ (КАК ТЕКУЩИХ, ТАК И НОВЫХ) ---------------------------------

    function componentFieldsRebuildFields() {
        $nc(document).find('.nc-netshop-exchange-component-fields-select').each(function() {
            var select = $nc(this);
            if (select.css('display') === 'none') {
                select.chosen('destroy');
            }
            // Ранее выбранные элементы select'а
            var alreadySelectedValues = [];
            select.find('option:selected').each(function() {
                alreadySelectedValues.push($nc(this).val());
            });
            // Сформируем и обновим значения select'a
            var selectHtml = [];
            var fields = getAllFieldsData();

            for (var i in fields) {
                var field = fields[i];
                var selected = alreadySelectedValues.indexOf(field[0]) > -1;
                selectHtml.push('<option value="' + field[0] + '"' + (selected ? 'selected' : '') + '>' + field[1] + ' [' + field[0] + ']' + '</option>');
            }
            selectHtml = selectHtml.join('');
            select.html(selectHtml);
            select.chosen({
                placeholder_text_multiple: '<?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_SELECT_PLACEHOLDER ?>',
                no_results_text: '<?= NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_NOT_FOUND ?>'
            });
        });
    }

    $nc(function() {
        function componentFieldsSelectionGetParentByScope(scope) {
            return $nc(document).find('.nc-netshop-exchange-component-fields-selection[data-scope="' + scope + '"]');
        }
        function componentFieldsSelectionGetScope(item) {
            return item.parents('.nc-netshop-exchange-component-fields-selection').data('scope');
        }
        $nc(document).on('change', '.nc-netshop-exchange-component-fields-selection-toggler', function() {
            var scope = componentFieldsSelectionGetScope($nc(this));
            var block = componentFieldsSelectionGetParentByScope(scope);

            componentFieldsRebuildFields();

            if ($nc(this).is(':checked')) {
                block.find('.nc-netshop-exchange-component-fields-selection-list').show();
            } else {
                block.find('.nc-netshop-exchange-component-fields-selection-list').hide();
            }
        });
    });

    // ------------------------------------------------ МОДАЛКА --------------------------------------------------

    function modalUpdateFieldFormat() {
        var block = $nc('.nc-netshop-exchange-modal-body-field-format');
        switch (parseInt($nc('select[name="modal_field_type"]').val())) {
            case <?= NC_FIELDTYPE_SELECT ?>:
            case <?= NC_FIELDTYPE_MULTISELECT ?>:
                block.show();
                break;

            default:
                block.hide();
        }
    }

    function modalProgressSet(current, total, callback) {
        var progress = parseInt((current / total) * 100);
        progress = progress + '%';
        $nc('.nc-netshop-exchange-modal-body-progress-text').text(progress);
        $nc('.nc-netshop-exchange-modal-body-progress-background').width(progress);
        if (callback) {
            setTimeout(function() {
                callback();
            }, 1);
        }
    }
    $nc(function() {
        $nc('.nc-netshop-exchange-btn-edit-field').click(function() {
            var id = $nc(this).data('id');

            var fieldName = $nc(this).siblings('.nc-netshop-exchange-new-field-input__name').val();
            var fieldDescription = $nc(this).siblings('.nc-netshop-exchange-new-field-input__description').val();
            var fieldType = $nc(this).siblings('.nc-netshop-exchange-new-field-input__type').val();
            var fieldFormat = $nc(this).siblings('.nc-netshop-exchange-new-field-input__format').val();

            $nc('input[name="modal_field_name"]').val(fieldName);
            $nc('input[name="modal_field_description"]').val(fieldDescription);
            $nc('select[name="modal_field_type"]').val(fieldType ? fieldType : 1);
            $nc('input[name="modal_field_format"]').val(fieldFormat);

            modalUpdateFieldFormat();

            $nc('.nc-netshop-exchange-modal').data('id', id);
            nc_netshop_exchange_modal_show('modal-field');
        });
        $nc('select[name="modal_field_type"]').change(function() {
            modalUpdateFieldFormat();
        });
        $nc('.nc-netshop-exchange-modal-save').click(function() {
            var id = $nc('.nc-netshop-exchange-modal').data('id');

            var fieldName = $nc('input[name="modal_field_name"]').val();
            var fieldDescription = $nc('input[name="modal_field_description"]').val();
            var fieldType = $nc('select[name="modal_field_type"]').val();
            var fieldFormat = $nc('input[name="modal_field_format"]').val();

            $nc('.nc-netshop-exchange-new-field-input__name[data-id="' + id + '"]').val(fieldName);
            $nc('.nc-netshop-exchange-new-field-input__description[data-id="' + id + '"]').val(fieldDescription);
            $nc('.nc-netshop-exchange-new-field-input__type[data-id="' + id + '"]').val(fieldType);
            $nc('.nc-netshop-exchange-new-field-input__format[data-id="' + id + '"]').val(fieldFormat);

            nc_netshop_exchange_modal_hide();

            componentFieldsRebuildFields();
        });
    });

    // #################################################################################################################
    // #                                                 ОБЩАЯ ЛОГИКА                                                  #
    // #################################################################################################################

    // Валидация формы маппинга перед отправкой
    window.nc_netshop_exchange_wizard_form_submit = function() {
        var form = $nc('#nc-netshop-exchange-form');

        if (!form.hasClass('validate')) {
            return true;
        }

        // Массив ошибок, если таковые будут
        var errors = [];

        // Данные для проверки на сервере
        var data = {
            nc_token: '<?= $nc_core->token->get(); ?>'
        };

        // Использование существующего раздела или создание нового?
        var subdivisionSource = $nc('input[name="mapping[subdivision_source]"]:checked').val();

        // Если раздел новый - проверим корректность данных
        if (subdivisionSource === 'new') {
            data.subdivision_check = true;

            // Данные нового раздела
            data.subdivision_base = $nc('select[name="mapping[subdivision_id]"]').val();
            data.subdivision_name = $nc('input[name="mapping[subdivision_name]"]').val();
            data.subdivision_keyword = $nc('input[name="mapping[subdivision_english_name]"]').val();
        }

        <?php if ($has_goods) { ?>

            // Использование существующего компонента или создание нового?
            var componentSource = $nc('input[name="mapping[component_source]"]:checked').val();

            // Если раздел компонент новый - проверим корректность данных
            if (componentSource === 'new') {
                data.component_check = true;

                // Проверим component на сервере
                data.component_base = $nc('select[name="mapping[component_id]"] option:selected').val();
                data.component_name = $nc('input[name="mapping[component_name]"]').val();
                data.component_keyword = $nc('input[name="mapping[component_keyword]"]').val();
                data.component_fields = [];
                data.component_lists = [];

                // Проверим поля
                $nc(document).find('.nc-netshop-exchange-field-select').each(function() {
                    var selectForField = $nc(this);

                    // Любое другое поле, кроме "Новое поле"
                    if (selectForField.val() !== '-1') {
                        return;
                    }

                    // Ключ поля
                    var fieldKey = selectForField.attr('name').replace('mapping[', '').replace(']', '');
                    var fieldIndex = parseInt(fieldKey.replace('field_', '')) + 1;

                    var parent = selectForField.parent().parent();

                    var fieldName = parent.find('input[name="mapping[' + fieldKey + '_name]"]').val();
                    var fieldDescription = parent.find('input[name="mapping[' + fieldKey + '_description]"]').val();
                    var fieldType = parseInt(parent.find('input[name="mapping[' + fieldKey + '_type]"]').val());
                    var fieldFormat = parent.find('input[name="mapping[' + fieldKey + '_format]"]').val();

                    var fieldErrorPrefix = '<?= NETCAT_MODULE_NETSHOP_EXCHANGE_NEW_FIELD; ?> №' + fieldIndex + ': ';

                    if (fieldName.length === 0) {
                        errors.push(fieldErrorPrefix + '<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_NAME); ?>');
                    } else {
                        // Проверим название поля
                        if (/[^a-zA-Z0-9_]/gi.test(fieldName)) {
                            errors.push(fieldErrorPrefix + '<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_WRONG_FORMAT, NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_NAME); ?>');
                        } else {
                            data.component_fields.push(fieldName);
                        }
                    }

                    // Список или множественный список
                    if (fieldType === <?= NC_FIELDTYPE_SELECT ?> || fieldType === <?= NC_FIELDTYPE_MULTISELECT ?>) {
                        if (fieldFormat.length === 0) {
                            errors.push(fieldErrorPrefix + '<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_FORMAT); ?>');
                        } else {
                            // Проверим формат, если выбран список или множественный список
                            data.component_lists.push(fieldFormat);
                        }
                    }

                    if (fieldDescription.length === 0) {
                        errors.push(fieldErrorPrefix + '<?= sprintf(NETCAT_MODULE_NETSHOP_EXCHANGE_ERROR_FIELD_IS_REQUIRED, NETCAT_MODULE_NETSHOP_EXCHANGE_FIELD_DESCRIPTION); ?>');
                    }
                });
            }

            // Проверим, выбраны ли обязательные поля
            var requiredFields = ['<?= $object->get_main_field_name(); ?>'];
            var requiredFieldsInfo = ['<?= $object->get_main_field_description(); ?> [<?= $object->get_main_field_name(); ?>]'];
            $nc(document).find('.nc-netshop-exchange-field-select').each(function() {
                var selectVal = parseInt($nc(this).val());
                var selectValName = $nc(this).find('option[value="' + selectVal + '"]').data('name');
                var requiredFieldId = requiredFields.indexOf(selectValName);
                if (requiredFieldId > -1) {
                    requiredFields.splice(requiredFieldId, 1);
                    requiredFieldsInfo.splice(requiredFieldId, 1);
                }
            });
            if (requiredFields.length) {
                var str = '<?= NETCAT_MODULE_NETSHOP_EXCHANGE_REQUIRED_FIELDS_NOT_MAPPED; ?>: ' + requiredFieldsInfo.join(', ') + '!';
                errors.push(str);
            }

            // Проверка полей
            $nc(document).find('.nc-netshop-exchange-component-fields-selection').each(function() {
                var scope = $nc(this).data('scope');
                var fieldsSelection = $nc(this);
                var fieldsSource = fieldsSelection.find('.nc-netshop-exchange-component-fields-selection-toggler:checked').val();
                if (fieldsSource !== 'new') {
                    return;
                }
                var selectFields = fieldsSelection.find('.nc-netshop-exchange-component-fields-select option:selected');
                if (typeof selectFields !== "undefined" && selectFields.length > 0) {
                    return;
                }
                switch (scope) {
                    case 'variant': {
                        errors.push('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_VARIANTS_FIELDS_NOT_SET; ?>');
                        break;
                    }
                    case 'search': {
                        errors.push('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_SEARCH_FIELDS_NOT_SET; ?>');
                        break;
                    }
                }
            });

        <?php } ?>

        $nc.ajax({
            url: '<?= nc_module_path(); ?>netshop/admin/?controller=exchange&action=check_mapping_data',
            type: 'POST',
            data: data,
            // Обработаем успешное выполнение запроса
            success: function(response) {
                if (response.status === 'ok' && errors.length === 0) {
                    form.removeClass('validate').submit();
                    return;
                }
                // Добавим ошибки, полученные при проверке на сервере
                for (var i in response.errors) {
                    errors.push(response.errors[i]);
                }
                printErrors(errors);
            }
        });

        return false;
    }

    function printErrors(errors) {
        if (errors.length === 0) {
            return;
        }
        for (var i in errors) {
            var number = parseInt(i) + 1;
            errors[i] = number + ") " + errors[i];
        }
        var errorText = '<?= NETCAT_MODULE_NETSHOP_EXCHANGE_PLEASE_FIX_ERROR; ?>:\n' + errors.join('\n');
        alert(errorText);
    }

    // Список всех полей и их описания (новые поля тоже учитываются)
    function getAllFieldsData() {
        var fields = [];
        $nc(document).find('.nc-netshop-exchange-field-select').each(function() {
            var value = parseInt($nc(this).val());
            var option = $nc(this).find('option[value="' + value + '"]');
            var fieldDescription = null;
            var fieldName = null;

            if (value > 0) {
                fieldName = option.data('name');
                fieldDescription = option.data('description');
            } else if (value === -1) {
                fieldName = $nc(this).parent().parent().find('.nc-netshop-exchange-new-field-input__name').val();
                fieldDescription = $nc(this).parent().parent().find('.nc-netshop-exchange-new-field-input__description').val();
            }

            if (!fieldName || !fieldDescription) {
                return;
            }

            fields.push([fieldName, fieldDescription]);
        });
        return fields;
    }

    // #################################################################################################################
    // #                                  ЛОГИКА ОБРАБОТКИ КОМПОНЕНТОВ И ИХ ПОЛЕЙ                                      #
    // #################################################################################################################

    <?php if ($has_goods) { ?>

        var components = null;
        var selectedFields = <?= nc_array_json(!empty($mapping['fields']) ? $mapping['fields'] : array()); ?>;
        var specialFields = ['<?= nc_netshop_exchange_object::FAKE_FIELD_NO_FIELD; ?>', '<?= nc_netshop_exchange_object::FAKE_FIELD_PARENT_FIELD; ?>', '<?= nc_netshop_exchange_object::FAKE_FIELD_NEW_FIELD; ?>'];

        function getValueByIndex(index, array) {
            for (var i in array) {
                if (i == index) {
                    return array[i];
                }
            }

            return null;
        }

        function refreshFieldSelect(component, select, selected_id, selected_value, callback) {
            var selectHtml = [];
            for (var i in component.fields) {
                var isFieldSelected = selected_id == component.fields[i].id;
                var isSelectedFieldValueIsSameWithNew = selected_value == component.fields[i].field.description;
                var isNewField = parseInt(select.data('new')) == 1 && component.fields[i].id == -1;
                var selectedAttr = (isFieldSelected || isSelectedFieldValueIsSameWithNew || isNewField) ? ' selected' : '';

                var selectText = component.fields[i].field.description + ' [' + component.fields[i].field.name + ']';
                if (specialFields.indexOf(component.fields[i].field.name) !== -1) {
                    selectText = component.fields[i].field.description;
                }

                selectHtml.push('<option value="' + component.fields[i].id + '"' + selectedAttr + ' data-type="' +
                    component.fields[i].field.type_of_data_id + '" data-name="' + component.fields[i].field.name +
                    '" data-description="' + component.fields[i].field.description + '">' +
                    selectText + '</option>');
            }
            selectHtml.join('');
            select.html(selectHtml);
            if (callback) {
                callback();
            }
        }

        function refreshFieldsSelects(componentId, selectedFields) {
            var component = getValueByIndex(componentId, components);

            nc_netshop_exchange_modal_show('modal-fields-processing-progress', function() {
                var fieldsSelects = $nc(document).find('.nc-netshop-exchange-field-select');
                var totalFieldsSelects = fieldsSelects.length;
                var progressTotal = totalFieldsSelects + 3;
                var counter = 0;
                function refreshNextFieldSelect(callback) {
                    var fieldSelect = $nc(fieldsSelects[counter]);

                    var selectedId = null;
                    var fieldKey = fieldSelect.siblings('.nc-netshop-exchange-new-field-input__key').val();

                    if (fieldKey && selectedFields.hasOwnProperty(fieldKey)) {
                        selectedId = selectedFields[fieldKey];
                    }

                    refreshFieldSelect(component, fieldSelect, selectedId, fieldSelect.find('option:selected').text(), function() {
                        counter++;
                        modalProgressSet(counter, progressTotal, function() {
                            if (counter < totalFieldsSelects) {
                                refreshNextFieldSelect(callback);
                            } else {
                                callback();
                            }
                        });
                    });
                }
                // Да, я ненавижу яваскрипт, отчасти потому, что не умею на нём нормально прагать...
                refreshNextFieldSelect(function() {
                    refreshFieldsNewField(function() {
                        modalProgressSet(++counter, progressTotal, function() {
                            refreshFieldsHideAlreadySelectedFields(function() {
                                modalProgressSet(++counter, progressTotal, function() {
                                    refreshFieldsMapNotMappedFields(function() {
                                        modalProgressSet(++counter, progressTotal, function() {
                                            setTimeout(function() {
                                                nc_netshop_exchange_modal_hide(function() {
                                                    modalProgressSet(0, 1);
                                                });
                                            }, 500);
                                        });
                                    });
                                });
                            });
                        });
                    });
                });
            });
        }

        function refreshFieldsMapNotMappedFields(callback) {
            <?php $fields_can_be_hard_mapped = $object->get_hard_mapped_fields();
                if (!empty($fields_can_be_hard_mapped)) { ?>

                    var convertFields = <?= nc_array_json(!empty($fields_can_be_hard_mapped) ? $fields_can_be_hard_mapped : array()); ?>;

                    // Найдем все селекты с не выбранными еще полями
                    $nc(document).find('.nc-netshop-exchange-field-select').each(function() {
                        var val = parseInt($nc(this).val());

                        if (val > 0) {
                            return null;
                        }

                        // Найдем по соседству скрытое поле, в котором хранится имя поля
                        var fieldName = $nc(this).siblings('input[type="hidden"]').val();
                        if (fieldName) {
                            // Если есть соответствие стандартного поля и поля из файла
                            if (convertFields.hasOwnProperty(fieldName)) {
                                var fieldConvertedName = convertFields[fieldName];

                                // Если это поле не было еще выбрано
                                var fieldOption = $nc(this).find('option[data-name="' + fieldConvertedName + '"]:not(.already-selected)');
                                if (fieldOption.length) {
                                    // То выберем это поле
                                    var val = fieldOption.val();
                                    $nc(this).val(val).trigger('change');
                                }
                            }
                        }
                    });

            <?php } ?>

            if (callback) {
                callback();
            }
        }

        function refreshFieldsNewField(callback) {
            var value = $nc(document).find('input[name="mapping[component_source]"]:checked').val();

            $nc(document).find('.nc-netshop-exchange-field-select option[data-type="-1"]').each(function() {
                switch (value) {
                    case 'current': {
                        $nc(this).hide();

                        if ($nc(this).prop('selected')) {
                            $nc(this).prop('selected', false);

                            $nc(this).parent().find('option').first().prop('selected', 'selected');
                        }

                        break;
                    }
                    case 'new': {
                        $nc(this).show();

                        break;
                    }
                }
            });
            if (callback) {
                callback();
            }
        }

        // У select'ов для выбор полей - скрываем варианты полей, которые уже выбраны в других select'ах
        function refreshFieldsHideAlreadySelectedFields(callback) {
            var selectedFieldsValues = [];

            $nc(document).find('.nc-netshop-exchange-field-select').each(function() {
                var val = parseInt($nc(this).val());

                selectedFieldsValues.push(val);
            });

            if (selectedFieldsValues.length === 0) {
                return;
            }

            $nc(document).find('.nc-netshop-exchange-field-select').each(function() {
                var selectVal = parseInt($nc(this).val());

                $nc(this).find('option').each(function() {
                    var optionVal = parseInt($nc(this).val());

                    if (optionVal === 0 || optionVal === -1) {
                        return;
                    }

                    $nc(this).show();
                    $nc(this).removeClass('already-selected');

                    if (selectVal === optionVal) {
                        return;
                    }

                    if (selectedFieldsValues.indexOf(optionVal) > -1) {
                        $nc(this).hide();
                        $nc(this).addClass('already-selected');
                    }
                });
            });
            if (callback) {
                callback();
            }
        }

        // Если выбран раздел как текущий, то проверим, может быть уже есть инфоблок с компонентом "Товар" внутри.
        // Если так, то выберем его при необходимости при сопоставлении
        function updateComponentBySubdivision() {
            var subdivisionSource = $nc(document).find('input[name="mapping[subdivision_source]"]:checked').val();
            if (subdivisionSource !== 'current') {
                return;
            }
            var subdivisionId = $nc(document).find('select[name="mapping[subdivision_id]"] option:selected').val();
            var data = {
                nc_token: '<?= $nc_core->token->get(); ?>',
                subdivision_id: subdivisionId
            };
            $nc.ajax({
                url: '<?= nc_module_path(); ?>netshop/admin/?controller=exchange&action=check_subdivision_for_goods_component',
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.status !== 'ok') {
                        return;
                    }
                    // Id компонента типа товар, который находится в данном разделе
                    var componentId = parseInt(response.component_id);
                    if (componentId <= 0) {
                        return
                    }
                    // Если данный компонент ещё не был выбран, или он выбран в качестве базового для нового, то
                    // выберем найденный компонент как текущий
                    var currentComponentSource = $nc(document).find('input[name="mapping[component_source]"]:checked').val();
                    var currentComponentId = parseInt($nc(document).find('select[name="mapping[component_id]"]').val());
                    if (currentComponentSource === 'current' && currentComponentId === componentId) {
                        return;
                    }
                    $nc('select[name="mapping[component_id]"] option').removeAttr('selected');
                    $nc('select[name="mapping[component_id]"] option[value="' + componentId + '"]').prop('selected', true);
                    $nc('input[name="mapping[component_source]"]').removeAttr('checked');
                    $nc('input[name="mapping[component_source]"][value="current"]').prop('checked', true);
                    $nc('input[name="mapping[component_source]"]').trigger('change');
                }
            });
        }

        $nc(function() {
            components = <?= nc_array_json($components); ?>;

            $nc(document).on('change', 'select[name="mapping[component_id]"]', function() {
                var componentId = parseInt($nc(this).val());

                refreshFieldsSelects(componentId, selectedFields);
            });

            $nc(document).on('change', 'input[name="mapping[component_source]"]', function() {
                switch ($nc('input[name="mapping[component_source]"]:checked').val()) {
                    case 'current':
                        $nc('#nc-netshop-exchange-component-new').hide();
                        $nc('#nc-netshop-exchange-component-current').show();

                        $nc('.nc-netshop-exchange-component-fields-selection').hide();

                        $nc('#nc-netshop-exchange-component-selection-title').text('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_COMPONENT; ?>');

                        break;

                    case 'new':
                        $nc('#nc-netshop-exchange-component-current').hide();
                        $nc('#nc-netshop-exchange-component-new').show();

                        $nc('.nc-netshop-exchange-component-fields-selection').show();

                        $nc('#nc-netshop-exchange-component-selection-title').text('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_PARENT_COMPONENT; ?>');

                        break;

                }

                refreshFieldsSelects(parseInt($nc('select[name="mapping[component_id]"]').val()), selectedFields);
            });

            $nc(document).on('change', '.nc-netshop-exchange-field-select', function() {
                var hasNewField = false;

                $nc(document).find('.nc-netshop-exchange-field-select').each(function() {
                    var type = $nc(this).find('option:selected').data('type');

                    if (type === -1) {
                        hasNewField = true;
                    }
                });

                $nc(document).find('.nc-netshop-exchange-btn-edit-field').each(function() {
                    if (hasNewField) {
                        $nc(this).attr('style', 'display: inline-block !important');
                    } else {
                        $nc(this).attr('style', 'display: none');
                    }
                });

                if (hasNewField) {
                    $nc(document).find('.nc-netshop-exchange-btn-edit-field').attr('style', 'display: inline-block !important');

                    $nc(document).find('.nc-netshop-exchange-field-select option[data-type="-1"]:not(:selected)').each(function() {
                        $nc(this).parent().parent().parent().find('.nc-netshop-exchange-btn-edit-field').attr('style', 'display: none');
                    });
                }

                refreshFieldsHideAlreadySelectedFields();
                componentFieldsRebuildFields();
            });

            var selectedComponentId = <?= $is_component_exists ? $default_component_id : 'null'; ?>;
            setTimeout(function() {
                if (selectedComponentId !== null) {
                    $nc(document).find('select[name="mapping[component_id]"]').val(selectedComponentId).trigger('change');
                    $nc(document).find('input[name="mapping[component_source]"][value="current"]').trigger('click');
                } else {
                    <?php $component_keys = array_keys($components); ?>
                    refreshFieldsSelects(<?= $component_keys[0]; ?>, selectedFields);
                    $nc(document).find('input[name="mapping[component_source]"][value="new"]').trigger('click');
                }
            }, 1000);
        });

    <?php } ?>

    // #################################################################################################################
    // #                                         ЛОГИКА ОБРАБОТКИ РАЗДЕЛОВ                                             #
    // #################################################################################################################

    $nc(function() {
        $nc(document).on('change', 'input[name="mapping[subdivision_source]"]', function() {
            switch ($nc(this).val()) {
                case 'current': {
                    $nc('#nc-netshop-exchange-subdivision-new').hide();
                    $nc('#nc-netshop-exchange-subdivision-current').show();

                    $nc('#nc-netshop-exchange-subdivision-selection-title').text('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_SUBDIVISION_THAT_EXISTS; ?>');

                    $nc('#nc-netshop-exchange-subdivision option[value="0"]').hide();
                    if (parseInt($nc('#nc-netshop-exchange-subdivision').val()) == 0) {
                        $nc('#nc-netshop-exchange-subdivision option').removeAttr('selected');
                        $nc('#nc-netshop-exchange-subdivision option:eq(1)').attr('selected', 'selected');
                    }

                    <?php if ($has_goods) { ?>

                    updateComponentBySubdivision();

                    <?php } ?>

                    break;
                }
                case 'new': {
                    $nc('#nc-netshop-exchange-subdivision-current').hide();
                    $nc('#nc-netshop-exchange-subdivision-new').show();

                    $nc('#nc-netshop-exchange-subdivision-selection-title').text('<?= NETCAT_MODULE_NETSHOP_EXCHANGE_SELECT_PARENT_SUBDIVISION; ?>');

                    $nc('#nc-netshop-exchange-subdivision option[value="0"]').show();

                    break;
                }
            }
        });

        <?php if ($has_goods) { ?>

        $nc(document).on('change', 'select[name="mapping[subdivision_id]"]', function() {
            updateComponentBySubdivision();
        });

        <?php } ?>

        <?php if ($is_subdivision_exists) { ?>

        setTimeout(function() {
            $nc(document).find('input[name="mapping[subdivision_source]"][value="current"]').trigger('click');
        }, 200);

        <?php } ?>
    });
})();
</script>