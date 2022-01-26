<?php

namespace FoxTool\Yukon\Contracts;

interface ResponseInterface
{
    public function json($data);
    public function content($data);
}
