<?php

function pdo()
{
    unsupported();
}

function pdos($table = '')
{
    unsupported();
}

function pdo_query($sql, $params = [])
{
    global $wpdb;
    $sql = _prepare_sql($sql, $params);

    return $wpdb->query($sql);
}

function pdo_fetchcolumn($sql, $params = [], $column = 0)
{
    global $wpdb;
    $sql = _prepare_sql($sql, $params);

    return $wpdb->get_col($sql, $column);
}

function pdo_fetch($sql, $params = [])
{
    global $wpdb;
    $sql = _prepare_sql($sql, $params);

    return $wpdb->get_row($sql, ARRAY_A);
}

function pdo_fetchall($sql, $params = [], $keyfield = '')
{
    global $wpdb;
    $sql = _prepare_sql($sql, $params);

    $tmp = $wpdb->get_results($sql, ARRAY_A);
    if (empty($keyfield)) {
        return $tmp;
    } else {
        $return = [];
        if ( ! empty($tmp)) {
            foreach ($tmp as $key => $row) {
                if (isset($row[$keyfield])) {
                    $return[$row[$keyfield]] = $row;
                } else {
                    $return[] = $row;
                }
            }
        }

        return $return;
    }
}

function pdo_get($tablename, $params = [], $fields = [], $orderby = [])
{
    $select     = SqlPaser::parseSelect($fields);
    $condition  = SqlPaser::parseParameter($params, 'AND');
    $orderbysql = SqlPaser::parseOrderby($orderby);

    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " $orderbysql LIMIT 1";

    return pdo_fetch($sql, $condition['params']);
}

function pdo_getall(
    $tablename,
    $params = [],
    $fields = [],
    $keyfield = '',
    $orderby = [],
    $limit = []
) {
    $select    = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');

    $limitsql   = SqlPaser::parseLimit($limit);
    $orderbysql = SqlPaser::parseOrderby($orderby);

    $sql = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $orderbysql . $limitsql;

    return pdo_fetchall($sql, $condition['params'], $keyfield);
}

function pdo_getslice(
    $tablename,
    $params = [],
    $limit = [],
    &$total = null,
    $fields = [],
    $keyfield = '',
    $orderby = []
) {
    $select    = SqlPaser::parseSelect($fields);
    $condition = SqlPaser::parseParameter($params, 'AND');
    $limitsql  = SqlPaser::parseLimit($limit);

    if ( ! empty($orderby)) {
        if (is_array($orderby)) {
            $orderbysql = implode(',', $orderby);
        } else {
            $orderbysql = $orderby;
        }
    }
    $sql   = "{$select} FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . (! empty($orderbysql) ? " ORDER BY $orderbysql " : '') . $limitsql;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($tablename) . (! empty($condition['fields']) ? " WHERE {$condition['fields']}" : ''),
        $condition['params']);

    return pdo_fetchall($sql, $condition['params'], $keyfield);
}

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

function pdo_exists($tablename, $params = [])
{
    $row = pdo_get($tablename, $params);
    if (empty($row) || ! is_array($row) || count($row) == 0) {
        return false;
    }

    return true;
}

function pdo_count($tablename, $params = [], $cachetime = 15)
{
    return (int)pdo_getcolumn($tablename, $params, 'count(*)');
}

function pdo_update($table, $data = [], $params = [], $glue = 'AND')
{
    $fields    = SqlPaser::parseParameter($data, ',');
    $condition = SqlPaser::parseParameter($params, $glue);
    $params    = array_merge($fields['params'], $condition['params']);
    $sql       = "UPDATE " . tablename($table) . " SET {$fields['fields']}";
    $sql       .= $condition['fields'] ? ' WHERE ' . $condition['fields'] : '';

    return pdo_query($sql, $params);
}

function pdo_insert($table, $data = [], $replace = false)
{
    $cmd       = $replace ? 'REPLACE INTO' : 'INSERT INTO';
    $condition = SqlPaser::parseParameter($data, ',');

    return pdo_query("$cmd " . tablename($table) . " SET {$condition['fields']}", $condition['params']);
}

function pdo_delete($table, $params = [], $glue = 'AND')
{
    $condition = SqlPaser::parseParameter($params, $glue);
    $sql       = "DELETE FROM " . tablename($table);
    $sql       .= $condition['fields'] ? ' WHERE ' . $condition['fields'] : '';

    return pdo_query($sql, $condition['params']);
}

function pdo_insertid()
{
    global $wpdb;

    return $wpdb->insert_id;
}

function pdo_begin()
{
    global $wpdb;
    $wpdb->query('START TRANSACTION');
}

function pdo_commit()
{
    global $wpdb;
    $wpdb->query('COMMIT');
}

function pdo_rollback()
{
    global $wpdb;
    $wpdb->query('ROLLBACK');
}

function pdo_debug($output = true, $append = [])
{
    unsupported();
}

