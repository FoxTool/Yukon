<?php

namespace FoxTool\Yukon\Contracts;

interface HTTPRequest
{
    public function headers($name);
    public function get($name);
}
