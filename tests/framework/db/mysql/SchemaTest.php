<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;
use yii\db\Expression;

use yii\db\mysql\ColumnSchema;
use yii\db\mysql\Schema;
use yiiunit\framework\db\AnyCaseValue;

/**
 * @group db
 * @group mysql
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'mysql';

    public function testLoadDefaultDatetimeColumn()
    {
        if (!version_compare($this->getConnection()->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.6', '>=')) {
            $this->markTestSkipped('Default datetime columns are supported since MySQL 5.6.');
        }
        $sql = <<<SQL
CREATE TABLE  IF NOT EXISTS `datetime_test`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

        $this->getConnection()->createCommand($sql)->execute();

        $schema = $this->getConnection()->getTableSchema('datetime_test');

        $dt = $schema->columns['dt'];

        $this->assertInstanceOf(Expression::className(), $dt->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP', (string)$dt->defaultValue);
    }

    public function testDefaultDatetimeColumnWithMicrosecs()
    {
        if (!version_compare($this->getConnection()->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.6.4', '>=')) {
            $this->markTestSkipped('CURRENT_TIMESTAMP with microseconds as default column value is supported since MySQL 5.6.4.');
        }
        $sql = <<<SQL
CREATE TABLE  IF NOT EXISTS `current_timestamp_test`  (
  `dt` datetime(2) NOT NULL DEFAULT CURRENT_TIMESTAMP(2),
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

        $this->getConnection()->createCommand($sql)->execute();

        $schema = $this->getConnection()->getTableSchema('current_timestamp_test');

        $dt = $schema->columns['dt'];
        $this->assertInstanceOf(Expression::className(), $dt->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP(2)', (string)$dt->defaultValue);

        $ts = $schema->columns['ts'];
        $this->assertInstanceOf(Expression::className(), $ts->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP(3)', (string)$ts->defaultValue);
    }

    public function testGetSchemaNames()
    {
        $this->markTestSkipped('Schemas are not supported in MySQL.');
    }

    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();

        $result['1: check'][2][0]->columnNames = null;
        $result['1: check'][2][0]->expression = "(`C_check` <> _utf8mb4\\'\\')";

        $result['2: primary key'][2]->name = null;

        // Work aroung bug in MySQL 5.1 - it creates only this table in lowercase. O_o
        $result['3: foreign key'][2][0]->foreignTableName = new AnyCaseValue('T_constraints_2');

        return $result;
    }

    /**
     * When displayed in the INFORMATION_SCHEMA.COLUMNS table, a default CURRENT TIMESTAMP is displayed
     * as CURRENT_TIMESTAMP up until MariaDB 10.2.2, and as current_timestamp() from MariaDB 10.2.3.
     *
     * @see https://mariadb.com/kb/en/library/now/#description
     * @see https://github.com/yiisoft/yii2/issues/15167
     */
    public function testAlternativeDisplayOfDefaultCurrentTimestampInMariaDB()
    {
        /**
         * We do not have a real database MariaDB >= 10.2.3 for tests, so we emulate the information that database
         * returns in response to the query `SHOW FULL COLUMNS FROM ...`
         */
        $schema = new Schema();
        $column = $this->invokeMethod($schema, 'loadColumnSchema', [[
            'field' => 'emulated_MariaDB_field',
            'type' => 'timestamp',
            'collation' => NULL,
            'null' => 'NO',
            'key' => '',
            'default' => 'current_timestamp()',
            'extra' => '',
            'privileges' => 'select,insert,update,references',
            'comment' => '',
        ]]);

        $this->assertInstanceOf(ColumnSchema::className(), $column);
        $this->assertInstanceOf(Expression::className(), $column->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP', $column->defaultValue);
    }

    /**
     * When displayed in the INFORMATION_SCHEMA.COLUMNS table, a default CURRENT TIMESTAMP is provided
     * as NULL.
     *
     * @see https://github.com/yiisoft/yii2/issues/19047
     */
    public function testAlternativeDisplayOfDefaultCurrentTimestampAsNullInMariaDB()
    {
        $schema = new Schema();
        $column = $this->invokeMethod($schema, 'loadColumnSchema', [[
            'field' => 'emulated_MariaDB_field',
            'type' => 'timestamp',
            'collation' => NULL,
            'null' => 'NO',
            'key' => '',
            'default' => NULL,
            'extra' => '',
            'privileges' => 'select,insert,update,references',
            'comment' => '',
        ]]);

        $this->assertInstanceOf(ColumnSchema::className(), $column);
        $this->assertEquals(NULL, $column->defaultValue);
    }

    public function getExpectedColumns()
    {
        $version = $this->getConnection()->getSchema()->getServerVersion();

        $columns = array_merge(
            parent::getExpectedColumns(),
            [
                'int_col' => [
                    'type' => 'integer',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'int' : 'int(11)',
                    'phpType' => 'integer',
                    'allowNull' => false,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'scale' => null,
                    'defaultValue' => null,
                ],
                'int_col2' => [
                    'type' => 'integer',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'int' : 'int(11)',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'int_col3' => [
                    'type' => 'integer',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'int unsigned' : 'int(11) unsigned',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'tinyint_col' => [
                    'type' => 'tinyint',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'tinyint' : 'tinyint(3)',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 3,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 3,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'smallint_col' => [
                    'type' => 'smallint',
                    'dbType' =>  \version_compare($version, '8.0.17', '>') ? 'smallint' : 'smallint(1)',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 1,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 1,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'bigint_col' => [
                    'type' => 'bigint',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'bigint unsigned' : 'bigint(20) unsigned',
                    'phpType' => 'string',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 20,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 20,
                    'scale' => null,
                    'defaultValue' => null,
                ],
            ]
        );

        if (version_compare($version, '5.7', '<')) {
            $columns['int_col3']['phpType'] = 'string';
            $columns['json_col']['type'] = 'text';
            $columns['json_col']['dbType'] = 'longtext';
            $columns['json_col']['phpType'] = 'string';
        }

        return $columns;
    }

    public function constraintsProviderMariaDb()
    {
        $result = $this->constraintsProvider();

        $result['1: check'][2][0]->expression = "`C_check` <> ''";
        $result['2: primary key'][2]->name = null;

        return $result;
    }

    /**
     * @dataProvider constraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraints($tableName, $type, $expected)
    {
        $version = $this->getConnection(false)->getServerVersion();

        if (\stripos($version, 'mariadb') !== false) {
            $this->markTestSkipped('Test is for MySQL only.');
        }

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (version_compare($version, '8.0.16', '<') && $type === 'checks') {
            $this->expectException('yii\base\NotSupportedException');
        }

        $constraints = $this->getConnection(false)->getSchema()->{'getTable' . ucfirst($type)}($tableName);
        $this->assertMetadataEquals($expected, $constraints);
    }

    /**
     * @dataProvider constraintsProviderMariaDb
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsMariaDb($tableName, $type, $expected)
    {
        $version = $this->getConnection(false)->getServerVersion();

        if (\stripos($version, 'mariadb') === false) {
            $this->markTestSkipped('Test is for MariaDB only.');
        }

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (\stripos($version, 'mariadb') !== false && version_compare($version, '10.2.0', '<') && $type === 'checks') {
            $this->expectException('yii\base\NotSupportedException');
        }

        $constraints = $this->getConnection(false)->getSchema()->{'getTable' . ucfirst($type)}($tableName);
        $this->assertMetadataEquals($expected, $constraints);
    }

    public function uppercaseConstraintsProviderMariaDb()
    {
        return $this->constraintsProviderMariaDb();
    }

    /**
     * @dataProvider uppercaseConstraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoUppercase($tableName, $type, $expected)
    {
        $version = $this->getConnection(false)->getServerVersion();

        if (\stripos($version, 'mariadb') !== false) {
            $this->markTestSkipped('Test is for MySQL only.');
        }

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        $version = $this->getConnection(false)->getServerVersion();

        if (version_compare($version, '8.0.16', '<') && $type === 'checks') {
            $this->expectException('yii\base\NotSupportedException');
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
    }

    /**
     * @dataProvider uppercaseConstraintsProviderMariaDb
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoUppercaseMariaDb($tableName, $type, $expected)
    {
        $version = $this->getConnection(false)->getServerVersion();

        if (\stripos($version, 'mariadb') === false) {
            $this->markTestSkipped('Test is for MariaDB only.');
        }

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (\stripos($version, 'mariadb') !== false && version_compare($version, '10.2.0', '<') && $type === 'checks') {
            $this->expectException('yii\base\NotSupportedException');
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
    }

    public function lowercaseConstraintsProviderMariaDb()
    {
       return $this->constraintsProviderMariaDb();
    }

    /**
     * @dataProvider lowercaseConstraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoLowercase($tableName, $type, $expected)
    {
        $version = $this->getConnection(false)->getServerVersion();

        if (\stripos($version, 'mariadb') !== false) {
            $this->markTestSkipped('Test is for MySQL only.');
        }

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (version_compare($version, '8.0.16', '<') && $type === 'checks') {
            $this->expectException('yii\base\NotSupportedException');
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
    }

    /**
     * @dataProvider lowercaseConstraintsProviderMariaDb
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoLowercaseMariaDb($tableName, $type, $expected)
    {
        $version = $this->getConnection(false)->getServerVersion();

        if (\stripos($version, 'mariadb') === false) {
            $this->markTestSkipped('Test is for MariaDB only.');
        }

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (\stripos($version, 'mariadb') !== false && version_compare($version, '10.2.0', '<') && $type === 'checks') {
            $this->expectException('yii\base\NotSupportedException');
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
    }
}
