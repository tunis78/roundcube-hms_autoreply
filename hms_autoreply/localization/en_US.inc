<?php

$labels = array();

$labels['autoreply'] = 'Autoreply';
$labels['changeautoreply'] = 'Change Autoreply';

$labels['enabled'] = 'Enabled';
$labels['subject'] = 'Subject';
$labels['message'] = 'Message';
$labels['expires'] = 'Expires';

$messages = array();
$messages['successfullyupdated'] = 'Autoreply has successfully update.';
$messages['internalerror'] = 'Could not update autoreply.';
$messages['connecterror'] = $messages['internalerror'] . ' Connection error.';
$messages['loadinternalerror'] = 'Could not load autoreply.';
$messages['loadconnecterror'] = $messages['loadinternalerror'] . ' Connection error.';
