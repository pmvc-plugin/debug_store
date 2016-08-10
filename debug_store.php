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
    private $_view;

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
        $view = $this->_getView();
        if (!empty($view->get('debugs'))
            && 'redirect' === $c[_FORWARD]->getType()
            ) {
            p\callPlugin(
                'dispatcher',
                'stop',
                [true]
            );
        }
    }

    private function _getView()
    {
        if (!$this->_view) {
            $this->_view = p\plug('view');
        }
        return $this->_view;
    }

    private function _appendToView(array $a)
    {
        $view = $this->_getView();
        if (!empty($view)) {
            $view->append(['debugs'=>[$a]]);
        } else {
            print_r($a);
        }
    }

    public function onFinish()
    {
        $view = $this->_getView();
        if (empty(\PMVC\plug('view'))) {
            print_r($view->get('debugs'));
            return;
        }
        if (!empty($view->get('debugs'))) {
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
