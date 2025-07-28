<?php
namespace DevNoKage\Interface;

use PDO;

interface IDatabase {
    public function getConnexion(): PDO;
}