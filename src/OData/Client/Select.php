<?php

/**
 * OData Client
 * @category   OData
 * @package    OData\Client
 * @author Victor Vasilev <vasilev@go-rost.ru>
 */
namespace OData\Client;

class Select
{
    const FROM         = 'from';
    const SELECT       = 'select';
    const WHERE        = 'where';
    const ORDER        = 'order';
    const LIMIT_COUNT  = 'limit';
    const LIMIT_OFFSET = 'offset';
    const COUNT        = 'count';

    const QUERY_AND  = 'and';
    const QUERY_OR   = 'or';
    const QUERY_ASC  = 'asc';
    const QUERY_DESC = 'desc';

    /**
     * The initial values for the $parts array.
     * maximum compatibility with database adapters.
     * @var array
     */
    protected static $partsInit = [
        self::FROM => [],
        self::SELECT => [],
        self::WHERE => [],
        self::ORDER => [],
        self::LIMIT_COUNT => null,
        self::LIMIT_OFFSET => null,
        self::COUNT => false,
    ];

    /**
     * The component parts of a SELECT statement.
     * Initialized to the $partsInit array in the constructor.
     * @var array
     */
    protected $parts;

    public function __construct()
    {
        $this->parts = self::$partsInit;
    }

    /**
     * Adds a FROM table and optional columns to the query.
     * The first parameter $name can be a simple string, in which case the
     * correlation name is generated automatically.
     * The first parameter can be null or an empty string, in which case
     * no correlation name is generated or prepended to the columns named
     * in the second parameter.
     * @param  string $name The table name
     * @param  array $cols The columns to select from this table.
     * @return $this
     */
    public function from(string $name, array $cols = [])
    {
        $this->parts[static::FROM] = $name;
        $this->parts[static::SELECT] = $cols;

        return $this;
    }

    /**
     * Adds a WHERE condition to the query by AND.
     * Operator    Description    Example
     * Logical Operators
     * eq    Equal    /Suppliers?$filter=Address/City eq 'Redmond'
     * ne    Not equal    /Suppliers?$filter=Address/City ne 'London'
     * gt    Greater than    /Products?$filter=Price gt 20
     * ge    Greater than or equal    /Products?$filter=Price ge 10
     * lt    Less than    /Products?$filter=Price lt 20
     * le    Less than or equal    /Products?$filter=Price le 100
     * and    Logical and    /Products?$filter=Price le 200 and Price gt 3.5
     * or    Logical or    /Products?$filter=Price le 3.5 or Price gt 200
     * not    Logical negation    /Products?$filter=not endswith(Description,'milk')
     * Arithmetic Operators
     * add    Addition    /Products?$filter=Price add 5 gt 10
     * sub    Subtraction    /Products?$filter=Price sub 5 gt 10
     * mul    Multiplication    /Products?$filter=Price mul 2 gt 2000
     * div    Division    /Products?$filter=Price div 2 gt 4
     * mod    Modulo    /Products?$filter=Price mod 2 eq 0
     * Grouping Operators
     * ( )    Precedence grouping    /Products?$filter=(Price sub 5) gt 10
     * @param string $cond The WHERE condition.
     * @param mixed $value OPTIONAL The value to quote into the condition.
     * @return $this
     */
    public function where(string $cond, $value = null)
    {
        $this->parts[self::WHERE][] = $this->_where($cond, $value);
        return $this;
    }

    /**
     * Adds a WHERE condition to the query by OR.
     * Otherwise identical to where().
     * @param string $cond The WHERE condition.
     * @param mixed $value OPTIONAL The value to quote into the condition.
     * @return $this
     * @see where()
     */
    public function orWhere(string $cond, $value = null)
    {
        $this->parts[self::WHERE][] = $this->_where($cond, $value, false);
        return $this;
    }

    /**
     * Adds a row order to the query.
     * @param string|array $spec The column(s) and direction to order by.
     * @return $this
     */
    public function order($spec)
    {
        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ((array)$spec as $val) {
            if (empty($val)) {
                continue;
            }
            $direction = self::QUERY_ASC;
            if (\preg_match('/(.*\W)(' . self::QUERY_ASC . '|' . self::QUERY_DESC . ')\b/si', $val, $matches)) {
                $val = \trim($matches[1]);
                $direction = $matches[2];
            }
            $this->parts[self::ORDER][] = [$val, $direction];
        }

        return $this;
    }

    /**
     * Sets a limit count and offset to the query.
     * @param int $count OPTIONAL The number of rows to return.
     * @param int $offset OPTIONAL Start returning after this many rows.
     * @return $this
     */
    public function limit($count = null, $offset = null)
    {
        $this->parts[self::LIMIT_COUNT] = (int)$count;
        $this->parts[self::LIMIT_OFFSET] = (int)$offset;
        return $this;
    }

