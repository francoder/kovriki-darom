<?php

require '../../../no_header.inc.php';

/**
 * Field properties as JSON
 */

/**
 * Output:
 *      { ComponentTypeName:
 *          [
 *              { id: 'classId:fieldId',
 *                description: 'readable field name',
 *                type: 'string|integer|...',
 *                classifier: 'ClassifierTableName' },
 *              { another field ... }
 *          ],
 *         NextComponentTypeName: ...
 *      }
 */

$exporter = new nc_netshop_condition_admin_fieldexporter();
echo nc_array_json($exporter->export());
