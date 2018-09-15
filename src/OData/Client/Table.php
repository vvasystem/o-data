<?php

/**
 * OData Client
 * @category   OData
 * @package    OData\Client
 * @author Victor Vasilev <vasilev@go-rost.ru>
 */
namespace OData\Client;

use GuzzleHttp\Exception\GuzzleException;
use OData\Parser\Exception\AdapterException as ParserAdapterException;

abstract class Table
{
    /**
     * @var string
     */
    const CONNECTION = 'connection';
    /**
     * @var string
     */
    const NAME = 'name';
    /**
     * @var string
     */
    const PRIMARY = 'primary';

    /**
     * @var Connection
     */
    protected static $defaultConnection;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * The table name.
     * @var string
     */
    protected $_name = '';

    /**
     * The primary key column.
     * @var string
     */
    protected $_primary = '';

    /**
     * Constructor.
     * Supported params for $config are:
     * - connection      = user-supplied instance of oData server connection
     * - name            = table name
     * - primary         = primary key
     * @param  array $config Array of user-specified config options
     */
    public function __construct(array $config = [])
    {
        if ($config) {
            $this->setOptions($config);
        }

        $this->init();
        if (null === $this->connection) {
            $this->connection = self::$defaultConnection;
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case self::CONNECTION:
                    $this->_setConnection($value);
                    break;
                case self::NAME:
                    $this->_name = (string)$value;
                    break;
                case self::PRIMARY:
                    $this->_primary = (array)$value;
                    break;
                default:
                    // ignore unrecognized configuration directive
                    break;
            }
        }

        return $this;
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public static function setDefaultConnection($connection = null)
    {
        self::$defaultConnection = $connection;
    }

    /**
     * @return Connection
     */
    public static function getDefaultConnection()
    {
        return self::$defaultConnection;
    }

    /**
     * @param  Connection $connection
     * @return $this Provides a fluent interface
     */
    protected function _setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Initialize object
     * Called from {@link __construct()} as final step of object instantiation.
     * @return void
     */
    public function init()
    {
    }

    /**
     * @param  string|Select $select
     * @return array
     * @throws GuzzleException
     * @throws ParserAdapterException
     */
    public function query($select): array
    {
        return $this->connection->query($select)->fetchAll();
    }

    /**
     * Inserts a new row.
     * @param  array $data Column-value pairs.
     * @return array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function insert(array $data): array
    {
        return $this->connection->insert($this->_name, $data);
    }

    /**
     * Updates existing rows.
     * @param  array $data Column-value pairs.
     * @param  string $guid
     * @return array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function update(array $data, $guid): array
    {
        return $this->connection->update($this->_name, $data, $guid);
    }

    /**
     * Deletes existing rows.
     * @param  string $guid
     * @return int    The number of rows deleted.
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function delete($guid): int
    {
        return $this->connection->delete($this->_name, $guid);
    }

    /**
     * @return int
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function count(): int
    {
        return $this->connection->count($this->_name);
    }

    /**
     * @param string $method
     * @param array $params
     * @param string $guid
     * @return array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function call($method, array $params = [], $guid = ''): array
    {
        return $this->connection->call($this->_name, $guid, $method, $params);
    }

    /**
     * @param string|array $order
     * @param int $count
     * @param int $offset
     * @return array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function findAll($order = null, $count = null, $offset = null): array
    {
        return $this->fetchAll(null, $order, $count, $offset);
    }

    /**
     * @param array $fields
     * @param string|array $order
     * @param int $count
     * @param int $offset
     * @return array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function findByFields(array $fields = [], $order = null, $count = null, $offset = null): array
    {
        $where = null;
        foreach ($fields as $k => $v) {
            $where[] = \sprintf('(%s eq %s)', $k, $v);
        }
        if (null !== $where) {
            $where = \implode(' and ', $where);
        }
        return $this->fetchAll($where, $order, $count, $offset);
    }

    /**
     * @param array $fields
     * @param string|array $order
     * @return false|array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function findOneByFields(array $fields = [], $order = null)
    {
        $result = $this->findByFields($fields, $order, 1);
        if (empty($result)) {
            return false;
        }
        return \array_shift($result);
    }

    /**
     * @param string $id
     * @return false|array
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function findById($id)
    {
        $result = $this->fetchAll($this->_primary . ' eq guid\'' . $id . '\'');
        if (empty($result)) {
            return false;
        }
        return \array_shift($result);
    }

    /**
     * Fetches all rows.
     * @param string $where OPTIONAL An WHERE clause.
     * @param string|array $order OPTIONAL An ORDER clause.
     * @param int $count OPTIONAL An LIMIT count.
     * @param int $offset OPTIONAL An LIMIT offset.
     * @return array
     * @throws \RuntimeException
     * @throws ConnectException
     * @throws ParserAdapterException
     * @throws GuzzleException
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null): array
    {
        $select = $this->connection->select()->from($this->_name);
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }

        if ($count || $offset) {
            $select->limit($count, $offset);
        }

        return $this->query($select);
    }


    /**
     * Get primary key
     * @return string
     */
    public function getPrimary(): string
    {
        return $this->_primary;
    }

    /**
     * Get table name
     * @return string
     */
    public function getTableName(): string
    {
        return $this->_name;
    }

    /**
     * @param array $data
     * @return string
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function save(array $data)
    {
        $primary = $this->getPrimary();
        $id = $data[$primary] ?? '';
        unset($data[$primary]);

        if ($id) {
            if (empty($data)) {
                return $id;
            }
            $data = $this->update($data, $id);
            return \array_key_exists($primary, $data) ? $data[$primary] : '';
        }

        $data = $this->insert($data);
        return \array_key_exists($primary, $data) ? $data[$primary] : '';
    }

    /**
     * @param array $data
     * @return bool|int
     * @throws \RuntimeException
     * @throws ParserAdapterException
     * @throws ConnectException
     * @throws GuzzleException
     */
    public function remove(array $data)
    {
        $primary = $this->getPrimary();
        if (\array_key_exists($primary, $data) && $data[$primary]) {
            $id = (string)$data[$primary];
            return $this->delete($id);
        }
        return true;
    }
}
