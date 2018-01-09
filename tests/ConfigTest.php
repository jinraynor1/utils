<?php

use Jinraynor1\Utils\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{

    public function testCanLoadConfigFiles()
    {

        $config = new Config(__DIR__ . '/config');
        $this->assertEquals($config->get('sample1.first_sample'),'success');
        $this->assertEquals($config->get('sample2.second_sample'),'success');

    }

}
