<?php

namespace Parser\Atom;

use OData\Parser\Adapter\XMLSimpleAdapter;
use OData\Parser\Atom\Entry;
use ODataTest\TestCase;

class EntryTest extends TestCase
{

    public function testAsXML()
    {
        $entry = new Entry(new XMLSimpleAdapter(), [
            'Ref_Key'     => '31b2c19f-e241-11e6-8108-005056a77adb',
            'Code'        => '000000001',
            'Description' => 'Машино-часы',
            'Документ'    => [
                [
                    'LineNumber'   => 1,
                    'Документ_Key' => '31b2c19f-e241-11e6-8108-005056a77abc',
                ]
            ],
            'ValueType' => [
                'Types Collection(Edm.String)' => [
                    'element' => 'Number',
                ],
                'NumberQualifiers ' => [
                    'AllowedSign'    => 'Any',
                    'Digits'         => 0,
                    'FractionDigits' => 0,
                ],
            ],
        ]);

        $table = 'Catalog_ПараметрыВыработкиОС';
        $guid = '31b2c19f-e241-11e6-8108-005056a77adb';
        $guidLink = sprintf("http://localhost/1c/odata/standard.odata/%s(guid'%s')", $table, $guid);
        $resultXMLString = $entry->toXML($table, $guidLink, 0);

        $standardXMLString = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <entry xmlns="http://www.w3.org/2005/Atom" 
                   xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" 
                   xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata">
                <title/>
                <author><name/></author>
                <updated>1970-01-01T00:00:00Z</updated>
                <id>http://localhost/1c/odata/standard.odata/Catalog_ПараметрыВыработкиОС(guid\'31b2c19f-e241-11e6-8108-005056a77adb\')</id>
                <content type="application/xml">
                    <m:properties xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata">
                        <d:Ref_Key xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices">31b2c19f-e241-11e6-8108-005056a77adb</d:Ref_Key>
                        <d:Code xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices">000000001</d:Code>
                        <d:Description xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices">Машино-часы</d:Description>
                        <d:Документ xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" m:type="Collection(StandardODATA.Catalog_ПараметрыВыработкиОС_Документ_RowType)">
                            <d:element m:type="StandardODATA.Catalog_ПараметрыВыработкиОС_Документ_RowType)">
                                <d:LineNumber>1</d:LineNumber>
                                <d:Документ_Key>31b2c19f-e241-11e6-8108-005056a77abc</d:Документ_Key>
                            </d:element>
                        </d:Документ>
                        <d:ValueType xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices">
		                    <d:Types m:type="Collection(Edm.String)">
			                    <d:element>Number</d:element>
		                    </d:Types>
		                    <d:NumberQualifiers>
			                    <d:AllowedSign>Any</d:AllowedSign>
			                    <d:Digits>0</d:Digits>
			                    <d:FractionDigits>0</d:FractionDigits>
		                    </d:NumberQualifiers>
	                    </d:ValueType>
                    </m:properties>
                </content>
            </entry>';

        $this->assertXmlStringEqualsXmlString($standardXMLString, $resultXMLString);
    }

}