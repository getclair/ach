<?php

namespace Clair\Ach\Definitions;

class Entry extends AbstractDefinition
{
    public static array $addenda = [

        'recordTypeCode' => [
            'name' => 'Record Type Code',
            'width' => 1,
            'position' => 1,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '7',
        ],

        'addendaTypeCode' => [
            'name' => 'Addenda Type Code',
            'width' => 2,
            'position' => 2,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '05',
        ],

        'paymentRelatedInformation' => [
            'name' => 'Payment Related Information',
            'width' => 80,
            'position' => 3,
            'required' => false,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'addendaSequenceNumber' => [
            'name' => 'Addenda Sequence Number',
            'width' => 4,
            'position' => 4,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 1,
        ],

        'entryDetailSequenceNumber' => [
            'name' => 'Entry Detail Sequnce Number',
            'width' => 7,
            'position' => 5,
            'required' => false,
            'type' => FieldTypes::TYPE_NUMERIC,
            'blank' => true,
            'value' => '',
        ],
    ];

    public static array $fields = [

        'recordTypeCode' => [
            'name' => 'Record Type Code',
            'width' => 1,
            'position' => 1,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '6',
        ],

        'transactionCode' => [
            'name' => 'Transaction Code',
            'width' => 2,
            'position' => 2,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
        ],

        'receivingDFI' => [
            'name' => 'Receiving DFI Identification',
            'width' => 8,
            'position' => 3,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'checkDigit' => [
            'name' => 'Check Digit',
            'width' => 1,
            'position' => 4,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => '',
        ],

        'DFIAccount' => [
            'name' => 'DFI Account Number',
            'width' => 17,
            'position' => 5,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'amount' => [
            'name' => 'Amount',
            'width' => 10,
            'position' => 6,
            'required' => true,
            'type' => FieldTypes::TYPE_NUMERIC,
            'value' => 0,
        ],

        'idNumber' => [
            'name' => 'Individual Identification Number',
            'width' => 15,
            'position' => 7,
            'required' => false,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'individualName' => [
            'name' => 'Individual Name',
            'width' => 22,
            'position' => 8,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'discretionaryData' => [
            'name' => 'Discretionary Data',
            'width' => 2,
            'position' => 9,
            'required' => false,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '',
        ],

        'addendaId' => [
            'name' => 'Addenda Record Indicator',
            'width' => 1,
            'position' => 10,
            'required' => true,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '0',
        ],

        'traceNumber' => [
            'name' => 'Trace Number',
            'width' => 15,
            'position' => 11,
            'required' => false,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'blank' => true,
            'value' => '',
        ],

    ];
}
