<?php
PMVC\Load::plug();
PMVC\addPlugInFolders(['../']);
class Debug_storeTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'debug_store';

    function setup()
    {
        \PMVC\plug('debug', ['output'=> $this->_plug]);
        $c = \PMVC\plug('controller',[
            _VIEW_ENGINE=>'json'
        ]);
        $b = new \PMVC\MappingBuilder(); 
        $b->addForward('debug', [_TYPE=>'view']);
        $c->addMapping($b);
        $view = \PMVC\plug('view',[_CLASS=>__NAMESPACE__.'\FakeView']);
    }

    function teardown()
    {
        \PMVC\unplug($this->_plug);
        \PMVC\unplug('view');
        \PMVC\unplug('controller');
        \PMVC\unplug('debug');
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

    function testGetDebugForward()
    {
        $debugStore=\PMVC\plug($this->_plug);
        \PMVC\d('fake'); 
        $debugStore->onFinish();
        $v = \PMVC\plug('view');
        $debugs = $v->get('debugs');
        $this->assertEquals($debugs['0']['0'], 'debug');
        $this->assertEquals($debugs['0']['1'], 'fake');
    }

    function testDebugForwardNotExists()
    {
        $c = \PMVC\plug('controller');
        $mapping = $c->getMappings();
        $b = new \PMVC\MappingBuilder(); 
        $key = \PMVC\ACTION_FORWARDS;
        $b[$key]['debug'] = null;
        $mapping->addByKey($key, $b);
        $debugStore=\PMVC\plug($this->_plug);
        \PMVC\d('fake'); 
        ob_start();
        $debugStore->onFinish();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Can\'t find debug forward.',$output);
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
        $this->assertEquals($expected,$v->get('debugs'));
    }
}

class FakeView extends \PMVC\PlugIn\view\ViewEngine
{
    function process(){
        \PMVC\option('set','v',1);
    }
}


