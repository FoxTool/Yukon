<?php

namespace FoxTool\Yukon\Core;

use FoxTool\Yukon\Contracts\ViewInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class View implements ViewInterface
{
    protected static $twig;

    public static function make(string $tamplate, array $data = [])
    {
        $templatesDir = __DIR__ . '/../../../../../resources/views';
        $fullTemplateName = "{$tamplate}.twig.php";

        $loader = new \Twig\Loader\FilesystemLoader($templatesDir);
        self::$twig = new \Twig\Environment($loader);

        if (file_exists($templatesDir . '/' . $fullTemplateName)) {
            echo self::$twig->render($fullTemplateName, $data);
        } else {
            echo "The template is not found!";
        }
    }
}