<?php

namespace Parser\Atom;

use OData\Parser\Adapter\XMLSimpleReaderAdapter;
use OData\Parser\Atom\Reader;
use OData\Parser\Exception\AdapterException;
use ODataTest\TestCase;

class ReaderTest extends TestCase
{

    private function getXML(string $fileName)
    {
        return \file_get_contents(static::TEST_DATA_PATH. '/' . $fileName);
    }

    public function testGetEntries()
    {
        $atom = new Reader(new XMLSimpleReaderAdapter());

        $entries = $atom->getEntries($this->getXML('catalog_operating_parameter.xml'));
        $this->assertCount(6, $entries);

        $entry = \array_shift($entries);

        $this->assertTrue(\array_key_exists('__id', $entry));
        $this->assertSame("http://localhost/1c/odata/standard.odata/Catalog_ПараметрыВыработкиОС(guid'31b2c19f-e241-11e6-8108-005056a77adb')", $entry['__id']);

        $this->assertTrue(\array_key_exists('__categoryTerm', $entry));
        $this->assertSame('StandardODATA.Catalog_ПараметрыВыработкиОС', $entry['__categoryTerm']);

        $this->assertTrue(\array_key_exists('Ref_Key', $entry));
        $this->assertSame('31b2c19f-e241-11e6-8108-005056a77adb', $entry['Ref_Key']);

        $this->assertTrue(\array_key_exists('DeletionMark', $entry));
        $this->assertSame('false', $entry['DeletionMark']);

        $this->assertTrue(\array_key_exists('Code', $entry));
        $this->assertSame('000000001', $entry['Code']);

        $this->assertTrue(\array_key_exists('Description', $entry));
        $this->assertSame('Машино-часы', $entry['Description']);

        $this->assertTrue(\array_key_exists('ЕдиницаИзмерения_Key', $entry));
        $this->assertSame('02c71dcc-e200-11e6-8108-005056a77adb', $entry['ЕдиницаИзмерения_Key']);
    }

    public function testGetEntriesWithSingleEntry()
    {
        $atom = new Reader(new XMLSimpleReaderAdapter());

        $entries = $atom->getEntries($this->getXML('single_entry.xml'));
        $this->assertCount(1, $entries);

        $entry = \array_pop($entries);

        $this->assertTrue(\array_key_exists('__id', $entry));
        $this->assertSame("http://localhost/1c/odata/standard.odata/Catalog_ПараметрыВыработкиОС(guid'31b2c19f-e241-11e6-8108-005056a77adb')", $entry['__id']);

        $this->assertTrue(\array_key_exists('__categoryTerm', $entry));
        $this->assertSame('StandardODATA.Catalog_ПараметрыВыработкиОС', $entry['__categoryTerm']);

        $this->assertTrue(\array_key_exists('Ref_Key', $entry));
        $this->assertSame('31b2c19f-e241-11e6-8108-005056a77adb', $entry['Ref_Key']);

        $this->assertTrue(\array_key_exists('DeletionMark', $entry));
        $this->assertSame('false', $entry['DeletionMark']);

        $this->assertTrue(\array_key_exists('Code', $entry));
        $this->assertSame('000000001', $entry['Code']);

        $this->assertTrue(\array_key_exists('Description', $entry));
        $this->assertSame('Машино-часы', $entry['Description']);

        $this->assertTrue(\array_key_exists('ЕдиницаИзмерения_Key', $entry));
        $this->assertSame('02c71dcc-e200-11e6-8108-005056a77adb', $entry['ЕдиницаИзмерения_Key']);
    }

