<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 事件调度器
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

/*
//简单调度器
class InitHook implements AYA_Event {
    public function execute() {
        echo "Initializing...\n";
    }
}

$hookSystem = new AYA_Event_Action();
$hookSystem->add('init', new InitHook());
$hookSystem->do('init');
*/

if (!class_exists('AYA_Event_Action')) {

    interface AYA_Event
    {
        public function action();
    }

    class AYA_Event_Action
    {
        private $listeners = [];

        public function add($event_name, $listener)
        {
            $this->listeners[$event_name][] = $listener;
        }

        public function do($event_name)
        {
            if (isset($this->listeners[$event_name])) {
                foreach ($this->listeners[$event_name] as $listener) {
                    $listener->action();
                }
            }
        }
    }
}
