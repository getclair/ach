<?php

namespace Clair\Ach\Dictionaries;

class Batch extends AbstractDictionary
{
    public static array $controls = [

        'recordTypeCode' => [
            'name' => 'Record Type Code',
            'width' => 1,
            'position' => 1,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '8',
        ],

        'serviceClassCode' => [
            'name' => 'Service Class Code',
            'width' => 3,
            'position' => 2,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '',
        ],

        'addendaCount' => [
            'name' => 'Addenda Count',
            'width' => 6,
            'position' => 3,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => 0,
        ],

        'entryHash' => [
            'name' => 'Entry Hash',
            'width' => 10,
            'position' => 4,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => 0,
        ],

        'totalDebit' => [
            'name' => 'Total Debit Entry Dollar Amount',
            'width' => 12,
            'position' => 5,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => 0,
            'number' => true,
        ],

        'totalCredit' => [
            'name' => 'Total Credit Entry Dollar Amount',
            'width' => 12,
            'position' => 6,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => 0,
            'number' => true,
        ],

        'companyIdentification' => [
            'name' => 'Company Identification',
            'width' => 10,
            'position' => 7,
            'required' => false,
            'type' => self::TYPE_ALPHANUMERIC,
            'blank' => true,
            'value' => '',
        ],

        'messageAuthenticationCode' => [
            'name' => 'Message Authentication Code',
            'width' => 19,
            'position' => 8,
            'required' => false,
            'type' => self::TYPE_ALPHANUMERIC,
            'blank' => true,
            'value' => '',
        ],

        'reserved' => [
            'name' => 'Reserved',
            'width' => 6,
            'position' => 9,
            'required' => false,
            'type' => self::TYPE_ALPHANUMERIC,
            'blank' => true,
            'value' => '',
        ],

        'originatingDFI' => [
            'name' => 'Originating DFI',
            'width' => 8,
            'position' => 10,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
        ],

        'batchNumber' => [
            'name' => 'Batch Number',
            'width' => 7,
            'position' => 11,
            'required' => false,
            'type' => self::TYPE_NUMERIC,
            'value' => 8,
        ],

    ];

    public static array $headers = [

        'recordTypeCode' => [
            'name' => 'Record Type Code',
            'width' => 1,
            'position' => 1,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '5',
        ],

        'serviceClassCode' => [
            'name' => 'Service Class Code',
            'width' => 3,
            'position' => 2,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '',
        ],

        'companyName' => [
            'name' => 'Company Name',
            'width' => 16,
            'position' => 3,
            'required' => true,
            'type' => self::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'companyIdentification' => [
            'name' => 'Company Identification',
            'width' => 10,
            'position' => 5,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '',
        ],

        'standardEntryClassCode' => [
            'name' => 'Standard Entry Class Code',
            'width' => 3,
            'position' => 6,
            'required' => true,
            'type' => self::TYPE_ALPHA,
            'value' => '',
        ],

        'companyEntryDescription' => [
            'name' => 'Standard Entry Class Code',
            'width' => 10,
            'position' => 7,
            'required' => true,
            'type' => self::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'companyDescriptiveDate' => [
            'name' => 'Company Descriptive Date',
            'width' => 6,
            'position' => 8,
            'required' => false,
            'type' => self::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'effectiveEntryDate' => [
            'name' => 'Effective Entry Date',
            'width' => 6,
            'position' => 9,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '',
        ],

        'settlementDate' => [
            'name' => 'Settlement Date',
            'width' => 3,
            'position' => 10,
            'required' => false,
            'type' => self::TYPE_NUMERIC,
            'blank' => true,
            'value' => '',
        ],

        'originatorStatusCode' => [
            'name' => 'Originator Status Code',
            'width' => 1,
            'position' => 11,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '1',
        ],

        'originatingDFI' => [
            'name' => 'Originating DFI',
            'width' => 8,
            'position' => 12,
            'required' => true,
            'type' => self::TYPE_NUMERIC,
            'value' => '1',
        ],

        'batchNumber' => [
            'name' => 'Batch Number',
            'width' => 7,
            'position' => 13,
            'required' => false,
            'type' => self::TYPE_NUMERIC,
            'value' => 0,
        ],

    ];
}