    public function testGetEntriesWithCollectionType()
    {
        $atom = new Reader(new XMLSimpleReaderAdapter());

        $entries = $atom->getEntries($this->getXML('catalog_defect_typical.xml'));
        $this->assertCount(2, $entries);

        $entry = \array_shift($entries);

        $this->assertTrue(\array_key_exists('Ref_Key', $entry));
        $this->assertSame('429ee164-204e-11e7-810e-005056a77adb', $entry['Ref_Key']);

        $this->assertTrue(\array_key_exists('DeletionMark', $entry));
        $this->assertSame('false', $entry['DeletionMark']);

        $this->assertTrue(\array_key_exists('Code', $entry));
        $this->assertSame('000000002', $entry['Code']);

        $this->assertTrue(\array_key_exists('Description', $entry));
        $this->assertSame('Типовой дефект', $entry['Description']);

        $this->assertTrue(\array_key_exists('ВидДефекта_Key', $entry));
        $this->assertSame('861e8bbf-e165-11e6-8108-005056a77adb', $entry['ВидДефекта_Key']);

        $this->assertTrue(\array_key_exists('ПричиныВозникновенияДефекта', $entry));
        $this->assertCount(1, $entry['ПричиныВозникновенияДефекта']);

        $causesOfDefectAppearance = \array_shift($entry['ПричиныВозникновенияДефекта']);
        $this->assertTrue(\array_key_exists('Ref_Key', $causesOfDefectAppearance));
        $this->assertSame('429ee164-204e-11e7-810e-005056a77adb', $causesOfDefectAppearance['Ref_Key']);
        $this->assertTrue(\array_key_exists('LineNumber', $causesOfDefectAppearance));
        $this->assertSame('1', $causesOfDefectAppearance['LineNumber']);
        $this->assertTrue(\array_key_exists('Причина_Key', $causesOfDefectAppearance));
        $this->assertSame('3312a2fc-1dd9-11e7-810e-005056a77adb', $causesOfDefectAppearance['Причина_Key']);
    }

    public function testGetEntriesWithDeletedEntry()
    {

        $atom = new Reader(new XMLSimpleReaderAdapter());

        $entries = $atom->getEntries($this->getXML('exchange_plan.xml'));
        $this->assertCount(3, $entries);

        $entry = \array_pop($entries);

        $this->assertTrue(\array_key_exists('__ref', $entry));
        $this->assertSame("http://localhost/1c/odata/standard.odata/Catalog_ПараметрыВыработкиОС(guid'7d01dea8-aa7f-11e7-be8e-b888e3a9a739')", $entry['__ref']);
    }

    public function testGetEntriesMultiArrayProperty()
    {

        $atom = new Reader(new XMLSimpleReaderAdapter());

        $entries = $atom->getEntries($this->getXML('chart_of_characteristic_types.xml'));
        $this->assertCount(1, $entries);

        $entry = \array_pop($entries);

        $this->assertTrue(\array_key_exists('Ref_Key', $entry));
        $this->assertSame('7f4ca510-e471-11e6-8108-005056a77adb', $entry['Ref_Key']);

        $this->assertTrue(\array_key_exists('ValueType', $entry));
        $this->assertTrue(\array_key_exists('Types', $entry['ValueType']));
        $this->assertTrue(\array_key_exists('element', $entry['ValueType']['Types']));
        $this->assertSame('Number', $entry['ValueType']['Types']['element']);

        $this->assertTrue(\array_key_exists('NumberQualifiers', $entry['ValueType']));
        $this->assertTrue(\array_key_exists('NumberQualifiers', $entry['ValueType']));
        $this->assertTrue(\array_key_exists('AllowedSign', $entry['ValueType']['NumberQualifiers']));
        $this->assertSame('Any', $entry['ValueType']['NumberQualifiers']['AllowedSign']);
    }

    public function testGetEntriesWithXMLException()
    {
        $atom = new Reader(new XMLSimpleReaderAdapter());

        $this->expectException(AdapterException::class);
        $atom->getEntries('<xml version="1.0">');
    }

    public function testGetEntriesWithErrorException()
    {
        $atom = new Reader(new XMLSimpleReaderAdapter());

        $this->expectException(AdapterException::class);
        $atom->getEntries($this->getXML('error.xml'));
    }

}