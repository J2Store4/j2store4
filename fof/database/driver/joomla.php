<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning F0FTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('F0F_INCLUDED') or die;

/**
 * This crazy three line bit is required to convince Joomla! to load JDatabaseInterface which is on the same file as the
 * abstract JDatabaseDriver class for reasons that beat me. It makes no sense. Furthermore, jimport on Joomla! 3.4
 * doesn't seem to actually load the file, merely registering the association in the autoloader. Hence the class_exists
 * in here.
 */
jimport('joomla.database.driver');
jimport('joomla.database.driver.mysqli');
class_exists('JDatabaseDriver', true);

/**
 * Joomla! pass-through database driver.
 */
class F0FDatabaseDriverJoomla extends F0FDatabaseDriver implements F0FDatabaseInterface //F0FDatabaseDriver //implements F0FDatabaseInterface//\Joomla\Database\DatabaseDriver//F0FDatabase implements F0FDatabaseInterface//
{
    
    /** @var F0FDatabase The real database connection object */
    protected $dbo;

    /**
     * @var    string  The character(s) used to quote SQL statement names such as table names or field names,
     *                 etc.  The child classes should define this as necessary.  If a single character string the
     *                 same character is used for both sides of the quoted name, else the first character will be
     *                 used for the opening quote and the second for the closing quote.
     * @since  11.1
     */
    protected $nameQuote = '';

    /**
     * The name of the database driver.
     *
     * @var    string
     * @since  1.0
     */
    public $name;
    /**
     * The type of the database server family supported by this driver.
     *
     * @var    string
     * @since  1.4.0
     */
    public $serverType;

    /**
     * True if the database engine supports UTF-8 character encoding.
     *
     * @var    boolean
     * @since  1.0
     */
    protected $utf = true;


    /**
     * Is this driver supported
     *
     * @since  11.2
     */
    public static function isSupported()
    {
        return true;
    }

    public function createDatabase($options, $utf = true){
        if ($options === null)
        {
            throw new \RuntimeException('$options object must not be null.');
        }

        if (empty($options->db_name))
        {
            throw new \RuntimeException('$options object must have db_name set.');
        }

        if (empty($options->db_user))
        {
            throw new \RuntimeException('$options object must have db_user set.');
        }

        $this->dbo->setQuery($this->getCreateDatabaseQuery($options, $utf));

        return $this->dbo->execute();
    }

    /**
     * Get the query strings to alter the character set and collation of a table.
     *
     * @param   string  $tableName  The name of the table
     *
     * @return  string[]  The queries required to alter the table's character set and collation
     *
     * @since   CMS 3.5.0
     */
    public function getAlterTableCharacterSet($tableName)
    {
        return $this->dbo->getAlterTableCharacterSet($tableName);
    }

    /**
     * Replace special placeholder representing binary field with the original string.
     *
     * @param   string|resource  $data  Encoded string or resource.
     *
     * @return  string  The original string.
     *
     * @since   1.7.0
     */
    public function decodeBinary($data)
    {
        return $data;
    }

    /**
     * Method to get the database connection collation in use by sampling a text field of a table in the database.
     *
     * @return  string|boolean  The collation in use by the database connection (string) or boolean false if not supported.
     *
     * @since   1.6.0
     * @throws  \RuntimeException
     */
    public function getConnectionCollation()
    {
        $this->connect();

        return $this->dbo->setQuery('SELECT @@collation_connection;')->loadResult();
    }

    /**
     * Get the name of the database driver.
     *
     * If $this->name is not set it will try guessing the driver name from the class name.
     *
     * @return  string
     *
     * @since   1.4.0
     */
    public function getName()
    {
        if (empty($this->name))
        {
            $reflect = new \ReflectionClass($this);

            $this->name = strtolower(str_replace('Driver', '', $reflect->getShortName()));
        }

        return $this->name;
    }

