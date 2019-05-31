<?php

namespace weikit\core\db;


class WpdbPDOStatement extends \PDOStatement
{
    /**
     * @var string
     */
    protected $statementQuery;
    /**
     * @var array
     */
    protected $statementBindings = [];
    /**
     * @var WpdbPDO
     */
    protected $pdo;
    /**
     * @var \mysqli
     */
    protected $dbh;
    /**
     * @var int
     */
    protected $defaultFetchMode;
    /**
     * @var mixed
     */
    protected $defaultFetchArgument;
    /**
     * @var array
     */
    protected $defaultFetchConstructorParams = [];

    /**
     * MysqliPDOStatement constructor.
     *
     * @param WpdbPDO $bridge
     * @param string $method
     * @param string $statement
     */
    public function __construct(WpdbPDO $pdo, $method, $statement)
    {
        $this->pdo              = $pdo;
        $this->dbh              = &$this->pdo->dbh;
        $this->defaultFetchMode = $this->pdo->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE);
        $args                   = func_get_args();
        call_user_func_array([$this, $method], array_slice($args, 2));
    }

    /**
     * @inheritdoc
     */
    protected function prepare($statement, $driver_options = [])
    {
        $this->statementQuery = $statement;
    }

    /**
     * @inheritdoc
     */
    public function execute($input_parameters = null)
    {
        $args = $this->convertPlaceholders($this->statementQuery, $this->statementBindings);
        $sql  = call_user_func_array([$this->pdo->db, 'prepare'], $args);

        return $this->dbh->query($sql);
    }

    /**
     * @inheritdoc
     */
    public function fetch($fetch_style = null, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = null)
    {
        if ($this->bridge->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY) && $cursor_offset !== null) {
            $this->result->data_seek($cursor_offset);
        }

        return $this->fetchRow($fetch_style);
    }

    /**
     * @inheritdoc
     */
    public function bindParam(
        $parameter,
        &$variable,
        $data_type = \PDO::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
        $this->statementBindings[$parameter] = [&$variable, $data_type, $length];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR)
    {
        $this->statementBindings[$parameter] = [$value, $data_type, null];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function bindColumn($column, &$param, $type = \PDO::PARAM_STR, $maxlen = null, $driverdata = null)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rowCount()
    {
        return $this->statement->affected_rows;
    }

    /**
     * @inheritdoc
     */
    public function fetchColumn($column_number = 0)
    {
        $row = $this->result->fetch_row();

        return $row[$column_number];
    }

    /**
     * @inheritdoc
     */
    public function fetchAll($how = null, $class_name = null, $ctor_args = null)
    {
        if (($mysqliFetchStyle = $this->mapFetchStyle($how)) !== null
            && $this->bridge->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY)) {
            return $this->result->fetch_all($mysqliFetchStyle);
        }
        $result = [];
        while ($row = $this->fetchRow($how, $class_name, (array)$ctor_args)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function fetchObject($class_name = null, $ctor_args = null)
    {
        $class_name = $class_name !== null ? $class_name : 'stdClass';

        return $this->fetchRow(\PDO::FETCH_CLASS, $class_name, (array)$ctor_args);
    }

    /**
     * @inheritdoc
     */
    public function errorCode()
    {
        return $this->statement instanceof \mysqli_stmt
            ? $this->statement->errno
            : $this->mysqli->errno;
    }

    /**
     * @inheritdoc
     */
    public function errorInfo()
    {
        return $this->statement instanceof \mysqli_stmt
            ? $this->statement->error
            : $this->mysqli->error;
    }

    /**
     * @inheritdoc
     */
    public function setAttribute($attribute, $value)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($attribute)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function columnCount()
    {
        return $this->result->field_count;
    }

    /**
     * @inheritdoc
     */
    public function getColumnMeta($column)
    {
        return $this->mapColumnMeta($this->result->fetch_field_direct($column));
    }

    /**
     * @inheritdoc
     */
    public function setFetchMode($mode, $params = null)
    {
        $this->defaultFetchMode     = $mode;
        $this->defaultFetchArgument = $params;
    }

    /**
     * @inheritdoc
     */
    public function nextRowset()
    {
        if ( ! $this->statement->more_results()) {
            return false;
        }
        $result       = $this->statement->next_result();
        $this->result = $this->statement->get_result();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function closeCursor()
    {
        if ($this->result instanceof \mysqli_result) {
            $this->result->close();
        }
    }

    /**
     * @inheritdoc
     */
    public function debugDumpParams()
    {
        parent::debugDumpParams(); // TODO: Change the autogenerated stub
    }

    /**
     * Free resources
     */
    public function __destruct()
    {
        $this->errorHandler = null;
        if ($this->result instanceof \mysqli_result) {
            $this->result->close();
        }
        if ($this->statement instanceof \mysqli_stmt) {
            $this->statement->close();
        }
    }

    /**
     * Parse query
     *
     * @param $queryString
     */
    protected function parseQuery($queryString)
    {
        $strings     = [];
        $bindings    = [];
        $mysqliQuery = '';
        if (preg_match_all('/"([^#"\\\\]*(?:\\\\.[^#"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/ms', $queryString,
            $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $match) {
                $strings[] = [$match[1], $match[1] + mb_strlen($match[0])];
            }
        }
        $cursor = 0;
        if (preg_match_all('/(\:\b[a-z0-9_-]+\b|\?)/ims', $queryString, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $match) {
                foreach ($strings as $string) {
                    if ($match[1] >= $string[0] && $match[1] <= $string[1]) {
                        continue(2);
                    }
                }
                $bindings[]  = $match[0];
                $mysqliQuery .= mb_substr($queryString, $cursor, $match[1] - $cursor) . '?';
                $cursor      = $match[1] + mb_strlen($match[0]);
            }
            if ($cursor < mb_strlen($queryString)) {
                $mysqliQuery .= mb_substr($queryString, $cursor);
            }
        } else {
            $mysqliQuery = $queryString;
        }
        $this->queryMysqli   = $mysqliQuery;
        $this->queryBindings = $bindings;
    }

    /**
     * Map PDO type to PHP type and cast the value
     *
     * @param $value
     * @param $type
     *
     * @return bool|int|null|string
     */
    protected function castPDOToPHPType($value, $type)
    {
        switch ($type) {
            case \PDO::PARAM_NULL:
                return null;
            case \PDO::PARAM_INT:
                return intval($value);
            case \PDO::PARAM_BOOL:
                return ! (in_array(strtolower($value), ['false', 'f']) || empty($value));
            default:
                return (string)$value;
        }
    }

    /**
     * Fetch row with any type of mode
     *
     * @param null $fetch_style
     * @param null $fetch_argument
     * @param array $ctor_args
     *
     * @return bool|mixed|null|object|\stdClass
     */
    protected function fetchRow($fetch_style = null, $fetch_argument = null, array $ctor_args = [])
    {
        if ($fetch_style === null) {
            if (($fetch_style = $this->defaultFetchMode) !== null) {
                $fetch_argument = $fetch_argument !== null ? $fetch_argument : $this->defaultFetchArgument;
                $ctor_args      = ! empty($ctor_args) ? $ctor_args : $this->defaultFetchConstructorParams;
            } else {
                $fetch_style = \PDO::FETCH_BOTH;
            }
        }
        if ($fetch_style == \PDO::FETCH_COLUMN) {
            $result = $this->result->fetch_array(MYSQLI_NUM);
            if ( ! $result) {
                return $result;
            }
            $column = intval($fetch_argument !== null ? $fetch_argument : $this->defaultFetchArgument);

            return isset($result[$column]) ? $result[$column] : null;
        } elseif ($fetch_style == \PDO::FETCH_FUNC) {
            $result = $this->result->fetch_array(\PDO::FETCH_BOTH);
            if ( ! $result) {
                return $result;
            }

            return call_user_func($fetch_argument, $result);
        } elseif (in_array($fetch_style, [\PDO::FETCH_CLASS, \PDO::FETCH_OBJ])) {
            $fetch_argument = $fetch_argument && $fetch_style == \PDO::FETCH_CLASS ? $fetch_argument : 'stdClass';

            return $ctor_args && $fetch_style == \PDO::FETCH_CLASS
                ? $this->result->fetch_object($fetch_argument, $ctor_args)
                : $this->result->fetch_object($fetch_argument);
        } elseif ($fetch_style == \PDO::FETCH_INTO) {
            $result = $this->result->fetch_array(MYSQLI_ASSOC);
            if ( ! $result) {
                return $result;
            }
            $this->hydrateObject(
                $fetch_argument,
                $result
            );

            return true;
        } elseif ($fetch_style == \PDO::FETCH_BOUND) {
            $result = $this->result->fetch_array(MYSQLI_BOTH);
            if ( ! $result) {
                return $result;
            }
            foreach ($this->resultBindings as $binding => $params) {
                $value = isset($result[$binding - 1]) ? $result[$binding - 1] : null;
                if ($params[2]) {
                    $value = mb_substr($value, 0, $params[2]);
                }
                if ($params[1]) {
                    $value = $this->castPDOToPHPType($value, $params[1]);
                }
                $params[0] = $value;
            }

            return true;
        }
        $styles      = [\PDO::FETCH_BOTH, \PDO::FETCH_ASSOC, \PDO::FETCH_NUM];
        $fetch_style = in_array($fetch_style, $styles) ? $fetch_style : reset($styles);

        return $this->result->fetch_array($this->mapFetchStyle($fetch_style));
    }

    /**
     * Map PDO fetch style to mysqli style
     *
     * @param $mode
     *
     * @return mixed
     */
    protected function mapFetchStyle($mode)
    {
        $map = [
            \PDO::FETCH_BOTH  => MYSQLI_BOTH,
            \PDO::FETCH_ASSOC => MYSQLI_ASSOC,
            \PDO::FETCH_NUM   => MYSQLI_NUM,
        ];

        return isset($map[$mode]) ? $map[$mode] : null;
    }

    /**
     * Map PDO type to mysqli type
     *
     * @param $type
     *
     * @return string
     */
    protected function mapPDOToMysqliType($type)
    {
        switch ($type) {
            case \PDO::PARAM_INT:
                return 'i';
            default:
                return 's';
        }
    }

    /**
     * Hydrate object with received data
     *
     * @param $object
     * @param $data
     *
     * @return mixed
     */
    protected function hydrateObject($object, $data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($object, $key)) {
                    $object->{$key} = $value;
                }
            }
        }

        return $object;
    }

    /**
     * Map mysqli column meta to PDO column meta format
     *
     * @param $meta
     *
     * @return array
     */
    protected function mapColumnMeta($meta)
    {
        $map    = [
            'orgname'  => 'name',
            'type'     => 'driver:decl_type',
            'table'    => 'table',
            'length'   => 'len',
            'decimals' => 'precision',
            'flags'    => 'flags',
        ];
        $result = [];
        foreach ($map as $mysqliName => $pdoName) {
            $result[$pdoName] = isset($meta[$mysqliName]) ? $meta[$mysqliName] : null;
        }

        return $result;
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
    protected function convertPlaceholders($sql, array $params)
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
}