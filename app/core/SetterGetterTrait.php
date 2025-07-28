<?php

namespace DevNoKage;

/**
 * Trait SetterGetterTrait
 * Génère automatiquement les getters et setters pour les propriétés de classe
 */
trait SetterGetterTrait
{
    /**
     * Méthode magique pour définir les valeurs des propriétés
     */
    public function __set($name, $value)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }
        
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return $this;
        }
        
        throw new \Exception("Propriété $name non trouvée dans " . get_class($this));
    }

    /**
     * Méthode magique pour récupérer les valeurs des propriétés
     */
    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        
        throw new \Exception("Propriété $name non trouvée dans " . get_class($this));
    }

    /**
     * Méthode magique pour appeler les getters et setters
     */
    public function __call($method, $args)
    {
        // Gérer les getters
        if (strpos($method, 'get') === 0 && strlen($method) > 3) {
            $property = lcfirst(substr($method, 3));
            if (property_exists($this, $property)) {
                return $this->$property;
            }
        }
        
        // Gérer les setters
        if (strpos($method, 'set') === 0 && strlen($method) > 3 && count($args) === 1) {
            $property = lcfirst(substr($method, 3));
            if (property_exists($this, $property)) {
                $this->$property = $args[0];
                return $this;
            }
        }
        
        // Gérer les méthodes is/has pour les booléens
        if ((strpos($method, 'is') === 0 || strpos($method, 'has') === 0) && strlen($method) > 2) {
            $property = lcfirst(substr($method, strpos($method, 'is') === 0 ? 2 : 3));
            if (property_exists($this, $property)) {
                return (bool) $this->$property;
            }
        }
        
        throw new \Exception("Méthode $method non trouvée dans " . get_class($this));
    }

    /**
     * Génère un tableau associatif des propriétés publiques et protégées
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        
        $result = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);
            
            // Gérer les objets DateTime
            if ($value instanceof \DateTime) {
                $result[$property->getName()] = $value->format('Y-m-d H:i:s');
            } else {
                $result[$property->getName()] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Remplit l'objet à partir d'un tableau associatif
     */
    public function fromArray(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        
        return $this;
    }
}
