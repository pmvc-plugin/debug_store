<?php
namespace PMVC\PlugIn\debug;
use PMVC\Event;
use PMVC as p;
${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\debug_store';
p\initPlugin([
    'debug'=>null,
    'controller'=>null
]);

class debug_store
    extends p\PlugIn
    implements DebugDumpInterface
{
    private $store = [];

    public function init()
    {
        p\callPlugin(
            'dispatcher',
            'attach',
            [
                $this,
                Event\B4_PROCESS_FORWARD,
            ]
        );
        p\callPlugin(
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
            $c = \PMVC\plug('controller');
            $error = $c->getMapping()->findForward('debug');
            if (!$error) {
                return false;
            }
            $error->set('debugs', $this->store);
            $this->store = null;
            p\callPlugin(
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
            p\callPlugin(
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
