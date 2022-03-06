<?php

abstract class DbRepository{
    protected $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function execute($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function fetch($sql, $params = [])
    {
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}