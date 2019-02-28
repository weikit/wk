<?php

// TODO 优化
class SqlParser
{
    private static $checkcmd = ['SELECT', 'UPDATE', 'INSERT', 'REPLAC', 'DELETE'];
    private static $disable = [
        'function' => [
            'load_file',
            'floor',
            'hex',
            'substring',
            'if',
            'ord',
            'char',
            'benchmark',
            'reverse',
            'strcmp',
            'datadir',
            'updatexml',
            'extractvalue',
            'name_const',
            'multipoint',
            'database',
            'user',
        ],
        'action'   => [
            '@',
            'intooutfile',
            'intodumpfile',
            'unionselect',
            'uniondistinct',
            'information_schema',
            'current_user',
            'current_date',
        ],
        'note'     => ['/*', '*/', '#', '--'],
    ];

    public static function checkQuery($sql)
    {
        $cmd = strtoupper(substr(trim($sql), 0, 6));
        if (in_array($cmd, self::$checkcmd)) {
            $mark = $clean = '';
            $sql = str_replace(['\\\\', '\\\'', '\\"', '\'\''], '', $sql);
            if (strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql,
                    '-- ') === false && strpos($sql, '@') === false && strpos($sql, '`') === false) {
                $cleansql = preg_replace("/'(.+?)'/s", '', $sql);
            } else {
                $cleansql = self::stripSafeChar($sql);
            }

            $cleansql = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($cleansql));
            if (is_array(self::$disable['function'])) {
                foreach (self::$disable['function'] as $fun) {
                    if (strpos($cleansql, $fun . '(') !== false) {
                        throw new \yii\base\InvalidArgumentException("DB function [{$fun}] is not allow");
                    }
                }
            }

            if (is_array(self::$disable['action'])) {
                foreach (self::$disable['action'] as $action) {
                    if (strpos($cleansql, $action) !== false) {
                        throw new \yii\base\InvalidArgumentException("DB action [{$action}] is not allow");
                    }
                }
            }

