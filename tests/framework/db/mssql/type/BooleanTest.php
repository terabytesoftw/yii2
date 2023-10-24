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

    public function testIsBoolean(): void
    {
        $db = $this->getConnection(true);
        $tableName = '{{%boolean}}';

        if ($db->getTableSchema($tableName)) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_PK),
                'bool_col' => $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN, 1),
            ]
        )->execute();

        $this->assertSame('boolean', $db->getTableSchema($tableName)->getColumn('bool_col')->phpType);

        $db->createCommand()->insert($tableName, ['bool_col' => true])->execute();

        $this->assertEquals(1, $db->createCommand("SELECT bool_col FROM $tableName WHERE id = 1")->queryScalar());
    }
}
