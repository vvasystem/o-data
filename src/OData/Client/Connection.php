<?php

/**
 * OData Client
 * @category   OData
 * @package    OData\Client
 * @author Victor Vasilev <vasilev@go-rost.ru>
 */
namespace OData\Client;

use GuzzleHttp\ClientInterface as RestClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use OData\Parser\Atom\ReaderInterface as AtomReaderInterface;
use \Psr\Http\Message\ResponseInterface;
use GuzzleHttp\RequestOptions;
use OData\Parser\Adapter\XMLSimpleAdapter;
use OData\Parser\Atom\Entry;
use OData\Parser\Exception\AdapterException as ParserAdapterException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Connection implements \Iterator
{
    /** @var string */
    private $url;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var RestClientInterface */
    private $client;

    /** @var AtomReaderInterface */
    private $reader;

    /** @var int */
    private $position = 0;

    /** @var array */
    private $array = [];

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param RestClientInterface $client
     * @param AtomReaderInterface $reader
     * @param string $url
     * @param string $username
     * @param string $password
     */
    public function __construct(
        RestClientInterface $client,
        AtomReaderInterface $reader,
        string $url,
        string $username = '',
        string $password = ''
    ) {
        $this->client   = $client;
        $this->reader   = $reader;
        $this->url      = $url;
        $this->username = $username;
        $this->password = $password;
        $this->logger   = new NullLogger();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $body
     * @throws ParserAdapterException
     */
    private function parserBody(string $body)
    {
        // for DELETE request
        if ('' === $body) {
            return;
        }

        // for $count request
        if (false === \strpos($body, '<?xml')) {
            $this->array[0] = $body;
            return;
        }

        $this->array = $this->reader->getEntries($body);
    }

    /**
     * @return array
     */
    private function getDefaultRequestOptions(): array
    {
        return [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            RequestOptions::AUTH => [
                $this->username,
                $this->password,
            ],
        ];
    }

    /**
     * @param null|int $offset
     * @return array|bool
     */
    public function fetch($offset = null)
    {
        if (null !== $offset) {
            $this->position += (int)$offset;
        }

        $value = false;
        if ($this->valid()) {
            $value = $this->current();
            $this->next();
        }
        return $value;
    }

    /**
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->array;
    }

    /**
     * Calling remote method.
     * @param string $table The table to insert data into.
     * @param string $guid Primary key
     * @param string $method Method name.
     * @param array $params paramName-paramValue pairs.
     * @return  array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function call($table, $guid, $method, array $params): array
    {
        $guidLink = '';

        // if is not root
        if ('' !== $table) {
            $guidLink = \sprintf("%s(guid'%s')", $table, $guid);
        }

        $response = $this->request('POST', \sprintf('%s/%s?%s', $guidLink, $method, \http_build_query($params)));
        $bodyContent = $response->getBody()->getContents();
        if ('' !== $bodyContent && false === \strpos($bodyContent, '<?xml')) {
            $bodyContent = '<?xml version="1.0" encoding="UTF-8"?>' . $bodyContent;
        }
        $this->parserBody($bodyContent);
        $this->rewind();

        return $this->fetchAll();
    }

    /**
     * Inserts a table row with specified data.
     * @param string $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return  array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function insert($table, array $bind): array
    {
        $atomEntry = new Entry(new XMLSimpleAdapter(), $bind);
        $xml = $atomEntry->toXML($table);

        $this->execute('POST', $table, $xml);

        return $this->valid() ? $this->current() : [];
    }

    /**
     * Updates table rows with specified data
     * @param  string $table The table to update.
     * @param  array $bind Column-value pairs.
     * @param  string $guid
     * @return array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function update($table, array $bind, $guid): array
    {
        $guidLink = \sprintf("/%s(guid'%s')", $table, $guid);

        $atomEntry = new Entry(new XMLSimpleAdapter(), $bind);
        $xml = $atomEntry->toXML($table, $this->url . $guidLink);

        $this->execute('PATCH', $guidLink, $xml);

        return $this->valid() ? $this->current() : [];
    }

    /**
     * Deletes table row
     * @param  string $table The table to update.
     * @param  string $guid
     * @return int
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function delete($table, $guid): int
    {
        $this->execute('DELETE', sprintf("/%s(guid'%s')", $table, $guid));
        return 1;
    }

    /**
     * Count rows
     * @param  string $table The table
     * @return int
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function count($table): int
    {
        return (int)$this->query($this->select()->from($table)->count())->fetch();
    }

    /**
     * Prepares and executes with bound data.
     * @param  string|Select $query
     * @return $this
     * @throws ConnectException
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws GuzzleException
     */
    public function query($query)
    {
        // is the $query a Select object?
        if ($query instanceof Select) {
            $query = $query->assemble();
        }

        $this->execute('GET', $query);

        return $this;
    }

    /**
     * @param string $method
     * @param string $query
     * @param null $body
     * @throws ParserAdapterException
     * @throws GuzzleException
     * @throws \RuntimeException
     * @throws ConnectException
     */
    private function execute(string $method, string $query, $body = null)
    {
        $response = $this->request($method, $query, $body);
        $this->parserBody($response->getBody()->getContents());
        $this->rewind();
    }

    /**
     * @param string $method
     * @param string $query
     * @param null $body
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws GuzzleException
     * @throws ConnectException
     */
    private function request(string $method, string $query, $body = null): ResponseInterface
    {
        $url = \sprintf('%s/%s', $this->url, $query);
        $defaultRequestOptions = $this->getDefaultRequestOptions();
        if (null !== $body) {
            $defaultRequestOptions[RequestOptions::BODY] = $body;
        }

        $this->logger->info(\sprintf('%s: %s%s%s', $method, $url, \PHP_EOL, $body));

        $response = $this->client->request($method, $url, $defaultRequestOptions);
        if (400 <= $response->getStatusCode()) {
            throw new ConnectException(\sprintf('HTTP response error %s for uri "%s" (%s)',
                $response->getStatusCode(),
                $url,
                PHP_EOL . $response->getBody()->getContents()
            ));
        }

        return $response;
    }

    /**
     * @return Select
     */
    public function select(): Select
    {
        return new Select();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->array[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @inheritdoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return \array_key_exists($this->position, $this->array);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

}