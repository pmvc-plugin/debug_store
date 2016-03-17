<?php
namespace PMVC\PlugIn\debug_store;
use PMVC\Event;
use PMVC as p;

// \PMVC\l(__DIR__.'/xxx.php');

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\debug_store';

class debug_store extends \PMVC\PlugIn
{
    private $store = [];

    public function init()
    {
        p\call_plugin(
            'dispatcher',
            'attach',
            [
                $this,
                Event\B4_PROCESS_FORWARD,
            ]
        );
        p\call_plugin(
            'dispatcher',
            'attach',
            [
                $this,
                Event\FINISH,
            ]
        );
    }

    public function onFinish()
    {
        if (!empty($this->store)) {
            $c = \PMVC\getC();
            $error = $c->getMapping()->findForward('debug');
            $error->set('debugs', $this->store);
            $this->store = null;
            p\call_plugin(
                'dispatcher',
                'stop',
                [false]
            );
            $c->processForward($error);
        }
    }

    public function onB4ProcessForward()
    {
        if (!empty($this->store)) {
            p\call_plugin(
                'dispatcher',
                'stop',
                [true]
            );
        }
    }

    public function escape($s) { return $s; }

    public function dump($p, $type='info')
    {
        $this->store[] = [$p, $type];
    }
}
