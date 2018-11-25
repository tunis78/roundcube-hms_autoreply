<?php

$labels = array();

$labels['autoreply'] = 'Autoreply';
$labels['changeautoreply'] = 'Change Autoreply';

$labels['enabled'] = 'Enabled';
$labels['subject'] = 'Subject';
$labels['message'] = 'Message';
$labels['expires'] = 'Expires';
$labels['expiresdate'] = 'Expire date';

$messages = array();
$messages['successfullyupdated'] = 'Autoreply has successfully update.';
$messages['internalerror'] = 'Error with selected action.';
//$messages['connecterror'] = $messages['internalerror'] . ' Connection error.';
$messages['loaderror'] = 'Could not load autoreply.';
$messages['loadconnecterror'] = $messages['loadinternalerror'] . ' Connection error.';
$messages['updateerror'] = 'Could not update autoreply.';
$messages['updateconnecterror'] = $messages['updateerror'] . ' Connection error.';
