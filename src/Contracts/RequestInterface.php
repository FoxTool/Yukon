<?php

namespace FoxTool\Yukon\Contracts;

interface RequestInterface
{
    public function headers($name);
    public function get($name);
}
