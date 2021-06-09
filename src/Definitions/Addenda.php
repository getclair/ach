<?php

namespace Clair\Ach\Definitions;

class Addenda extends AbstractDefinition
{
    public static array $fields = [

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
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
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
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'value' => '1',
            'number' => true,
        ],

        'entryDetailSequenceNumber' => [
            'name' => 'Entry Detail Sequence Number',
            'width' => 7,
            'position' => 5,
            'required' => false,
            'type' => FieldTypes::TYPE_ALPHANUMERIC,
            'blank' => true,
            'value' => '',
        ],

    ];
}
