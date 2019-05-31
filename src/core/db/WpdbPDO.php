<?php

namespace weikit\core\db;

/**
 * Class WpdbPDO
 * TODO 未完成, 是否需要适配wpdp
 * @see https://github.com/anonymous-php/mysqli-pdo-bridge
 * @package weikit\core\db
 */
class WpdbPDO extends \PDO
{
    /**
     * @var \wpdb
     */
    public $db;
    /**
     * @var \mysqli|null
     */
    public $dbh;

    /**
     * @var array
     */
    protected $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT,
        \PDO::ATTR_AUTOCOMMIT => true,
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_BOTH,
        \PDO::MYSQL_ATTR_INIT_COMMAND => '',
        \PDO::ATTR_PERSISTENT => false,
    ];

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        // wpdb dbh reference hack.
        $this->dbh = & \Closure::bind(function & () {
            /* @var $this \wpdb */
            return $this->dbh;
        }, $this->db, $this->db)->__invoke();
    }

    /**
     * @inheritdoc
     */
    public function beginTransaction()
    {
        return $this->dbh->begin_transaction();
    }

    /**
     * @inheritdoc
     */
    public function commit()
    {
        return $this->dbh->commit();
    }

    /**
     * @inheritdoc
     */
    public function rollBack()
    {
        return $this->dbh->rollback();
    }

    /**
     * @inheritdoc
     */
    public function setAttribute($attribute, $value)
    {
        return $this->setOptions([ $attribute => $value ]);
    }

    /**
     * @inheritdoc
     */
    public function exec($statement)
    {
        try {
            $result = $this->options[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY]
                ? $this->dbh->query($statement)
                : $this->dbh->real_query($statement);

            if ($result instanceof \mysqli_result) {
                $result->close();
                return true;
            }

            return $result ? $this->dbh->affected_rows : false;
        } catch (\mysqli_sql_exception $e) {
            throw new \PDOException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritdoc
     */
    public function query($statement, $mode = null, $arg3 = null, array $ctorargs = array())
    {
        throw new NotSupportedException('query method is not supported');
    }

    /**
     * @inheritdoc
     */
    public function prepare($statement, $options = null)
    {
        return new WpdbPDOStatement($this, __FUNCTION__, $statement, $options);
    }

    /**
     * @inheritdoc
     */
    public function lastInsertId($name = null)
    {
        return $this->dbh->insert_id;
    }

    /**
     * @inheritdoc
     */
    public function errorCode()
    {
        return $this->dbh->errno;
    }

    /**
     * @inheritdoc
     */
    public function errorInfo()
    {
        return $this->dbh->error;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($attribute)
    {
        if ($attribute == \PDO::ATTR_CONNECTION_STATUS) {
            return @$this->dbh->stat();
        }
        return $this->options[$attribute] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function quote($string, $parameter_type = \PDO::PARAM_STR)
    {
        switch ($parameter_type) {
            case \PDO::PARAM_NULL:
                $string = 'NULL';
                break;
            case \PDO::PARAM_BOOL:
                $string = intval(!(in_array(strtolower($string), ['false', 'f'], true) || empty($string)));
                break;
            case \PDO::PARAM_INT:
                $string = intval($string);
                break;
            default:
                $string = "'{$this->dbh->real_escape_string($string)}'";
        }
        return $string;
    }

    protected function setOptions($options)
    {
        $result = true;
        foreach ($options as $option => $value) {
            if (!isset($this->options[$option])) {
                continue;
            }
            $this->options[$option] = $value;
//            if ($option == \PDO::ATTR_AUTOCOMMIT) {
//                $result &= $this->errorHandler->handle(function () use ($value) {
//                    return $this->mysqli->autocommit((bool)$value);
//                });
//            }
        }
        return $result;
    }

}