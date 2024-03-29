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
    private $_queue;

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
        $this->_queue = new HashMap();
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
        $data =  ['debugs' => [$a]];
        if (!empty($view)) {
            $view->append($data);
        } else {
            $this->_queue[[]] = $data;
        } 
        if ($this->_store) {
            $this->_store[[]] = $data;
        }
    }

    private function _reset()
    {
        if ($this->_store) {
            unset($this->_store[null]);
        }
        unset($this->_queue[null]);
    } 

    public function onFinish()
    {
        $view = $this->_getView();
        if (empty($view) && \PMVC\exists('view', 'plug')) {
            $view = \PMVC\plug('view');
        }
        if (empty($view)) {
            // echo directly, because no view here
            return $this->__dump(\PMVC\get($this->_queue));
        }
        if (count($this->_queue)) {
            $view->append(\PMVC\get($this->_queue));
        }
        if (!empty($view->get('debugs'))) {
            $c = p\plug('controller');
            $mapping = $c->getMappings();
            if (!$mapping->forwardExists('debug')) {
                // echo directly, because no view here
                $this->__dump('Can\'t find debug forward.');
                $this->__dump(\PMVC\get($this->_queue));
                return;
            } else {
                $debug = $mapping->findForward('debug');
                p\callPlugin('dispatcher', 'stop', [false]);
                $c->processForward($debug);
            }
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

    public function escape($string, $type = null)
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
