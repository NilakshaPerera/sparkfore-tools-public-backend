<?php

namespace App\Domain\DataClasses;

use Illuminate\Support\Str;

class AppDataClass
{
    public function toArray()
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);

        $data = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyValue = $this->$propertyName;
            // Transform camelCase to underscore_case
            $underscoreKey = Str::snake($propertyName);
            $data[$underscoreKey] = $propertyValue;
        }

        return $data;
    }
}