    /**
     * Method to get the database encryption details (cipher and protocol) in use.
     *
     * @return  string  The database encryption details.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \RuntimeException
     */
    public function getConnectionEncryption(): string
    {
        $this->connect();

        $variables = $this->dbo->setQuery('SHOW SESSION STATUS WHERE `Variable_name` IN (\'Ssl_version\', \'Ssl_cipher\')')
            ->loadObjectList('Variable_name');

        if (!empty($variables['Ssl_cipher']->Value))
        {
            return $variables['Ssl_version']->Value . ' (' . $variables['Ssl_cipher']->Value . ')';
        }

        return '';
    }

    /**
     * Determine whether or not the database engine supports UTF-8 character encoding.
     *
     * @return  boolean  True if the database engine supports UTF-8 character encoding.
     *
     * @since   1.0
     */
    public function hasUtfSupport()
    {
        return $this->utf;
    }

    /**
     * Quotes a binary string to database requirements for use in database queries.
     *
     * @param   string  $data  A binary string to quote.
     *
     * @return  string  The binary quoted input string.
     *
     * @since   1.7.0
     */
    public function quoteBinary($data)
    {
        // SQL standard syntax for hexadecimal literals
        return "X'" . bin2hex($data) . "'";
    }

    /**
     * This function replaces a string identifier with the configured table prefix.
     *
     * @param   string  $sql     The SQL statement to prepare.
     * @param   string  $prefix  The table prefix.
     *
     * @return  string  The processed SQL statement.
     *
     * @since   1.0
     */
    public function replacePrefix($sql, $prefix = '#__')
    {
        $escaped   = false;
        $startPos  = 0;
        $quoteChar = '';
        $literal   = '';

        $sql = trim($sql);
        $n   = \strlen($sql);

        while ($startPos < $n)
        {
            $ip = strpos($sql, $prefix, $startPos);

            if ($ip === false)
            {
                break;
            }

            $j = strpos($sql, "'", $startPos);
            $k = strpos($sql, '"', $startPos);

            if (($k !== false) && (($k < $j) || ($j === false)))
            {
                $quoteChar = '"';
                $j         = $k;
            }
            else
            {
                $quoteChar = "'";
            }

            if ($j === false)
            {
                $j = $n;
            }
            $literal .= str_replace($prefix, !empty($this->tablePrefix) ? $this->tablePrefix : '', substr($sql, $startPos, $j - $startPos));
            $startPos = $j;

            $j = $startPos + 1;

            if ($j >= $n)
            {
                break;
            }

            // Quote comes first, find end of quote
            while (true)
            {
                $k       = strpos($sql, $quoteChar, $j);
                $escaped = false;

                if ($k === false)
                {
                    break;
                }

                $l = $k - 1;

                while ($l >= 0 && $sql[$l] === '\\')
                {
                    $l--;
                    $escaped = !$escaped;
                }

                if ($escaped)
                {
                    $j = $k + 1;

                    continue;
                }

                break;
            }

            if ($k === false)
            {
                // Error in the query - no end quote; ignore it
                break;
            }

            $literal .= substr($sql, $startPos, $k - $startPos + 1);
            $startPos = $k + 1;
        }

        if ($startPos < $n)
        {
            $literal .= substr($sql, $startPos, $n - $startPos);
        }

        return $literal;
    }

