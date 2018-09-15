<?php

namespace OData\Parser;

interface XMLAdapterInterface
{

    /**
     * @param string $namespaceURI
     * @param string $elementName
     * @param string $version
     * @param string $encoding
     * @return mixed
     */
    public function createElementNS(string $namespaceURI, string $elementName, string $version = '1.0', string $encoding = 'UTF-8');

    /**
     * @param mixed $object
     * @param string $namespaceURI
     * @param string $qualifiedName
     * @param string $value
     */
    public function setAttributeNS($object, string $namespaceURI, string $qualifiedName, string $value);

    /**
     * @param mixed $object
     * @param string $name
     * @param string $value
     */
    public function setAttribute($object, string $name, string $value);

    /**
     * @param mixed $object
     * @param string $elementName
     * @param string $namespaceURI
     * @return mixed
     */
    public function appendChild($object, string $elementName, string $namespaceURI = '');

    /**
     * @param mixed $object
     * @return string
     */
    public function getObjectAsXML($object): string;

    /**
     * @param mixed $object
     * @param string $value
     */
    public function setObjectValue($object, string $value);

}