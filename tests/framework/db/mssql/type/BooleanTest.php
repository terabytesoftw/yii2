<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\type;

use yii\db\mssql\Schema;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group db
 * @group mssql
 */
class BooleanTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testBoolean(): void
    {
        $db = $this->getConnection(true);
        $schema = $db->getSchema();
        $tableName = '{{%boolean}}';

        if ($db->getTableSchema($tableName)) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => $schema->createColumnSchemaBuilder(Schema::TYPE_PK),
                'bool_col' => $schema->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN),
            ]
        )->execute();

        $column = $db->getTableSchema($tableName)->getColumn('bool_col');

        $this->assertSame('boolean', $column->phpType);

        $db->createCommand()->insert($tableName, ['bool_col' => true])->execute();

        $boolValue = $db->createCommand("SELECT bool_col FROM $tableName WHERE id = 1")->queryScalar();

        $this->assertEquals(1, $boolValue);

        $phpTypeCast = $column->phpTypecast($boolValue);

        $this->assertSame(true, $phpTypeCast);
    }
}
