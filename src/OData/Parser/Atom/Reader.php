<?php

namespace OData\Parser\Atom;

use OData\Parser\Exception\AdapterException;
use OData\Parser\XMLReaderAdapterInterface;

class Reader implements ReaderInterface
{
    const QUERY_ERROR            = '/m:error';
    const QUERY_ID               = 'default:id';
    const QUERY_CATEGORY         = 'default:category';
    const QUERY_ROOT_FEED        = '/default:feed';

    const QUERY_ENTRY            = 'default:entry';
    const QUERY_ROOT_ENTRY       = '/default:entry';
    const QUERY_ENTRY_DELETED    = 'at:deleted-entry';

    const QUERY_CONTENT          = 'default:content';

    const QUERY_PROPERTIES       = 'm:properties/d:*';
    const QUERY_PROPERTY_ELEMENT = 'd:element';
    const QUERY_PROPERTY         = 'd:*';

    const ID_FIELD_NAME            = '__id';
    const CATEGORY_TERM_FIELD_NAME = '__categoryTerm';
    const REF_FIELD_NAME           = '__ref';
    const DELETED_ENTRY_FIELD_NAME = '__deletedEntry';

    const DELETED_ENTRY_OBJECT_NAME = 'deleted-entry';

    /** @var XMLReaderAdapterInterface */
    private $xmlReaderAdapter;

    public function __construct(XMLReaderAdapterInterface $xmlReaderAdapter)
    {
        $this->xmlReaderAdapter = $xmlReaderAdapter;
    }

    /**
     * @param mixed $object
     * @return null|Object
     */
    private function getError($object)
    {
        $errors = $this->xmlReaderAdapter->findObjects($object, self::QUERY_ERROR);
        return \array_shift($errors);
    }

    /**
     * @param mixed $object
     * @return null|Object
     */
    private function getFeed($object)
    {
        $feeds = $this->xmlReaderAdapter->findObjects($object, self::QUERY_ROOT_FEED);
        return \array_shift($feeds);
    }

    /**
     * @param mixed $object
     * @param string $query
     * @return array
     */
    private function getEntriesFromNode($object, string $query): array
    {
        return \array_merge(
            $this->xmlReaderAdapter->findObjects($object, $query),
            $this->xmlReaderAdapter->findObjects($object, self::QUERY_ENTRY_DELETED)
        );
    }

    /**
     * @param mixed $object
     * @return null|Object
     */
    private function getContent($object)
    {
        $contents = $this->xmlReaderAdapter->findObjects($object, self::QUERY_CONTENT);
        return \array_shift($contents);
    }

    /**
     * @param mixed $object
     * @return null|Object
     */
    private function getId($object)
    {
        $ids = $this->xmlReaderAdapter->findObjects($object, self::QUERY_ID);
        return \array_shift($ids);
    }

    /**
     * @param mixed $object
     * @return null|Object
     */
    private function getCategory($object)
    {
        $categories = $this->xmlReaderAdapter->findObjects($object, self::QUERY_CATEGORY);
        return \array_shift($categories);
    }

    /**
     * @param mixed $object
     * @return array
     */
    private function getEntryProperties($object): array
    {
        return $this->xmlReaderAdapter->findObjects($object, self::QUERY_PROPERTIES);
    }

    private function parseProperties(array $properties): array
    {
        $result = [];
        while (null !== ($property = \array_shift($properties))) {
            $value = $this->xmlReaderAdapter->getObjectValue($property);

            // for array type value
            $propertyElements = $this->xmlReaderAdapter->findObjects($property, self::QUERY_PROPERTY_ELEMENT);
            if (empty($propertyElements)) {
                // for multi array type value
                $propertyElements = $this->xmlReaderAdapter->findObjects($property, self::QUERY_PROPERTY);
            }
            $elements = [];
            while (null !== ($propertyElement = \array_shift($propertyElements))) {
                $elementProperties = $this->xmlReaderAdapter->findObjects($propertyElement, self::QUERY_PROPERTY);
                $element = [];
                while (null !== ($elementProperty = \array_shift($elementProperties))) {
                    $element[$this->xmlReaderAdapter->getObjectName($elementProperty)] = $this->xmlReaderAdapter->getObjectValue($elementProperty);
                }
                if ($element) {
                    $propertyElementName = $this->xmlReaderAdapter->getObjectName($propertyElement);
                    if ('element' === $propertyElementName) {
                        $elements[] = $element;
                    } else {
                        $elements[$propertyElementName] = $element;
                    }
                }
            }
            if ($elements) {
                $value = $elements;
            }
            $result[$this->xmlReaderAdapter->getObjectName($property)] = $value;
        }

        return $result;

    }

    /**
     * @param string $xml
     * @return array
     * @throws AdapterException
     */
    public function getEntries(string $xml): array
    {
        try {
            $rootElement = $this->xmlReaderAdapter->loadFromString($xml);
        } catch (\Throwable $e) {
            throw new AdapterException($e->getMessage());
        }

        $error = $this->getError($rootElement);
        if (\is_object($error)) {
            throw new AdapterException($this->xmlReaderAdapter->getObjectValue($error));
        }

        $result = [];
        $feed = $this->getFeed($rootElement);
        if ($feed) {
            $entries = $this->getEntriesFromNode($feed, self::QUERY_ENTRY);
        } else {
            $entries = $this->getEntriesFromNode($rootElement, self::QUERY_ROOT_ENTRY);
        }
        while ($entry = \array_shift($entries)) {
            $content = $this->getContent($entry);
            $object = [];
            if ($content) {
                $properties = $this->getEntryProperties($content);
                $object = $this->parseProperties($properties);

                $object[self::DELETED_ENTRY_FIELD_NAME] = false;

                $id = $this->getId($entry);
                if ($id) {
                    $object[self::ID_FIELD_NAME] = $this->xmlReaderAdapter->getObjectValue($id);
                }

                $category = $this->getCategory($entry);
                if ($category) {
                    $object[self::CATEGORY_TERM_FIELD_NAME] = $this->xmlReaderAdapter->getObjectAttribute($category, 'term');
                }
            } elseif (self::DELETED_ENTRY_OBJECT_NAME === $this->xmlReaderAdapter->getObjectName($entry)) {
                $object[self::REF_FIELD_NAME] = $this->xmlReaderAdapter->getObjectAttribute($entry, 'ref');
                $object[self::DELETED_ENTRY_FIELD_NAME] = true;
            }

            if ($object) {
                $result[] = $object;
            }
        }

        return $result;
    }

}