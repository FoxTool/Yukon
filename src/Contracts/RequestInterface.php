<?php

namespace FoxTool\Yukon\Contracts;

interface RequestInterface
{
    public function headers($name);
    public function input(string $key, $default = null);
    public function filled(string $key): bool;
    public function has(string $key): bool;
    public function all(): array;
}
