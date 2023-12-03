<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

class AYA_Option_Fired_callback extends AYA_Field_Action
{
    public function callback($field)
    {
        if (isset($field['function']) && is_callable($field['function'])) {

            $args = (isset($field['args'])) ? $field['args'] : null;

            return call_user_func($field['function'], $args);
        }
    }
}
