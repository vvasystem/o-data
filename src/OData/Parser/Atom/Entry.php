<?php

namespace OData\Parser\Atom;

use OData\Parser\XMLAdapterInterface;

class Entry
{
    /** @var XMLAdapterInterface */
    private $xmlAdapter;

    /** @var  array */
    private $data;

    /**
     * @param XMLAdapterInterface $xmlAdapter
     * @param array|null $data
     */
    public function __construct(XMLAdapterInterface $xmlAdapter, array $data = null)
    {
        $this->xmlAdapter = $xmlAdapter;
        $this->setData($data ?? []);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toXML($table = '', $editLinkUri = '', int $updatedTimestamp = null): string
    {
        $entry = $this->xmlAdapter->createElementNS('http://www.w3.org/2005/Atom', 'entry');

        $this->xmlAdapter->setAttribute($entry, 'xmlns:xmlns:d', 'http://schemas.microsoft.com/ado/2007/08/dataservices');
        $this->xmlAdapter->setAttribute($entry, 'xmlns:xmlns:m', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');

        $this->xmlAdapter->appendChild($entry, 'title');

        $author = $this->xmlAdapter->appendChild($entry, 'author');
        $this->xmlAdapter->appendChild($author, 'name');

        $updated = $this->xmlAdapter->appendChild($entry, 'updated');
        $this->xmlAdapter->setObjectValue($updated, $this->timeInISO8601($updatedTimestamp ?? \time()));

        $id = $this->xmlAdapter->appendChild($entry, 'id');
        if ('' !== $editLinkUri) {
            $this->xmlAdapter->setObjectValue($id, $editLinkUri);
        }

        $content = $this->xmlAdapter->appendChild($entry, 'content');
        $this->xmlAdapter->setAttribute($content, 'type', 'application/xml');

        $properties = $this->xmlAdapter->appendChild($content, 'm:properties', 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata');
        $data = $this->getData();
        foreach ($data as $key => $value) {
            $property = $this->xmlAdapter->appendChild($properties, \sprintf('d:%s', $key), 'http://schemas.microsoft.com/ado/2007/08/dataservices');

            if (\is_array($value) && $value) {
                $addNSForProperty = true;
                $elementNS   = \sprintf('StandardODATA.%s_%s_RowType)', $table, $key);
                $elementName = 'element';
                foreach ($value as $keyElement => $element) {
                    $keyElementParts = \explode(' ', $keyElement);
                    if (2 === \count($keyElementParts)) {
                        list($elementName, $elementNS) = $keyElementParts;
                        $addNSForProperty = false;
                    }

                    if (\is_array($element)) {
                        $propertyElement = $this->xmlAdapter->appendChild($property, \sprintf('d:%s', $elementName), 'http://schemas.microsoft.com/ado/2007/08/dataservices');
                        if ($elementNS) {
                            $this->xmlAdapter->setAttributeNS(
                                $propertyElement,
                                'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata',
                                'm:type',
                                $elementNS
                            );
                        }

                        foreach ($element as $k => $v) {
                            $newElement = $this->xmlAdapter->appendChild($propertyElement, \sprintf('d:%s', $k), 'http://schemas.microsoft.com/ado/2007/08/dataservices');
                            $this->xmlAdapter->setObjectValue($newElement, $v);
                        }
                    }
                }
                if ($addNSForProperty) {
                    $this->xmlAdapter->setAttributeNS(
                        $property,
                        'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata',
                        'm:type',
                        \sprintf('Collection(StandardODATA.%s_%s_RowType)', $table, $key)
                    );
                }
            } else {
                $this->xmlAdapter->setObjectValue($property, $value);
            }
        }

        return $this->xmlAdapter->getObjectAsXML($entry);
    }

    /**
     * @param int $timestamp
     * @return string
     */
    private function timeInISO8601(int $timestamp): string
    {
        return \date('Y-m-d', $timestamp) . 'T' . \gmdate('H:i:s', $timestamp) .'Z';
    }

}