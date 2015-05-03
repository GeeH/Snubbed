<?php
/**
 * Created by Gary Hockin.
 * Date: 03/05/15
 * @GeeH
 */

namespace Snubbed;


class ViewSnubGenerator
{
    private static $fileWriter;
    /**
     * @var array
     */
    private static $paths;
    /**
     * @var array
     */
    private static $controllers;
    /**
     * @var array
     */
    private static $variables;
    /**
     * @var array
     */
    private static $helpers;
    /**
     * @var string
     */
    private static $viewPath = 'View';

    /**
     * @param FileWriter $fileWriter
     * @param array $paths
     * @param array $controllers
     * @param array $variables
     * @param array $helpers
     */
    public static function writeFiles(FileWriter $fileWriter, array $paths, array $controllers, array $variables, array $helpers)
    {
        self::$fileWriter  = $fileWriter;
        self::$paths       = $paths;
        self::$controllers = $controllers;
        self::$variables   = $variables;
        self::$helpers     = $helpers;

        self::goGoGo();
    }

    /**
     * GO GO GO!
     */
    private static function goGoGo()
    {
        self::writeViewMap();
        self::writeViewTemplateFiles();
    }

    /**
     * Dumps a view map that tells you which Controller/Action uses which View Template
     */
    private static function writeViewMap()
    {
        $path  = '.ide/Snubbed/' . self::$viewPath . '/class_map.php';
        $paths = var_export(self::$paths, true);
        $paths = '<?php' . PHP_EOL . $paths . ';' . PHP_EOL;
        self::$fileWriter->write($path, $paths);
    }

    /**
     * @return string
     */
    public static function getViewPath()
    {
        return self::$viewPath;
    }

    /**
     * @param string $viewPath
     */
    public static function setViewPath($viewPath)
    {
        self::$viewPath = $viewPath;
    }

    private static function writeViewTemplateFiles()
    {
        $generated = date("Y-m-d H:i:s");
        $viewPath = self::$viewPath;
        $snub      = <<<SNUB
<?php

/**
 * Generated on {$generated}
 */
namespace Snubbed\\{$viewPath};

/**

SNUB;

        foreach (self::$helpers as $name => $returns) {
            if (strpos($returns, '\\') !== 0) {
                $returns = '\\' . $returns;
            }
            $snub .= " * @method $returns $name()" . PHP_EOL;
        }

        foreach (self::$paths as $controller => $actions) {
            self::writeActionTemplates($controller, $actions, $snub);
        }

    }

    private static function writeActionTemplates($controller, array $actions, $snub)
    {
        foreach ($actions as $action => $path) {
            self::writeActionTemplate($controller, $action, $snub);
        }
    }

    private static function writeActionTemplate($controller, $action, $snub)
    {
        if(isset(self::$variables[$controller][$action])) {
            foreach (self::$variables[$controller][$action] as $variable => $type) {
                if (strpos($type, '\\') !== 0) {
                    $type = '\\' . $type;
                }
                $snub .= " * @property $type $variable" . PHP_EOL;
            }
        }

        $snub .= ' */' . PHP_EOL;

        $className = ucfirst(str_replace('\\', '', $controller)) . ucfirst($action);

        $snub .= "class $className extends \\Zend\\View\Renderer\\PhpRenderer {}" . PHP_EOL;

        $path = '.ide/Snubbed/' . self::$viewPath . '/' . $className . '.php';

        self::$fileWriter->write($path, $snub);

    }

}