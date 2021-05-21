<?php

namespace Clair\Ach\Definitions;

class File extends AbstractDefinition
{
    public static array $header = [

        'recordTypeCode' => [
            'name' => 'Record Type Code',
            'width' => 1,
            'position' => 1,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '1',
        ],

        'priorityCode' => [
            'name' => 'Priority Code',
            'width' => 2,
            'position' => 2,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '01',
        ],

        'immediateDestination' => [
            'name' => 'Immediate Destination',
            'width' => 10,
            'position' => 3,
            'required' => true,
            'type' => FieldTypes::TYPE_ABA,
            'paddingChar' => ' ',
            'value' => '',
        ],

        'immediateOrigin' => [
            'name' => 'Immediate Origin',
            'width' => 10,
            'position' => 4,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'paddingChar' => ' ',
            'value' => 0,
        ],

        'fileCreationDate' => [
            'name' => 'File Creation Date',
            'width' => 6,
            'position' => 5,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => null,
        ],

        'fileCreationTime' => [
            'name' => 'File Creation Time',
            'width' => 4,
            'position' => 6,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => null,
        ],

        'fileIdModifier' => [
            'name' => 'File Modifier',
            'width' => 1,
            'position' => 7,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => 'A',
        ],

        'recordSize' => [
            'name' => 'Record Size',
            'width' => 3,
            'position' => 8,
            'type' => FieldTypes::TYPE_NUMERIC,
            'required' => true,
            'value' => '094',
        ],

        'blockingFactor' => [
            'name' => 'Blocking Factor',
            'width' => 2,
            'position' => 9,
            'type' => FieldTypes::TYPE_NUMERIC,
            'required' => true,
            'value' => '10',
        ],

        'formatCode' => [
            'name' => 'Format Code',
            'width' => 1,
            'position' => 10,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '1',
        ],

        'immediateDestinationName' => [
            'name' => 'Immediate Destination Name',
            'width' => 23,
            'position' => 11,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'immediateOriginName' => [
            'name' => 'Immediate Origin Name',
            'width' => 23,
            'position' => 12,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'referenceCode' => [
            'name' => 'Reference Code',
            'width' => 8,
            'position' => 13,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

    ];

    public static array $control = [

        'recordTypeCode' => [
            'name' => 'Record Type Code',
            'width' => 1,
            'position' => 1,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '9',
        ],

        'batchCount' => [
            'name' => 'Batch Count',
            'width' => 6,
            'position' => 2,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 0,
        ],

        'blockCount' => [
            'name' => 'Block Count',
            'width' => 6,
            'position' => 3,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 0,
        ],

        'addendaCount' => [
            'name' => 'Addenda Count',
            'width' => 8,
            'position' => 4,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 0,
        ],

        'entryHash' => [
            'name' => 'Entry Hash',
            'width' => 10,
            'position' => 5,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 0,
        ],

        'totalDebit' => [
            'name' => 'Total Debit Entry Dollar Amount in File',
            'width' => 12,
            'position' => 6,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 0,
        ],

        'totalCredit' => [
            'name' => 'Total Credit Entry Dollar Amount in File',
            'width' => 12,
            'position' => 7,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 0,
        ],

        'reserved' => [
            'name' => 'Reserved',
            'width' => 39,
            'position' => 8,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'blank' => true,
            'value' => '',
        ],

    ];
}
