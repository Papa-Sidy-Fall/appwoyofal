<?php
namespace DevNoKage\Interface;

use DevNoKage\Enums\ClassName;

interface IApp {
    public static function  getDependencie(ClassName $className): mixed;
}