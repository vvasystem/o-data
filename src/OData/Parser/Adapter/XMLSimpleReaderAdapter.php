<?php

namespace OData\Parser\Adapter;

use OData\Parser\Exception\AdapterException;

class XMLSimpleReaderAdapter extends AbstractXMLReaderAdapter
{

    /**
     * @param \SimpleXMLElement $xPath
     */
    protected function applyNamespace($xPath) {
        foreach (self::NAMESPACES as $prefix => $namespaceURI) {
            $xPath->registerXPathNamespace($prefix, $namespaceURI);
        }
    }

    /**
     * @param string $xml
     * @return mixed
     * @throws AdapterException
     */
    public function loadFromString(string $xml)
    {
        try {
            $simpleXML = new \SimpleXMLElement($xml);
        } catch (\Throwable $e) {
            throw new AdapterException($e->getMessage());
        }

        return $simpleXML;
    }

    /**
     * @param \SimpleXMLElement $object
     * @param string $xPathExpression
     * @return mixed
     */
    public function findObjects($object, string $xPathExpression): array
    {
        $this->applyNamespace($object);
        return $object->xpath($xPathExpression);
    }

    /**
     * @param \SimpleXMLElement $object
     * @return string
     */
    public function getObjectName($object): string
    {
        return $object->getName();
    }

    /**
     * @param \SimpleXMLElement $object
     * @return string
     */
    public function getObjectValue($object): string
    {
        return (string)$object;
    }

    /**
     * @param \SimpleXMLElement $object
     * @param string $attributeName
     * @return string
     */
    public function getObjectAttribute($object, string $attributeName): string
    {
        return isset($object[$attributeName]) ? (string)$object[$attributeName] : '';
    }

}