    /**
     * Method to truncate a table.
     *
     * @param   string  $table  The table to truncate
     *
     * @return  void
     *
     * @since   1.0
     * @throws  \RuntimeException
     */
    public function truncateTable($table)
    {
        $this->setQuery('TRUNCATE TABLE ' . $this->quoteName($table))
            ->execute();
    }
    /**
     * Get the server family type.
     *
     * If $this->serverType is not set it will attempt guessing the server family type from the driver name. If this is not possible the driver
     * name will be returned instead.
     *
     * @return  string
     *
     * @since   1.4.0
     */
    public function getServerType()
    {
        if (empty($this->serverType))
        {
            $name = $this->getName();

            if (stristr($name, 'mysql') !== false)
            {
                $this->serverType = 'mysql';
            }
            elseif (stristr($name, 'postgre') !== false)
            {
                $this->serverType = 'postgresql';
            }
            elseif (stristr($name, 'pgsql') !== false)
            {
                $this->serverType = 'postgresql';
            }
            elseif (stristr($name, 'oracle') !== false)
            {
                $this->serverType = 'oracle';
            }
            elseif (stristr($name, 'sqlite') !== false)
            {
                $this->serverType = 'sqlite';
            }
            elseif (stristr($name, 'sqlsrv') !== false)
            {
                $this->serverType = 'mssql';
            }
            elseif (stristr($name, 'sqlazure') !== false)
            {
                $this->serverType = 'mssql';
            }
            elseif (stristr($name, 'mssql') !== false)
            {
                $this->serverType = 'mssql';
            }
            else
            {
                $this->serverType = $name;
            }
        }

        return $this->serverType;
    }
    /**
     * Return the query string to create new Database.
     *
     * @param   stdClass  $options  Object used to pass user and database name to database driver. This object must have "db_name" and "db_user" set.
     * @param   boolean   $utf      True if the database supports the UTF-8 character set.
     *
     * @return  string  The query that creates database
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getCreateDatabaseQuery($options, $utf)
    {
        return 'CREATE DATABASE ' . $this->dbo->quoteName($options->db_name);
    }

    /**
     * Database object constructor
     *
     * @param    array $options List of options used to configure the connection
     */
    public function __construct($options = array())
    {
        // Get best matching Akeeba Backup driver instance
        $this->dbo = JFactory::getDbo();

        $reflection = new \ReflectionClass($this->dbo);

        try
        {
            $process_list = array('nameQuote','name','serverType','connection','nullDate','utf',
                'count','cursor','debug','limit','log','timings','callStacks','offset','options','sql','tablePrefix','utf8mb4','errorNum','errorMsg',
                'transactionDepth','disconnectHandlers');
            foreach ($process_list as $item){
                $refProp = $reflection->getProperty($item);
                $refProp->setAccessible(true);
                $this->$item = $refProp->getValue($this->dbo);
            }
        }
        catch (Exception $e)
        {
            $this->nameQuote = '`';
        }
    }

    public function close()
    {
        if (method_exists($this->dbo, 'close'))
        {
            $this->dbo->close();
        }
        elseif (method_exists($this->dbo, 'disconnect'))
        {
            $this->dbo->disconnect();
        }
    }

    public function disconnect()
    {
        $this->close();
    }

    public function open()
    {
        if (method_exists($this->dbo, 'open'))
        {
            $this->dbo->open();
        }
        elseif (method_exists($this->dbo, 'connect'))
        {
            $this->dbo->connect();
        }
    }

    public function connect()
    {
        $this->open();
    }

    public function connected()
    {
        if (method_exists($this->dbo, 'connected'))
        {
            return $this->dbo->connected();
        }

        return true;
    }

    public function escape($text, $extra = false)
    {
        return $this->dbo->escape($text, $extra);
    }

    public function execute()
    {
        if (method_exists($this->dbo, 'execute'))
        {
            return $this->dbo->execute();
        }

        return $this->dbo->execute();
    }

    public function getAffectedRows()
    {
        if (method_exists($this->dbo, 'getAffectedRows'))
        {
            return $this->dbo->getAffectedRows();
        }

        return 0;
    }

    public function getCollation()
    {
        if (method_exists($this->dbo, 'getCollation'))
        {
            return $this->dbo->getCollation();
        }

        return 'utf8_general_ci';
    }

    public function getConnection()
    {
        if (method_exists($this->dbo, 'getConnection'))
        {
            return $this->dbo->getConnection();
        }

        return null;
    }

    public function getCount()
    {
        if (method_exists($this->dbo, 'getCount'))
        {
            return $this->dbo->getCount();
        }

        return 0;
    }

    public function getDateFormat()
    {
        if (method_exists($this->dbo, 'getDateFormat'))
        {
            return $this->dbo->getDateFormat();
        }

        return 'Y-m-d H:i:s';;
    }

    public function getMinimum()
    {
        if (method_exists($this->dbo, 'getMinimum'))
        {
            return $this->dbo->getMinimum();
        }

        return '5.0.40';
    }

    public function getNullDate()
    {
        if (method_exists($this->dbo, 'getNullDate'))
        {
            return $this->dbo->getNullDate();
        }

        return '0000-00-00 00:00:00';
    }

