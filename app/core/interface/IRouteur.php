<?php
namespace DevNoKage\Interface;

interface IRouteur {
    public static function resolve(): void;
    public static function setRoute(array $route): void;
}