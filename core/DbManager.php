<?php

class DbManager {
    protected $connections = [];
    protected $repositoryConnectionMap = [];
    protected $repositories = [];

    public function connect($name, $params)
    {
        $params = array_merge([
            'dsn' => null,
            'user' => '',
            'password' => '',
            'options' => [],
        ], $params);

        $connection = new PDO(
            $params['dsn'],
            $params['user'],
            $params['password'],
            $params['options'],
        );

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->connections[$name] = $connection;
    }

    public function getConnection($name = null)
    {
        if (is_null($name)) {
            return current($this->connections);
        }

        return $this->connections[$name];
    }

    public function setRepositoryConnectionMap($repositoryName, $name)
    {
        $this->repositoryConnectionMap[$repositoryName] = $name;
    }

    public function getConnectionForRepository($repositoryName)
    {
        if (isset($this->repositoryConnectionMap[$repositoryName])) {
            $name = $this->repositoryConnectionMap[$repositoryName];
            $connection = $this->getConnection($name);
        } else {
            $connection = $this->getConnection();
        }

        return $connection;
    }

    public function get($repositoryName)
    {
        if (!isset($this->repositories[$repositoryName])) {
            $repositoryClass = "{$repositoryName}Repository";
            $connection = $this->getConnectionForRepository($repositoryName);

            $repositry = new $repositoryClass($connection);
            $this->repositories[$repositoryName] = $repositry;
        }

        return $this->repositories[$repositoryName];
    }

    public function __destruct()
    {
        foreach ($this->repositories as $repository) {
            unset($repository);
        }

        foreach ($this->connections as $con) {
            unset($con);
        }
    }
}