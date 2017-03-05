<?php

/**
 * hMailServer Autoreply Plugin for Roundcube
 *
 * @version 1.2
 * @author Andreas Tunberg <andreas@tunberg.com>
 *
 * Copyright (C) 2017, Andreas Tunberg
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

define('HMS_ERROR', 1);
define('HMS_CONNECT_ERROR', 2);
define('HMS_SUCCESS', 0);

/**
 * Change hMailServer autoreply plugin
 *
 * Plugin that adds functionality to change hMailServer autoreply message.
 * It provides common functionality and user interface and supports
 * several backends to finally update the autoreply message.
 *
 * For installation and configuration instructions please read the README file.
 *
 * @author Andreas Tunberg
 */
 
class hms_autoreply extends rcube_plugin
{
    public $task    = "settings";
    public $noframe = true;
    public $noajax  = true;
    private $rc;
    private $driver;

    function init()
    {
        
        $this->load_config();

        $this->add_texts('localization/');
        $this->include_stylesheet($this->local_skin_path() . '/hms_autoreply.css');

        $this->add_hook('settings_actions', array($this, 'settings_actions'));

        $this->register_action('plugin.autoreply', array($this, 'autoreply'));
        $this->register_action('plugin.autoreply-save', array($this, 'autoreply_save'));
    }

    function settings_actions($args)
    {
        $args['actions'][] = array(
            'action' => 'plugin.autoreply',
            'class'  => 'autoreply',
            'label'  => 'autoreply',
            'title'  => 'changeautoreply',
            'domain' => 'hms_autoreply'
        );

        return $args;
    }
    
    function autoreply_init()
    {
        $this->rc = rcube::get_instance();
        $this->load_config();
        $this->rc->output->set_pagetitle($this->gettext('changeautoreply'));
    }

    function autoreply()
    {
        $this->autoreply_init();
        
        $this->register_handler('plugin.body', array($this, 'autoreply_form'));


        $this->rc->output->send('plugin');
    }

    function autoreply_save()
    {
        $this->autoreply_init();

        $dataToSave = array(
            'action'      => 'autoreply_save',
            'enabled'     => rcube_utils::get_input_value('_enabled', rcube_utils::INPUT_POST),
            'subject'     => rcube_utils::get_input_value('_subject', rcube_utils::INPUT_POST),
            'message'     => rcube_utils::get_input_value('_message', rcube_utils::INPUT_POST, true),
            'expires'     => rcube_utils::get_input_value('_expires', rcube_utils::INPUT_POST),
            'expiresdate' => rcube_utils::get_input_value('_expiresdate', rcube_utils::INPUT_POST)
        );

        if (!($result = $this->_save($dataToSave))) {
            $this->rc->output->command('display_message', $this->gettext('successfullyupdated'), 'confirmation');
        }
        else {
            $this->rc->output->command('display_message', $result, 'error');
        }

        $this->register_handler('plugin.body', array($this, 'autoreply_form'));

        $this->rc->overwrite_action('plugin.autoreply');
        $this->rc->output->send('plugin');
    }

    function autoreply_form()
    {
        $currentData = $this->_load(array('action' => 'autoreply_load'));

        if (!is_array($currentData)) {
            if ($currentData == HMS_CONNECT_ERROR) {
                $error = $this->gettext('loadconnecterror');
            }
            else {
                $error = $this->gettext('loaderror');
            }

            $this->rc->output->command('display_message', $error, 'error');
            return;
        }

        $table = new html_table(array('cols' => 2));

        $field_id = 'enabled';
        $input_enabled = new html_checkbox(array (
                'name'  => '_enabled',
                'id'    => $field_id,
                'value' => 1
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('enabled'))));
        $table->add(null, $input_enabled->show($currentData['enabled']));

        $field_id = 'subject';
        $input_subject = new html_inputfield(array (
                'type'      => 'text',
                'name'      => '_subject',
                'id'        => $field_id,
                'maxlength' => 192
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('subject'))));
        $table->add(null, $input_subject->show($currentData['subject']));

        $field_id = 'message';
        $input_message = new html_textarea(array (
                'name' => '_message',
                'id'   => $field_id,
                'rows' => 5,
                'cols' => 50
        ));

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('message'))));
        $table->add(null, $input_message->show($currentData['message']));

        $field_id = 'expires';
        $input_expires = new html_checkbox(array (
                'name'  => '_expires',
                'id'    => $field_id,
                'value' => 1
        ));            
        $field2_id = 'expiresdate';
        $input_expiresdate = new html_inputfield(array (
                'name'      => '_expiresdate',
                'id'        => $field2_id,
                'title'     => 'YYYY-MM-DD',
                'size'      => 10,
                'maxlength' => 10
        ));
        $table->add('title', html::label($field_id, rcube::Q($this->gettext('expires'))));
        $table->add(null, $input_expires->show($currentData['expires']) . ' ' . $input_expiresdate->show($currentData['expiresdate']));		

        $submit_button = $this->rc->output->button(array(
                'command' => 'plugin.autoreply-save',
                'type'    => 'input',
                'class'   => 'button mainaction',
                'label'   => 'save'
        ));

        $form = $this->rc->output->form_tag(array(
            'id'     => 'autoreply-form',
            'name'   => 'autoreply-form',
            'method' => 'post',
            'action' => './?_task=settings&_action=plugin.autoreply-save',
        ), $table->show() . html::p(null, $submit_button));

        $out = html::div(array('class' => 'box'),
            html::div(array('id' => 'prefs-title', 'class' => 'boxtitle'), $this->gettext('changeautoreply'))
            . html::div(array('class' => 'boxcontent'),
                $form));

        $this->rc->output->add_gui_object('autoreplyform', 'autoreply-form');

        $this->include_script('hms_autoreply.js');

        return $out;
    }

    private function _load($data)
    {
        if (is_object($this->driver)) {
            $result = $this->driver->load($data);
        }
        elseif (!($result = $this->load_driver())){
            $result = $this->driver->load($data);
        }
        return $result;
    }

    private function _save($data, $response = false)
    {
        if (is_object($this->driver)) {
            $result = $this->driver->save($data);
        }
        elseif (!($result = $this->load_driver())){
            $result = $this->driver->save($data);
        }
        
        if ($response) return $result;

        switch ($result) {
            case HMS_SUCCESS:
                return;
            case HMS_CONNECT_ERROR:
                $reason = $this->gettext('updateconnecterror');
                break;
            case HMS_ERROR:
            default:
                $reason = $this->gettext('updateerror');
        }

        return $reason;
    }

    private function load_driver()
    {
        $config = rcmail::get_instance()->config;
        $driver = $config->get('hms_autoreply_driver', 'hmail');
        $class  = "rcube_{$driver}_autoreply";
        $file   = $this->home . "/drivers/$driver.php";

        if (!file_exists($file)) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "hms_autoreply plugin: Unable to open driver file ($file)"
            ), true, false);
            return HMS_ERROR;
        }

        include_once $file;

        if (!class_exists($class, false) || !method_exists($class, 'save') || !method_exists($class, 'load')) {
            rcube::raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "hms_autoreply plugin: Broken driver $driver"
            ), true, false);
            return $this->gettext('internalerror');
        }

        $this->driver = new $class;
    }
}
