<?php
class Sphinx
{
    protected static $sphinx;
    protected static $conn;

    /**
     * @var array
     */
    protected $weights = array();

    protected $options = array(
        'host'     => '127.0.0.1',
        'port'     => 9306,
        'user'     => '',
        'password' => '',
    );

    private function __construct()
    { }

    /**
     * @return Sphinx
     */
    static public function get()
    {
        if (null === static::$sphinx) {
            static::$sphinx = new static;
        }
        return static::$sphinx;
    }

    public function setHost($host)
    {
        $this->options['host'] = $host;

        return $this;
    }

    public function setPort($port)
    {
        $this->options['port'] = $port;

        return $this;
    }

    /**
     * @param array $weights
     */
    public function setWeights($weights)
    {
        $this->weights = $weights;
    }

    /**
     * @todo: Нужно переписать, что бы в качестве PDO можно было передать что-то другое - DI
     * @return mixed
     */
    protected function connect()
    {
        if (null === static::$conn) {
            static::$conn = new \PDO(
                'mysql:host=' . $this->options['host'] . ';'.
                      'port=' . $this->options['port'] . ';',
                $this->options['user'],
                $this->options['password']
            );
        }
        return static::$conn;
    }

    /**
     * @param string $sql
     * @return string
     */
    protected function formatSQL($sql)
    {
        $weights = '';
        if (count($this->weights) > 0) {
            foreach($this->weights as $_key => $value) {
                $weights.= ',' . $_key . '=' . $value;
            }
            $weights = substr($weights, 1);
        }

        $sql.=
            ' OPTION ' .
            (!empty($weights) ? 'field_weights=(' . $weights . ')' : '');

        return $sql;
    }

    /**
     * @param string $sql
     * @param array $input_parameters
     * @param int $fetch_style
     *
     * @return array
     */
    public function fetchAll($sql, array $input_parameters = null, $fetch_style = PDO::FETCH_ASSOC)
    {
        /** @var \PDOStatement $sth */
        $sth = $this
            ->connect()
            ->prepare($this->formatSQL($sql));
        $sth->execute($input_parameters);
        return $sth->fetchAll($fetch_style);
    }

    /**
     * @param string $sql
     * @param array $input_parameters
     * @return array
     */
    public function fetchColumn($sql, array $input_parameters = null)
    {
        /** @var \PDOStatement $sth */
        $sth = $this
            ->connect()
            ->prepare($this->formatSQL($sql));
        $sth->execute($input_parameters);
        return $sth->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param string $sql
     * @param array $input_parameters
     * @return array
     */
    public function fetch($sql, array $input_parameters = null)
    {
        /** @var \PDOStatement $sth */
        $sth = $this
            ->connect()
            ->prepare($this->formatSQL($sql));
        $sth->execute($input_parameters);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
}