    public function getNumRows($cursor = null)
    {
        if (method_exists($this->dbo, 'getNumRows'))
        {
            return $this->dbo->getNumRows($cursor);
        }

        return 0;
    }

    public function getQuery($new = false)
    {
        if (method_exists($this->dbo, 'getQuery'))
        {
            return $this->dbo->getQuery($new);
        }

        return null;
    }

    public function getTableColumns($table, $typeOnly = true)
    {
        if (method_exists($this->dbo, 'getTableColumns'))
        {
            return $this->dbo->getTableColumns($table, $typeOnly);
        }

        $result = $this->dbo->getTableFields(array($table), $typeOnly);

        return $result[$table];
    }

    public function getTableKeys($tables)
    {
        if (method_exists($this->dbo, 'getTableKeys'))
        {
            return $this->dbo->getTableKeys($tables);
        }

        return array();
    }

    public function getTableList()
    {
        if (method_exists($this->dbo, 'getTableList'))
        {
            return $this->dbo->getTableList();
        }

        return array();
    }

    public function getVersion()
    {
        if (method_exists($this->dbo, 'getVersion'))
        {
            return $this->dbo->getVersion();
        }

        return '5.0.40';
    }

    public function insertid()
    {
        if (method_exists($this->dbo, 'insertid'))
        {
            return $this->dbo->insertid();
        }

        return null;
    }

    public function insertObject($table, &$object, $key = null)
    {
        if (method_exists($this->dbo, 'insertObject'))
        {
            return $this->dbo->insertObject($table, $object, $key);
        }

        return null;
    }

    public function loadAssoc()
    {
        if (method_exists($this->dbo, 'loadAssoc'))
        {
            return $this->dbo->loadAssoc();
        }

        return null;
    }

    public function loadAssocList($key = null, $column = null)
    {
        if (method_exists($this->dbo, 'loadAssocList'))
        {
            return $this->dbo->loadAssocList($key, $column);
        }

        return null;
    }

    public function loadObject($class = 'stdClass')
    {
        if (method_exists($this->dbo, 'loadObject'))
        {
            return $this->dbo->loadObject($class);
        }

        return null;
    }

    public function loadObjectList($key = '', $class = 'stdClass')
    {
        if (method_exists($this->dbo, 'loadObjectList'))
        {
            return $this->dbo->loadObjectList($key, $class);
        }

        return null;
    }

    public function loadResult()
    {
        if (method_exists($this->dbo, 'loadResult'))
        {
            return $this->dbo->loadResult();
        }

        return null;
    }

    public function loadRow()
    {
        if (method_exists($this->dbo, 'loadRow'))
        {
            return $this->dbo->loadRow();
        }

        return null;
    }

    public function loadRowList($key = null)
    {
        if (method_exists($this->dbo, 'loadRowList'))
        {
            return $this->dbo->loadRowList($key);
        }

        return null;
    }

    public function lockTable($tableName)
    {
        if (method_exists($this->dbo, 'lockTable'))
        {
            return $this->dbo->lockTable($this);
        }

        return $this;
    }

    public function quote($text, $escape = true)
    {
        if (method_exists($this->dbo, 'quote'))
        {
            return $this->dbo->quote($text, $escape);
        }

        return $text;
    }

    public function select($database)
    {
        if (method_exists($this->dbo, 'select'))
        {
            return $this->dbo->select($database);
        }

        return false;
    }

    public function setQuery($query, $offset = 0, $limit = 0)
    {
        if (method_exists($this->dbo, 'setQuery'))
        {
            return $this->dbo->setQuery($query, $offset, $limit);
        }

        return false;
    }

    public function transactionCommit($toSavepoint = false)
    {
        if (method_exists($this->dbo, 'transactionCommit'))
        {
            $this->dbo->transactionCommit($toSavepoint);
        }
    }

    public function transactionRollback($toSavepoint = false)
    {
        if (method_exists($this->dbo, 'transactionRollback'))
        {
            $this->dbo->transactionRollback($toSavepoint);
        }
    }

