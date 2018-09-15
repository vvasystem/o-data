<?php

namespace OData\Parser\Atom;

interface ReaderInterface
{

    /**
     * @param string $xml
     * @return array
     */
    public function getEntries(string $xml): array;

}