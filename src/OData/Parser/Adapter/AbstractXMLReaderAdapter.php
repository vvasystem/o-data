<?php

namespace OData\Parser\Adapter;

use OData\Parser\XMLReaderAdapterInterface;

abstract class AbstractXMLReaderAdapter implements XMLReaderAdapterInterface
{

    const NAMESPACES = [
        'default' => 'http://www.w3.org/2005/Atom',
        'd'       => 'http://schemas.microsoft.com/ado/2007/08/dataservices',
        'm'       => 'http://schemas.microsoft.com/ado/2007/08/dataservices/metadata',
        'at'      => 'http://purl.org/atompub/tombstones/1.0',
    ];

    abstract protected function applyNamespace($xPath);

}