    public function transactionStart($asSavepoint = false)
    {
        if (method_exists($this->dbo, 'transactionStart'))
        {
            $this->dbo->transactionStart($asSavepoint);
        }
    }

    public function unlockTables()
    {
        if (method_exists($this->dbo, 'unlockTables'))
        {
            return $this->dbo->unlockTables();
        }

        return $this;
    }

    public function updateObject($table, &$object, $key, $nulls = false)
    {
        if (method_exists($this->dbo, 'updateObject'))
        {
            return $this->dbo->updateObject($table, $object, $key, $nulls);
        }

        return false;
    }

    public function getLog()
    {
        if (method_exists($this->dbo, 'getLog'))
        {
            return $this->dbo->getLog();
        }

        return array();
    }

    public function dropTable($table, $ifExists = true)
    {
        if (method_exists($this->dbo, 'dropTable'))
        {
            return $this->dbo->dropTable($table, $ifExists);
        }

        return $this;
    }

    public function getTableCreate($tables)
    {
        if (method_exists($this->dbo, 'getTableCreate'))
        {
            return $this->dbo->getTableCreate($tables);
        }

        return array();
    }

    public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
    {
        if (method_exists($this->dbo, 'renameTable'))
        {
            return $this->dbo->renameTable($oldTable, $newTable, $backup, $prefix);
        }

        return $this;
    }

    public function setUtf()
    {
        if (method_exists($this->dbo, 'setUtf'))
        {
            return $this->dbo->setUtf();
        }

        return false;
    }


    protected function freeResult($cursor = null)
    {
        return false;
    }

    /**
     * Method to get an array of values from the <var>$offset</var> field in each row of the result set from
     * the database query.
     *
     * @param   integer  $offset  The row offset to use to build the result array.
     *
     * @return  mixed    The return value or null if the query failed.
     *
     * @since   11.1
     * @throws  RuntimeException
     */
    public function loadColumn($offset = 0)
    {
        if (method_exists($this->dbo, 'loadColumn'))
        {
            return $this->dbo->loadColumn($offset);
        }

        return $this->dbo->loadResultArray($offset);
    }

    /**
     * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
     * risks and reserved word conflicts.
     *
     * @param   mixed  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in quotes.
     *                        Each type supports dot-notation name.
     * @param   mixed  $as    The AS query part associated to $name. It can be string or array, in latter case it has to be
     *                        same length of $name; if is null there will not be any AS part for string or array element.
     *
     * @return  mixed  The quote wrapped name, same type of $name.
     *
     * @since   11.1
     */
    public function quoteName($name, $as = null)
    {
        if (is_string($name))
        {
            $quotedName = $this->quoteNameStr(explode('.', $name));

            $quotedAs = '';

            if (!is_null($as))
            {
                settype($as, 'array');
                $quotedAs .= ' AS ' . $this->quoteNameStr($as);
            }

            return $quotedName . $quotedAs;
        }
        else
        {
            $fin = array();

            if (is_null($as))
            {
                foreach ($name as $str)
                {
                    $fin[] = $this->quoteName($str);
                }
            }
            elseif (is_array($name) && (count($name) == count($as)))
            {
                $count = count($name);

                for ($i = 0; $i < $count; $i++)
                {
                    $fin[] = $this->quoteName($name[$i], $as[$i]);
                }
            }

            return $fin;
        }
    }

    /**
     * Quote strings coming from quoteName call.
     *
     * @param   array  $strArr  Array of strings coming from quoteName dot-explosion.
     *
     * @return  string  Dot-imploded string of quoted parts.
     *
     * @since 11.3
     */
    protected function quoteNameStr($strArr)
    {
        $parts = array();
        $q = $this->nameQuote;

        foreach ($strArr as $part)
        {
            if (is_null($part))
            {
                continue;
            }

            if (strlen($q) == 1)
            {
                $parts[] = $q . $part . $q;
            }
            else
            {
                $parts[] = $q[0] . $part . $q[1];
            }
        }

        return implode('.', $parts);
    }

