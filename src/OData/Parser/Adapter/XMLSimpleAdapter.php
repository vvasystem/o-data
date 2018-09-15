<?php

namespace OData\Parser\Adapter;

class XMLSimpleAdapter extends AbstractXMLAdapter
{

    /**
     * @param string $namespaceURI
     * @param string $elementName
     * @param string $version
     * @param string $encoding
     * @return \SimpleXMLElement
     */
    public function createElementNS(string $namespaceURI, string $elementName, string $version = '1.0', string $encoding = 'UTF-8'): \SimpleXMLElement
    {
        $xmlString = \sprintf(
            '<?xml version="%s" encoding="%s" standalone="yes"?><%s xmlns="%s"/>',
            $version,
            $encoding,
            $elementName,
            $namespaceURI
        );
        return \simplexml_load_string($xmlString);
    }

    /**
     * @param \SimpleXMLElement $object
     * @param string $namespaceURI
     * @param string $qualifiedName
     * @param string $value
     */
    public function setAttributeNS($object, string $namespaceURI, string $qualifiedName, string $value)
    {
        $object->addAttribute($qualifiedName, $value, $namespaceURI);
    }

    /**
     * @param \SimpleXMLElement $object
     * @param string $name
     * @param string $value
     */
    public function setAttribute($object, string $name, string $value)
    {
        $object->addAttribute($name, $value);
    }

    /**
     * @param \SimpleXMLElement $object
     * @param string $elementName
     * @param string $namespaceURI
     * @return \SimpleXMLElement
     */
    public function appendChild($object, string $elementName, string $namespaceURI = '')
    {
        return $object->addChild($elementName, null, '' !== $namespaceURI ? $namespaceURI : null);
    }

    /**
     * @param \SimpleXMLElement $object
     * @return string
     */
    public function getObjectAsXML($object): string
    {
        return (string)$object->asXML();
    }

    /**
     * @param \SimpleXMLElement $object
     * @param string $value
     */
    public function setObjectValue($object, string $value)
    {
        $object[0] = $value;
    }

}