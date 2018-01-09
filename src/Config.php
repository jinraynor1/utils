<?php


namespace Jinraynor1\Utils;


class Config
{
    function __construct($config_directory)
    {
        $this->config =  $this->readConfig($config_directory);
    }

    public function get( $path, $default = null)
    {
        $current = $this->config;

        $p = strtok($path, '.');

        while ($p !== false) {
            if (!isset($current[$p])) {
                return $default;
            }
            $current = $current[$p];
            $p = strtok('.');
        }

        return $current;
    }

    /**
     * Read config files from directory and returns array
     * @param $config_path
     * @param $extension
     * @return array
     */
    private function readConfig($config_path,$extension = 'php'){

        $ritit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($config_path), \RecursiveIteratorIterator::CHILD_FIRST);
        $config = array();

        foreach ($ritit as $splFileInfo) {

            if(pathinfo($splFileInfo->getFilename(), PATHINFO_EXTENSION)!=$extension){
                continue;
            }

            $path = $splFileInfo->isDir()
                ? array($splFileInfo->getFilename() => array())
                : array($splFileInfo->getBasename('.'.$extension)=>  include $splFileInfo->getRealPath());


            for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) {


                $path = array(
                    $ritit->getSubIterator($depth)->current()->getBasename('.'.$extension)   => $path);


            }
            $config = array_merge_recursive($config, $path);
        }

        return $config;
    }



}