<?php
/**
 * Debug Store use with view engine.
 */

namespace PMVC\PlugIn\debug;

use PMVC\Event;
use PMVC\HashMap;
use PMVC as p;
use UnderflowException;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__ . '\debug_store';
p\initPlugin([
    'debug' => null,
    'controller' => null,
]);

/**
 * @parameters numeric level Debug dump level
 */
class debug_store extends p\PlugIn implements DebugDumpInterface
{
    private $_view;
    private $_store;

    public function init()
    {
        p\callPlugin('dispatcher', 'attach', [
            $this,
            Event\SET_CONFIG . '_' . _FORWARD,
        ]);
        p\callPlugin('dispatcher', 'attach', [$this, Event\FINISH]);
        if (!isset($this['level'])) {
            $this['level'] = 'trace';
        }
        if (false !== $this['keep']) {
            $this->_store = new HashMap();
        }
    }

    public function onSetConfig__forward_()
    {
        $c = p\plug('controller');
        $view = $this->_getView();
        if (
            !empty($view->get('debugs')) &&
            'redirect' === $c[_FORWARD]->getType()
        ) {
            p\callPlugin('dispatcher', 'stop', [true]);
        }
    }

    private function _getView()
    {
        if (!$this->_view && \PMVC\exists('view', 'plugin')) {
            $this->_view = p\plug('view');
        }
        return $this->_view;
    }

    private function _appendToView(array $a)
    {
        $view = $this->_getView();
        if (!empty($view)) {
            $view->append(['debugs' => [$a]]);
        }
        if ($this->_store) {
            $this->_store[[]] = ['debugs' => [$a]];
        }
    }

    private function _reset()
    {
        if ($this->_store) {
            $this->_store = new HashMap();
        }
    } 

    public function onFinish()
    {
        $view = $this->_getView();
        if (empty($view)) {
            // echo directly, because no view here
            return $this->__dump(\PMVC\get($this->_store));
        }
        if (!empty($view->get('debugs'))) {
            $c = p\plug('controller');
            $mapping = $c->getMappings();
            if (!$mapping->forwardExists('debug')) {
                // echo directly, because no view here
                $this->__dump('Can\'t find debug forward.');
                return;
            }
            $debug = $mapping->findForward('debug');
            p\callPlugin('dispatcher', 'stop', [false]);
            $c->processForward($debug);
        }
        $this->_reset();
    }

    public function toArray()
    {
        $data = null;
        if ($this->_store) {
            $data = \PMVC\get($this->_store);
            $this->_reset();
        }
        return $data;
    }

    public function escape($string)
    {
        return \PMVC\plug('utf8')->toUtf8($string);
    }

    private function __dump($p)
    {
        if (!empty($p)) {
            print_r($p);
        }
        $this->_reset();
    }

    public function dump($p, $type = 'debug')
    {
        if (p\plug('debug')->isShow($type, $this['level'])) {
            $this->_appendToView([$type, $p]);
        }
    }
}
