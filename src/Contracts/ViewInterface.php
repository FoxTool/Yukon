<?php

namespace FoxTool\Yukon\Contracts;

interface ViewInterface {
    public static function make(string $template, array $data);
}
