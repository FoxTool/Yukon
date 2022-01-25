<?php

namespace FoxTool\Yukon\Contracts;

interface HTTPResponse
{
    public function json($data);
    public function content($data);
}