    /**
     * Get amount of all rows
     * @return $this
     */
    public function count()
    {
        $this->parts[self::COUNT] = true;
        return $this;
    }

    /**
     * Internal function for creating the where clause
     * @param string $condition
     * @param mixed $value optional
     * @param boolean $bool true = AND, false = OR
     * @return string  clause
     */
    protected function _where(string $condition, $value = null, bool $bool = true): string
    {
        if (null !== $value) {
            $condition = \str_replace('?', $value, $condition);
        }

        $cond = '';
        if ($this->parts[self::WHERE]) {
            if ($bool) {
                $cond = self::QUERY_AND . ' ';
            } else {
                $cond = self::QUERY_OR . ' ';
            }
        }

        return $cond . \sprintf('(%s)', $condition);
    }

    /**
     * Render FROM clause
     * @param string $query
     * @return string
     */
    protected function _renderFrom(string $query): string
    {
        return $query . $this->parts[static::FROM] . '?';
    }

    /**
     * Render SELECT clause
     * @param string $query
     * @return string
     */
    protected function _renderSelect(string $query): string
    {
        return $query . ($this->parts[static::SELECT] ? ('$select=' . implode(',', $this->parts[static::SELECT])) : '');
    }

    /**
     * Render WHERE clause
     * @param string $query
     * @return string
     */
    protected function _renderWhere(string $query): string
    {
        if ($this->parts[self::FROM] && $this->parts[self::WHERE]) {
            $query .= ($this->parts[self::SELECT] ? '&' : '') . '$filter=' . \rawurlencode(\implode(' ', $this->parts[self::WHERE]));
        }

        return $query;
    }

    /**
     * Render ORDER clause
     * @param string $query
     * @return string
     */
    protected function _renderOrder(string $query): string
    {
        if ($this->parts[self::ORDER]) {
            $order = [];
            foreach ($this->parts[self::ORDER] as $term) {
                if (\is_array($term)) {
                    if (\is_numeric($term[0]) && (string)(int)$term[0] === $term[0]) {
                        $order[] = (int)\trim($term[0]) . ' ' . $term[1];
                    } else {
                        $order[] = $term[0] . ' ' . $term[1];
                    }
                } elseif (\is_numeric($term) && (string)(int)$term === $term) {
                    $order[] = (int)\trim($term);
                } else {
                    $order[] = $term;
                }
            }
            $query .= ($this->parts[self::SELECT] || $this->parts[self::WHERE] ? '&' : '') . '$orderby=' . \rawurlencode(\implode(', ', $order));
        }

        return $query;
    }

    /**
     * Render LIMIT OFFSET clause
     * @param string $query
     * @return string
     */
    protected function _renderOffset(string $query): string
    {
        $count = 0;
        $offset = 0;

        if (!empty($this->parts[self::LIMIT_OFFSET])) {
            $offset = (int)$this->parts[self::LIMIT_OFFSET];
            $count = PHP_INT_MAX;
        }

        if (!empty($this->parts[self::LIMIT_COUNT])) {
            $count = (int)$this->parts[self::LIMIT_COUNT];
        }

        /*
         * Add limits clause
         */
        if ($count > 0) {
            $query .= ($this->parts[self::SELECT] || $this->parts[self::WHERE] || $this->parts[self::ORDER] ? '&' : '') . '$top=' . $count . '&$skip=' . $offset;
        }

        return $query;
    }

    /**
     * Render COUNT clause
     * @param string $query
     * @return string
     */
    protected function _renderCount(string $query): string
    {
        if ($this->parts[self::FROM] && $this->parts[self::COUNT]) {
            $query = $this->parts[static::FROM] . '/$count' . $this->_renderWhere('?');
        }

        return $query;
    }

    /**
     * Clear parts of the Select object, or an individual part.
     * @param string $part OPTIONAL
     * @return $this
     */
    public function reset($part = null)
    {
        if (null === $part) {
            $this->parts = self::$partsInit;
        } elseif (\array_key_exists($part, self::$partsInit)) {
            $this->parts[$part] = self::$partsInit[$part];
        }
        return $this;
    }

    /**
     * Converts this object to an SELECT string.
     * @return string This object as a SELECT string. (or null if a string cannot be produced.)
     */
    public function assemble(): string
    {
        $query = '';
        $partsInit = \array_keys(self::$partsInit);
        foreach ($partsInit as $part) {
            $method = '_render' . \ucfirst($part);
            if (\method_exists($this, $method)) {
                $query = $this->$method($query);
            }
        }

        return $query;
    }

}
