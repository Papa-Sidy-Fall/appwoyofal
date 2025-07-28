<?php
namespace DevNoKage\Abstract;

use DevNoKage\SetterGetter;

abstract class AbstractEntity extends SetterGetter
{
    abstract public static function toObject(array $data): static;
    abstract public function toArray(): array;
    public function toJson(): string{
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}