            if (is_array(self::$disable['note'])) {
                foreach (self::$disable['note'] as $note) {
                    if (strpos($cleansql, $note) !== false) {
                        throw new \yii\base\InvalidArgumentException("DB comments is not allow");
                    }
                }
            }
        } elseif (substr($cmd, 0, 2) === '/*') {
            throw new \yii\base\InvalidArgumentException("DB comments is not allow");
        }
    }

    private static function stripSafeChar($sql)
    {
        $len = strlen($sql);
        $mark = $clean = '';
        for ($i = 0; $i < $len; $i++) {
            $str = $sql[$i];
            switch ($str) {
                case '\'':
                    if ( ! $mark) {
                        $mark = '\'';
                        $clean .= $str;
                    } elseif ($mark == '\'') {
                        $mark = '';
                    }
                    break;
                case '/':
                    if (empty($mark) && $sql[$i + 1] == '*') {
                        $mark = '/*';
                        $clean .= $mark;
                        $i++;
                    } elseif ($mark == '/*' && $sql[$i - 1] == '*') {
                        $mark = '';
                        $clean .= '*';
                    }
                    break;
                case '#':
                    if (empty($mark)) {
                        $mark = $str;
                        $clean .= $str;
                    }
                    break;
                case "\n":
                    if ($mark == '#' || $mark == '--') {
                        $mark = '';
                    }
                    break;
                case '-':
                    if (empty($mark) && substr($sql, $i, 3) == '-- ') {
                        $mark = '-- ';
                        $clean .= $mark;
                    }
                    break;
                default:
                    break;
            }
            $clean .= $mark ? '' : $str;
        }

        return $clean;
    }

    public static function parseParameter($params, $glue = ',', $alias = '')
    {
        $result = ['fields' => ' 1 ', 'params' => []];
        $split = '';
        $suffix = '';
        $allow_operator = ['>', '<', '<>', '!=', '>=', '<=', '+=', '-=', 'LIKE', 'like'];
        if (in_array(strtolower($glue), ['and', 'or'])) {
            $suffix = '__';
        }
        if ( ! is_array($params)) {
            $result['fields'] = $params;

            return $result;
        }
        if (is_array($params)) {
            $result['fields'] = '';
            foreach ($params as $fields => $value) {
                if ($glue == ',') {
                    $value = $value === null ? '' : $value;
                }
                $operator = '';
                if (strpos($fields, ' ') !== false) {
                    list($fields, $operator) = explode(' ', $fields, 2);
                    if ( ! in_array($operator, $allow_operator)) {
                        $operator = '';
                    }
                }
                if (empty($operator)) {
                    $fields = trim($fields);
                    if (is_array($value) && ! empty($value)) {
                        $operator = 'IN';
                    } elseif ($value === 'NULL') {
                        $operator = 'IS';
                    } else {
                        $operator = '=';
                    }
                } elseif ($operator == '+=') {
                    $operator = " = `$fields` + ";
                } elseif ($operator == '-=') {
                    $operator = " = `$fields` - ";
                } elseif ($operator == '!=' || $operator == '<>') {
                    if (is_array($value) && ! empty($value)) {
                        $operator = 'NOT IN';
                    } elseif ($value === 'NULL') {
                        $operator = 'IS NOT';
                    }
                }

                $select_fields = self::parseFieldAlias($fields, $alias);
                if (is_array($value) && ! empty($value)) {
                    $insql = [];
                    $value = array_values($value);
                    foreach ($value as $v) {
                        $placeholder = self::parsePlaceholder($fields, $suffix);
                        $insql[] = $placeholder;
                        $result['params'][$placeholder] = is_null($v) ? '' : $v;
                    }
                    $result['fields'] .= $split . "$select_fields {$operator} (" . implode(",", $insql) . ")";
                    $split = ' ' . $glue . ' ';
                } else {
                    $placeholder = self::parsePlaceholder($fields, $suffix);
                    $result['fields'] .= $split . "$select_fields {$operator} " . ($value === 'NULL' ? 'NULL' : $placeholder);
                    $split = ' ' . $glue . ' ';
                    if ($value !== 'NULL') {
                        $result['params'][$placeholder] = is_array($value) ? '' : $value;
                    }
                }
            }
        }

        return $result;
    }

    private static function parsePlaceholder($field, $suffix = '')
    {
        static $params_index = 0;
        $params_index++;

        $illegal_str = ['(', ')', '.', '*'];
        $placeholder = ":{$suffix}" . str_replace($illegal_str, '_', $field) . "_{$params_index}";

        return $placeholder;
    }

    private static function parseFieldAlias($field, $alias = '')
    {
        if (strexists($field, '.') || strexists($field, '*')) {
            return $field;
        }
        if (strexists($field, '(')) {
            $select_fields = str_replace(['(', ')'], ['(' . (! empty($alias) ? "`{$alias}`." : '') . '`', '`)'],
                $field);
        } else {
            $select_fields = (! empty($alias) ? "`{$alias}`." : '') . "`$field`";
        }

        return $select_fields;
    }

    public static function parseSelect($field = [], $alias = '')
    {
        if (empty($field) || $field == '*') {
            return ' SELECT *';
        }
        if ( ! is_array($field)) {
            $field = [$field];
        }
        $select = [];
        $index = 0;
        foreach ($field as $field_row) {
            if (strexists($field_row, '*')) {
                if ( ! strexists(strtolower($field_row), 'as')) {
                }
            } elseif (strexists(strtolower($field_row), 'select')) {
                if ($field_row[0] != '(') {
                    $field_row = "($field_row) AS '{$index}'";
                }
            } elseif (strexists($field_row, '(')) {
                $field_row = str_replace(['(', ')'], ['(' . (! empty($alias) ? "`{$alias}`." : '') . '`', '`)'],
                    $field_row);
                if ( ! strexists(strtolower($field_row), 'as')) {
                    $field_row .= " AS '{$index}'";
                }
            } else {
                $field_row = self::parseFieldAlias($field_row, $alias);
            }
            $select[] = $field_row;
            $index++;
        }

        return " SELECT " . implode(',', $select);
    }

    public static function parseLimit($limit, $inpage = true)
    {
        $limitsql = '';
        if (empty($limit)) {
            return $limitsql;
        }
        if (is_array($limit)) {
            if (empty($limit[0]) && ! empty($limit[1])) {
                $limitsql = " LIMIT 0, " . $limit[1];
            } else {
                $limit[0] = max(intval($limit[0]), 1);
                ! empty($limit[1]) && $limit[1] = max(intval($limit[1]), 1);
                if (empty($limit[0]) && empty($limit[1])) {
                    $limitsql = '';
                } elseif ( ! empty($limit[0]) && empty($limit[1])) {
                    $limitsql = " LIMIT " . $limit[0];
                } else {
                    $limitsql = " LIMIT " . ($inpage ? ($limit[0] - 1) * $limit[1] : $limit[0]) . ', ' . $limit[1];
                }
            }
        } else {
            $limit = trim($limit);
            if (preg_match('/^(?:limit)?[\s,0-9]+$/i', $limit)) {
                $limitsql = strexists(strtoupper($limit), 'LIMIT') ? " $limit " : " LIMIT $limit";
            }
        }

        return $limitsql;
    }

    public static function parseOrderby($orderby, $alias = '')
    {
        $orderbysql = '';
        if (empty($orderby)) {
            return $orderbysql;
        }

        if ( ! is_array($orderby)) {
            $orderby = explode(',', $orderby);
        }
        foreach ($orderby as $i => &$row) {
            $row = strtolower($row);
            list($field, $orderbyrule) = explode(' ', $row);

            if ($orderbyrule != 'asc' && $orderbyrule != 'desc') {
                unset($orderby[$i]);
            }
            $field = self::parseFieldAlias($field, $alias);
            $row = "{$field} {$orderbyrule}";
        }
        $orderbysql = implode(',', $orderby);

        return ! empty($orderbysql) ? " ORDER BY $orderbysql " : '';
    }

    public static function parseGroupby($statement, $alias = '')
    {
        if (empty($statement)) {
            return $statement;
        }
        if ( ! is_array($statement)) {
            $statement = explode(',', $statement);
        }
        foreach ($statement as $i => &$row) {
            $row = self::parseFieldAlias($row, $alias);
            if (strexists($row, ' ')) {
                unset($statement[$i]);
            }
        }
        $statementsql = implode(', ', $statement);

        return ! empty($statementsql) ? " GROUP BY $statementsql " : '';
    }
}
