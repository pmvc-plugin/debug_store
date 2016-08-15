<?php
PMVC\Load::plug();
PMVC\addPlugInFolders(['../']);
class Debug_storeTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'debug_store';

    function setup()
    {
        $c = \PMVC\plug('controller',[
            _VIEW_ENGINE=>'json'
        ]);
        $b = new \PMVC\MappingBuilder(); 
        $b->addForward('debug', [_TYPE=>'view']);
        $c->addMapping($b);
        \PMVC\plug('view',[_CLASS=>__NAMESPACE__.'\FakeView']);
    }

    function teardown()
    {
        \PMVC\unplug($this->_plug);
        \PMVC\unplug('view');
        \PMVC\unplug('controller');
    }

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
        \PMVC\d('fake'); 
        $debugStore->onFinish();
        $this->assertEquals(1,\PMVC\getOption('v'));
    }

    function testAppendDebugToView()
    {
        $v = \PMVC\plug('view');
        $debugStore=\PMVC\plug($this->_plug);
        $debugStore->dump('a');
        $debugStore->dump('b');
        $expected = [
            ['debug','a'],
            ['debug','b'],
        ];
        $this->assertEquals($expected,$v['debugs']);
    }
}

class FakeView extends \PMVC\PlugIn
{
    function process(){
        \PMVC\option('set','v',1);
    }
    function get(){
        return true;
    }
    function setThemeFolder(){}

    function appendView(array $arr){
        $this->append($arr); 
    }
}