    /**
     * Gets the error message from the database connection.
     *
     * @param   boolean  $escaped  True to escape the message string for use in JavaScript.
     *
     * @return  string  The error message for the most recent query.
     *
     * @since   11.1
     */
    public function getErrorMsg($escaped = false)
    {
        if (method_exists($this->dbo, 'getErrorMsg'))
        {
            $errorMessage = $this->dbo->getErrorMsg();
        }
        else
        {
            $errorMessage = $this->errorMsg;
        }

        if ($escaped)
        {
            return addslashes($errorMessage);
        }

        return $errorMessage;
    }

    /**
     * Gets the error number from the database connection.
     *
     * @return      integer  The error number for the most recent query.
     *
     * @since       11.1
     * @deprecated  13.3 (Platform) & 4.0 (CMS)
     */
    public function getErrorNum()
    {
        if (method_exists($this->dbo, 'getErrorNum'))
        {
            $errorNum = $this->dbo->getErrorNum();
        }
        else
        {
            $errorNum = $this->getErrorNum;
        }

        return $errorNum;
    }

    /**
     * Return the most recent error message for the database connector.
     *
     * @param   boolean  $showSQL  True to display the SQL statement sent to the database as well as the error.
     *
     * @return  string  The error message for the most recent query.
     */
    public function stderr($showSQL = false)
    {
        if (method_exists($this->dbo, 'stderr'))
        {
            return $this->dbo->stderr($showSQL);
        }

        return parent::stderr($showSQL);
    }

    /**
     * Magic method to proxy all calls to the loaded database driver object
     */
    /*public function __call($name, array $arguments)
    {
        if (is_null($this->dbo))
        {
            throw new Exception('F0F database driver is not loaded');
        }

        if (method_exists($this->dbo, $name) || in_array($name, array('q', 'nq', 'qn', 'query')))
        {
            switch ($name)
            {
                case 'execute':
                    $name = 'query';
                    break;

                case 'q':
                    $name = 'quote';
                    break;

                case 'qn':
                case 'nq':
                    switch (count($arguments))
                    {
                        case 0 :
                            $result = $this->quoteName();
                            break;
                        case 1 :
                            $result = $this->quoteName($arguments[0]);
                            break;
                        case 2:
                        default:
                            $result = $this->quoteName($arguments[0], $arguments[1]);
                            break;
                    }
                    return $result;

                    break;
            }

            switch (count($arguments))
            {
                case 0 :
                    $result = $this->dbo->$name();
                    break;
                case 1 :
                    $result = $this->dbo->$name($arguments[0]);
                    break;
                case 2:
                    $result = $this->dbo->$name($arguments[0], $arguments[1]);
                    break;
                case 3:
                    $result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2]);
                    break;
                case 4:
                    $result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                    break;
                case 5:
                    $result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
                    break;
                default:
                    // Resort to using call_user_func_array for many segments
                    $result = call_user_func_array(array($this->dbo, $name), $arguments);
            }

            if (class_exists('JDatabase') && is_object($result) && ($result instanceof JDatabase))
            {
                return $this;
            }

            return $result;
        }
        else
        {
            throw new \Exception('Method ' . $name . ' not found in F0FDatabase');
        }
    }*/
    function getPrefix()
    {
        return $this->dbo->getPrefix(); // TODO: Change the autogenerated stub
    }

    public function __get($name)
    {

        if (isset($this->dbo->$name) || property_exists($this->dbo, $name))
        {
            if($name == 'tablePrefix'){
                return $this->dbo->getPrefix();
            }
            return $this->dbo->$name;
        }
        else
        {
            $this->dbo->$name = null;
            user_error('Database driver does not support property ' . $name);
        }
    }

    public function __set($name, $value)
    {
        if (isset($this->dbo->$name) || property_exists($this->dbo, $name))
        {
            $this->dbo->$name = $value;
        }
        else
        {
            $this->dbo->$name = null;
            user_error('Database driver not support property ' . $name);
        }
    }


    protected function prepareStatement(string $query): \Joomla\Database\StatementInterface
    {

    }

    protected function fetchArray($cursor = null){

    }

    protected function fetchAssoc($cursor = null){

    }

    protected function fetchObject($cursor = null, $class = 'stdClass'){

    }

    public function isConnectionEncryptionSupported(): bool
    {

    }
}