function pdo_run($sql)
{
    $sql = str_replace("\r", "\n", str_replace(' ims_ ' . WEIKIT_TABLE_PREFIX, $sql));
    $sql = str_replace("\r", "\n", str_replace(' `ims_`' . WEIKIT_TABLE_PREFIX, $sql));
    $ret = [];
    $num = 0;
    $sql = preg_replace("/\;[ \f\t\v]+/", ';', $sql);
    foreach (explode(";\n", trim($sql)) as $query) {
        $ret[$num] = '';
        $queries   = explode("\n", trim($query));
        foreach ($queries as $query) {
            $ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0] . $query[1] == '--') ? '' : $query;
        }
        $num++;
    }
    unset($sql);
    foreach ($ret as $query) {
        $query = trim($query);
        if ($query) {
            pdo_query($query, []);
        }
    }
}

function pdo_fieldexists($tablename, $fieldname = '')
{
    $field = pdo_fetch('DESCRIBE ' . tablename($tablename) . ' `' . $fieldname . '`', []);

    return ! empty($field);
}

function pdo_fieldmatch($tablename, $fieldname, $datatype = '', $length = '')
{
    $datatype   = strtolower($datatype);
    $field_info = pdo_fetch("DESCRIBE " . tablename($tablename) . ' `' . $fieldname . '`', []);
    if (empty($field_info)) {
        return false;
    }
    if ( ! empty($datatype)) {
        $find = strexists($field_info['Type'], '(');
        if (empty($find)) {
            $length = '';
        }
        if ( ! empty($length)) {
            $datatype .= ("({$length})");
        }

        return strpos($field_info['Type'], $datatype) === 0 ? true : -1;
    }

    return true;
}

function pdo_indexexists($tablename, $indexname = '')
{
    if ( ! empty($indexname)) {
        $indexs = pdo_fetchall("SHOW INDEX FROM " . tablename($tablename), [], '');
        if ( ! empty($indexs) && is_array($indexs)) {
            foreach ($indexs as $row) {
                if ($row['Key_name'] == $indexname) {
                    return true;
                }
            }
        }
    }

    return false;
}

function pdo_fetchallfields($tablename)
{
    $fields = pdo_fetchall("DESCRIBE {$tablename}", [], 'Field');

    return array_keys($fields);
}

function pdo_tableexists($tablename)
{
    $table = trim(tablename($tablename), '`');
    $data = pdo_fetch("SHOW TABLES LIKE '{$table}'", []);
    if ( ! empty($data)) {
        return in_array($table, array_values($data));
    }

    return false;
}

function _prepare_sql($sql, $params = [])
{
    global $wpdb;
    if (count($params) > 0) {
        $args = _convert_pdo_placeholders($sql, $params);
        $sql  = call_user_func_array([$wpdb, 'prepare'], $args);
    }

    return $sql;
}

/**
 * Convert pdo named or unnamed placeholders to wpdb style placeholders
 *
 * @param $sql
 * @param $params
 *
 * Example:
 * ```php
 *  convert_pdo_placeholders('select * from wp_users where user_login = :user_login', array(':user_login' => 'admin'))
 *  // output ['select * from wp_users where user_login = %s', array('admin')]
 *
 *  convert_pdo_placeholders('select * from wp_users where user_login = ?', array('admin'))
 *  // output ['select * from wp_users where user_login = %s', array('admin')]
 * ```
 *
 * @return array
 */
function _convert_pdo_placeholders($sql, array $params)
{
    $params_keys = array_keys($params);
    $first_key   = array_shift($params_keys);

    if ((is_int($first_key) && strpos($sql, '?') === false) && substr($first_key, 0, 1) !== ':') { // wpdb style
        return [$sql, $params];
    }

    $contents = []; // collect content inside quotes
    $i        = 0;
    $sql      = preg_replace_callback('/(("[^"\\\\]*(?:\\\\.[^"\\\\]*)*")|(`[^`\\\\]*(?:\\\\.[^`\\\\]*)*`)|(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'))/s',
        function ($matches) use (&$i, &$contents) {
            $replace            = '{{' . $i . '}}';
            $contents[$replace] = $matches[0];
            $i++;

            return $replace;
        }, $sql); // replace quotes contents

    if (is_int($first_key)) { // unnamed placeholders
        $i   = 0;
        $sql = preg_replace_callback('/(\?)/', function ($matches) use ($params, &$i) {
            if (array_key_exists($i, $params)) {
                $value = $params[$i];
                if (is_int($value)) {
                    return '%d';
                } elseif (is_float($value)) {
                    return '%f';
                } else {
                    return '%s';
                }
            }

            return $matches[0];
        }, $sql);
    } elseif (substr($first_key, 0, 1) === ':') { // named placeholders
        $sql    = preg_replace_callback('/([:][a-zA-Z_]+[a-zA-Z0-9_]+)/', function ($matches) use ($params) {
            if (array_key_exists($matches[0], $params)) {
                $value = $params[$matches[0]];
                if (is_int($value)) {
                    return '%d';
                } elseif (is_float($value)) {
                    return '%f';
                } else {
                    return '%s';
                }
            }

            return $matches[0];
        }, $sql);
        $params = array_values($params);
    }
    if (count($contents) > 0) {
        $sql = strtr($sql, $contents); // replace contents back
    }


    return [$sql, $params];
}