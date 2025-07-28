<?php
namespace DevNoKage\Interface;

interface ISingleton {
    public static function getInstance(): static;
}