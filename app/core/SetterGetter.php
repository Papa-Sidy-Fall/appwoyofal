<?php
namespace DevNoKage;

use Exception;

class SetterGetter {
    
    public function __set($name, $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return $this;
        } else {
            throw new Exception("Propriété $name non trouvée");
        }
    }

    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new Exception("Propriété $name non trouvée");
    }
}