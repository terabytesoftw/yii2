<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

/**
 * Command represents an MySQL SQL statement to be executed against a database.
 *
 * {@inheritdoc}
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.50
 */
class Command extends \yii\db\Command
{
    /**
     * Creates a SQL command for adding a check constraint to an existing table.
     * @param string $name the name of the check constraint.
     * The name will be properly quoted by the method.
     * @param string $table the table that the check constraint will be added to.
     * The name will be properly quoted by the method.
     * @param string $expression the SQL of the `CHECK` constraint.
     * @param bool $enforce whether to enforce the constraint. Defaults to `true`.
     * @return $this the command object itself.
     * @since 2.0.50
     */
    public function addCheck($name, $table, $expression, $enforce = true)
    {
        $sql = $this->db->getQueryBuilder()->addCheck($name, $table, $expression, $enforce);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

}
