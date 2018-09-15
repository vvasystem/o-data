<?php

namespace OData\Parser;

interface XMLReaderAdapterInterface
{

    /**
     * @param string $xml
     * @return mixed
     */
    public function loadFromString(string $xml);

    /**
     * @param mixed $object
     * @param string $xPathExpression
     * @return mixed[]
     */
    public function findObjects($object, string $xPathExpression): array;

    /**
     * @param mixed $object
     * @return string
     */
    public function getObjectName($object): string;

    /**
     * @param mixed $object
     * @return string
     */
    public function getObjectValue($object): string;

    /**
     * @param mixed $object
     * @param string $attributeName
     * @return string
     */
    public function getObjectAttribute($object, string $attributeName): string;

}