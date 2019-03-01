<?php

use yii\helpers\ArrayHelper;
use weikit\core\exceptions\UnsupportedException;

function pdo()
{
    throw new UnsupportedException();
}

function pdos($table = '')
{
    throw new UnsupportedException();
}

/**
 * 执行sql语句
 *
 * @param $sql
 * @param array $params
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_query($sql, $params = [])
{
    SqlParser::checkQuery($sql);

    return Yii::$app->db->createCommand($sql, $params)->execute();
}

/**
 * 获取指定列数据
 *
 * @param string $sql
 * @param array $params
 * @param int $column
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_fetchcolumn($sql, $params = [], int $column = 0)
{
    SqlParser::checkQuery($sql);
    $command = Yii::$app->db->createCommand($sql, $params);
    if ($column === 0) {
        return $command->queryColumn();
    } else {
        $result = $command->queryAll(\PDO::FETCH_NUM);

        return ArrayHelper::getColumn($result, $column);
    }
}

/**
 * 获取一行数据
 *
 * @param string $sql
 * @param array $params
 *
 * @return array|false
 * @throws \yii\db\Exception
 */
function pdo_fetch($sql, $params = [])
{
    SqlParser::checkQuery($sql);

    return Yii::$app->db->createCommand($sql, $params)->queryOne();
}

/**
 * 获取多行数据
 *
 * @param string $sql
 * @param array $params
 * @param string|null $keyField
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_fetchall($sql, $params = [], string $keyField = null)
{
    SqlParser::checkQuery($sql);
    $result = Yii::$app->db->createCommand($sql, $params)->queryAll();

    return ArrayHelper::getColumn($result, $keyField);
}

/**
 * 获取一行数据(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param array $fields
 * @param array $orderby
 *
 * @return array|false
 * @throws \yii\db\Exception
 */
function pdo_get($tablename, $params = [], $fields = [], $orderBy = [])
{
    $select = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');
    $orderBySql = SqlPaser::parseOrderby($orderBy);

    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " $orderBySql LIMIT 1";

    return pdo_fetch($sql, $condition['params']);
}

/**
 * 获取多行数据(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param array $fields
 * @param string $keyField
 * @param array $orderBy
 * @param array $limit
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_getall(
    $tablename,
    $params = [],
    $fields = [],
    $keyField = '',
    $orderBy = [],
    $limit = []
) {
    $select = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');

    $limitSql = SqlPaser::parseLimit($limit);
    $orderBySql = SqlPaser::parseOrderby($orderBy);

    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $orderBySql . $limitSql;

    return pdo_fetchall($sql, $condition['params'], $keyField);
}

/**
 * 获取指定列的数据(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param array $limit
 * @param null $total
 * @param array $fields
 * @param string $keyField
 * @param array $orderBy
 *
 * @return array
 * @throws \yii\db\Exception
 */
function pdo_getslice(
    $tablename,
    $params = [],
    $limit = [],
    &$total = null,
    $fields = [],
    $keyField = '',
    $orderBy = []
) {
    $select = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');
    $limitSql = SqlPaser::parseLimit($limit);

    if ( ! empty($orderby)) {
        if (is_array($orderby)) {
            $orderBySql = implode(',', $orderBy);
        } else {
            $orderBySql = $orderBy;
        }
    }
    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . (! empty($orderBySql) ? " ORDER BY $orderBySql " : '') . $limitSql;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : ''),
        $condition['params']);

    return pdo_fetchall($sql, $condition['params'], $keyField);
}

/**
 * 获取指定列的数据(自动拼装Sql)
 *
 * @param $tablename
 * @param array $params
 * @param $field
 *
 * @return bool|mixed
 * @throws \yii\db\Exception
 */
function pdo_getcolumn($tablename, $params = [], $field)
{
    $result = pdo_get($tablename, $params, $field);
    if ( ! empty($result)) {
        if (strexists($field, '(')) {
            return array_shift($result);
        } else {
            return $result[$field];
        }
    }

    return false;
}

/**
 * 查询指定条件的数据是否存在(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 *
 * @return bool
 * @throws \yii\db\Exception
 */
function pdo_exists($tablename, $params = [])
{
    $row = pdo_get($tablename, $params);
    if (empty($row) || ! is_array($row) || count($row) == 0) {
        return false;
    }

    return true;
}

