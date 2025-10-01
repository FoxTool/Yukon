<?php

namespace FoxTool\Yukon\Core;

class Container {
    private $bindings = [];
    private $instances = [];

    public function bind(string $abstract, $concrete = null) {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = $concrete;
    }

    public function resolve(string $abstract) {
        // If the instance already exists - return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;
        
        // If it's the callback
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // Create new instance throught the Reflection
        $reflector = new \ReflectionClass($concrete);

        // If the class doesn't have the constructor
        if (!$constructor = $reflector->getConstructor()) {
            return new $concrete;
        }

        // Get the constructor's parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            // If no type defined or it's a scalar type (int, string etc.)
            if (!$type || $type->isBuiltin()) {
                // Check if default value presents
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new Exception(
                        "Cannot resolve parameter '{$parameter->getName()}' in {$parameter->getDeclaringClass()->getName()}"
                    );
                }
                continue;
            }

            // Creating dependencies recursively
            $dependencies[] = $this->resolve($type->getName());
        }

        // Creating the object with all dependencies
        return $reflector->newInstanceArgs($dependencies);
    }
}
