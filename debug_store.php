<?php
/**
 * Debug Store use with view engine.
 */

namespace PMVC\PlugIn\debug;

use PMVC\Event;
use PMVC as p;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\debug_store';
p\initPlugin([
    'debug'=>null,
    'controller'=>null
]);

/**
 * @parameters numeric level Debug dump level 
 */
class debug_store
    extends p\PlugIn
    implements DebugDumpInterface
{
    public function init()
    {
        p\callPlugin(
            'dispatcher',
            'attach',
            [
                $this,
                Event\SET_CONFIG.'_'._FORWARD,
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
        if (!isset($this['level'])) {
            $this['level'] = 'trace';
        }
    }

    public function onSetConfig__forward_()
    {
        $c = p\plug('controller');
        if (!empty($c->getErrorForward())
            || 'redirect' === $c[_FORWARD]->getType()
            ) {
            p\callPlugin(
                'dispatcher',
                'stop',
                [true]
            );
        }
    }

    private function _appendToView(array $a)
    {
        p\plug('view')->append('debugs', [$a]);
    }

    public function onFinish()
    {
        if (!empty(p\plug('view')->get('debugs'))) {
            $c = p\plug('controller');
            $debug = $c->getMapping()->findForward('debug');
            if (!$debug) {
                return false;
            }
            p\callPlugin(
                'dispatcher',
                'stop',
                [false]
            );
            $c->processForward($debug);
        }
    }

    public function escape($s) { return $s; }

    public function dump($p, $type='debug')
    {
        if (p\plug('debug')->isShow(
            $type,
            $this['level']
        )) {
            $this->_appendToView([$type, $p]);
        }
    }
}
