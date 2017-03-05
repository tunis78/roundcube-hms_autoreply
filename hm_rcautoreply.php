<?php

/**
 * hMailserver remote autoreply changer
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
 
$rc_remote_ip = 'YOUR ROUNDCUBE SERVER IP ADDRESS';

/*****************/

if($_SERVER['REMOTE_ADDR'] !== $rc_remote_ip)
{
	header('HTTP/1.0 403 Forbidden');
	exit('You are forbidden!');
}

define('HMS_ERROR', 1);
define('HMS_SUCCESS', 0);

if (empty($_POST['action']) || empty($_POST['email']) || empty($_POST['password']))
	sendResult('Required fields can not be empty.', HMS_ERROR);

$action = $_POST['action'];
$email = $_POST['email'];
$password = $_POST['password'];

try {
	$obApp = new COM("hMailServer.Application", NULL, CP_UTF8);
}
catch (Exception $e) {
	sendResult(trim(strip_tags($e->getMessage())), HMS_ERROR);
}
$temparr = explode('@', $email);
$domain = $temparr[1];
$obApp->Authenticate($email, $password);
try {
	$obAccount = $obApp->Domains->ItemByName($domain)->Accounts->ItemByAddress($email);

	switch($action){
		case 'autoreply_load':
			$adata = array(
				'enabled'     => $obAccount->VacationMessageIsOn,
				'subject'     => $obAccount->VacationSubject,
				'message'     => $obAccount->VacationMessage,
				'expires'     => $obAccount->VacationMessageExpires,
				'expiresdate' => substr($obAccount->VacationMessageExpiresDate, 0, 10)
			);
			sendResult($adata);
		case 'autoreply_save':
			$obAccount->VacationMessageIsOn        = isset($_POST['enabled']) ?: 0;
			$obAccount->VacationSubject            = $_POST['subject'];
			$obAccount->VacationMessage            = $_POST['message'];
			$obAccount->VacationMessageExpires     = isset($_POST['expires']) ?: 0;
			$obAccount->VacationMessageExpiresDate = $_POST['expiresdate'];
			$obAccount->Save();
			sendResult(HMS_SUCCESS);
	}
	sendResult('Action unknown', HMS_ERROR);
}
catch (Exception $e) {
	sendResult(trim(strip_tags($e->getMessage())), HMS_ERROR);
}

function sendResult($message, $error = 0)
{
	$out = array('error' => $error, 'text' => $message);
	exit(serialize($out));
}