/**
 * 查询指定条件的数据统计数(自动拼装Sql)
 *
 * @param string $tablename
 * @param array $params
 * @param int $cachetime
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_count($tablename, $params = [], $cachetime = 15)
{
    // TODO cache
    return (int)pdo_getcolumn($tablename, $params, 'count(*)');
}

/**
 * 更新数据(自动拼装Sql)
 *
 * @param string $table
 * @param array $data
 * @param array $params
 * @param string $glue
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_update($table, $data = [], $params = [], $glue = 'AND')
{
    $fields = SqlPaser::parseParameter($data, ',');
    $condition = SqlPaser::parseParameter($params, $glue);
    $params = array_merge($fields['params'], $condition['params']);
    $sql = "UPDATE " . tablename($table) . " SET {$fields['fields']}";
    $sql .= $condition['fields'] ? ' WHERE ' . $condition['fields'] : '';

    return pdo_query($sql, $params);
}

/**
 * 插入数据(自动拼装Sql)
 *
 * @param $table
 * @param array $data
 * @param bool $replace
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_insert($table, $data = [], $replace = false)
{
    $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
    $condition = SqlPaser::parseParameter($data, ',');

    return pdo_query("$cmd " . tablename($table) . " SET {$condition['fields']}", $condition['params']);
}

/**
 * 删除数据(自动拼装Sql)
 *
 * @param string $table
 * @param array $params
 * @param string $glue
 *
 * @return int
 * @throws \yii\db\Exception
 */
function pdo_delete($table, $params = [], $glue = 'AND')
{
    $condition = SqlPaser::parseParameter($params, $glue);
    $sql = "DELETE FROM " . tablename($table);
    $sql .= $condition['fields'] ? ' WHERE ' . $condition['fields'] : '';

    return pdo_query($sql, $condition['params']);
}

/**
 * 获取最后插入的数据ID
 *
 * @return string
 */
function pdo_insertid()
{
    return Yii::$app->db->getLastInsertID();
}

/**
 * 开始事务
 *
 * @return \yii\db\Transaction
 */
function pdo_begin()
{
    Yii::$app->db->beginTransaction();
}

/**
 * 事务提交
 *
 * @throws \yii\db\Exception
 */
function pdo_commit()
{
    Yii::$app->db->getTransaction()->commit();
}

/**
 * 事务回滚
 */
function pdo_rollback()
{
    Yii::$app->db->getTransaction()->rollBack();
}

function pdo_debug($output = true, $append = [])
{
    throw new UnsupportedException();
}

/**
 * 批量运行sql语句
 *
 * @param string $sql
 */
function pdo_run($sql)
{
    $db = Yii::$app->db;
    // @see https://stackoverflow.com/questions/7690380/regular-expression-to-match-all-comments-in-a-t-sql-script/13821950#13821950 移除注释
    $sql = preg_replace('@(([\'"]).*?[^\\\]\2)|((?:\#|--).*?$|/\*(?:[^/*]|/(?!\*)|\*(?!/)|(?R))*\*\/)\s*|(?<=;)\s+@ms',
        '$1', $sql);
    // 替换前缀
    $sql = str_replace(' ims_', ' ' . $db->tablePrefix, $sql);
    $sql = str_replace(' `ims_', ' `' . $db->tablePrefix, $sql);

    foreach (explode(';', $sql) as $sql) {
        if ( ! empty($sql)) {
            pdo_query($sql);
        }
    }
}

/**
 * 查询自乱是否存在
 *
 * @param string $tablename
 * @param string $fieldName
 *
 * @return bool
 */
function pdo_fieldexists($tablename, $fieldName = '')
{
    return Yii::$app->db->getSchema()->getTableSchema(pdo_tablename($tablename))->getColumn($fieldName) !== null;
}

/**
 * 匹配表的字段类型和长度
 *
 * @param string $tablename
 * @param string $fieldName
 * @param string $dataType
 * @param string|int $length
 *
 * @return bool
 */
function pdo_fieldmatch($tablename, $fieldName, $dataType = '', $length = '')
{
    $column = Yii::$app->db->getTableSchema(pdo_tablename($tablename))->getColumn($fieldName);

    if ($column !== null) {
        if ( ! empty($datatype)) {
            $dataType .= ! empty($length) ? '(' . $length . ')' : '';
            return stripos($column->dbType, $dataType) === 0;
        }
        return true;
    }

    return false;
}

/**
 * 查询表索引名是否存在
 *
 * @param string $tablename
 * @param string $indexName
 */
function pdo_indexexists($tablename, $indexName = '')
{
    $indexes = ArrayHelper::getColumn(Yii::$app->db->getSchema()->getTableIndexes(pdo_tablename($tablename)), 'name');

    return in_array($indexName, $indexes);
}

/**
 * 获取表字段名
 *
 * @param string $tablename
 *
 * @return array
 */
function pdo_fetchallfields($tablename)
{
    return Yii::$app->db->getTableSchema(pdo_tablename($tablename))->columnNames;
}

/**
 * 查询表是否存在
 *
 * @param $tablename
 *
 * @return bool
 */
function pdo_tableexists($tablename)
{
    return Yii::$app->db->getTableSchema(pdo_tablename($tablename)) !== null;
}

/**
 * 返回带前缀的表名
 *
 * @param $tablename
 *
 * @return string
 */
function pdo_tablename($tablename)
{
    $prefix = Yii::$app->db->tablePrefix;

    return strpos($tablename, $prefix) === 0 ? $tablename : $prefix . $tablename;
}