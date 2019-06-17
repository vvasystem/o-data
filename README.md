# OData API

Package OData is designed to connect to REST API "1C:Enterprise" using the OData protocol.

## Install

Via Composer

``` bash
$ composer require vvasystem/o-data
```

## Usage

### 1. Create connection

```php
 $connection = ConnectionFactory::getInstance('http://localhost/test/odata/standard.odata', 'test', 'test');

```

### 2. Create table

TestTable.php

```php
use OData\Client\Table;

class TestTable extends Table
{
    /**
     * @inheritdoc
     */
    protected $_name = 'Catalog_Склады';
    
    /**
     * @inheritdoc
     */
    protected $_primary = 'Ref_Key';
}

```

```php
 $table = new TestTable(['connection' => $connection]);

```

### 3. Execute query

```php
 $resultRow = $table->insert([
    'Code'        => 'Test',
    'Discription' => 'Test',
 ]);
 
 \var_dump($resultRow);
 array(3) { 
    ["Ref_Key"]=> string(36) "31b2c19f-e241-11e6-8108-005056a77adb"
    ["Code"]=> string(5) "Test" 
    ["Discription"]=> string(5) "Test" 
 }

 $resultRow = $table->update([
    'Code'        => 'Test1',
    'Discription' => 'Test1',
 ], '31b2c19f-e241-11e6-8108-005056a77adb');
 
 $table->delete('31b2c19f-e241-11e6-8108-005056a77adb');
 
 // For getting count of entries
 $count = $table->count();
 
 // Running RPC
 $result = $table->call('SelectChanges', [
    'DataExchangePoint' => 'http://localhost/1c/odata/standard.odata/ExchangePlan_Обмен(guid'9d586f0e-afec-11e7-be8e-b888e3a9a739')',
    'MessageNo'         => '123456',
 ]);
 
```

## License

The MIT License (MIT).
