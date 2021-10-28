<?php

namespace GoldbachAlgorithms\SymfonyEntityExporter;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SymfonyEntityExporterAdmin extends SymfonyEntityExporterValidator{

    const TRANSITORY_MEMORY = 'TRANSITORY_MEMORY';
    const DEFAULT_TRANSITORY_MEMORY = '1GB';
    const ENV_NOT_DEFINED = "is not defined into .env";
    const __toString = "__toString";
    const __getId = "getId";
    const DEFAULT_FIELDS = [
        'IsVerified' => 'isVerified'
    ];

    public function transitoryMemory()
    {
        $transitoryMemory = self::DEFAULT_TRANSITORY_MEMORY;
        if (isset($_ENV[self::TRANSITORY_MEMORY]) && !empty($_ENV[self::TRANSITORY_MEMORY])) {
            $transitoryMemory = $_ENV(self::TRANSITORY_MEMORY);
        }        
        ini_set('memory_limit', $transitoryMemory);
    }

    public function env($parameter)
    {
        if (isset($_ENV[$parameter]) && !empty($_ENV[$parameter])) {
            return $_ENV[$parameter];
        } else {
            throw new BadRequestException(
                "GoldbachAlgorithmsError: " . $parameter . self::ENV_NOT_DEFINED
            );
        }
    }

    public function str_to_camel($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    public function getMethods($entityPath)
    {
        $entityPathString = (string) $entityPath;
        $class = new $entityPathString;

        $reflectionObject = new \ReflectionObject($class);
        $getMethods = $reflectionObject->getMethods();

        $methods = [];

        foreach ($getMethods as $method) {
            $methods[] = $method->getName();
        }

        return $methods;
    }

    public function hasToString($entityPath)
    {
        $eMethods = $this->getMethods($entityPath);
        $toString = in_array(self::__toString, $eMethods);
        return $toString;
    }

    public function hasId($entityPath)
    {
        $eMethods = $this->getMethods($entityPath);
        $hasId = in_array(self::__getId, $eMethods);
        return $hasId;
    }

    public function isDefault($field)
    {
        return array_key_exists($field, self::DEFAULT_FIELDS);
    }
    
}
