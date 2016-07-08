<?php
PMVC\Load::plug();
PMVC\addPlugInFolders(['../'], ['view_json'=>'view']);
class Debug_storeTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'debug_store';
    function testPlugin()
    {
        ob_start();
        print_r(PMVC\plug($this->_plug));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains($this->_plug,$output);
    }

    function testDumpAtFinish()
    {
        \PMVC\option('set','v',0);
        $debugStore=\PMVC\plug($this->_plug);
        \PMVC\plug('view',[_CLASS=>__NAMESPACE__.'\FakeView']);
        $b = new \PMVC\MappingBuilder(); 
        $b->addForward('debug', [_TYPE=>'view']);
        $c = \PMVC\plug('controller',[
            _VIEW_ENGINE=>'json'
        ]);
        $c->addMapping($b);
        \PMVC\d('fake'); 
        $debugStore->onFinish();
        $this->assertEquals(1,\PMVC\getOption('v'));
    }
}

class FakeView extends \PMVC\PlugIN
{
    function process(){
        \PMVC\option('set','v',1);
    }
    function append($k,$v){}
    function get(){
        return true;
    }
    function setThemeFolder(){}
}


