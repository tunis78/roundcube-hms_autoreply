<?php

/**
 * hMailserver autoreply driver
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

class rcube_hmail_autoreply
{

    public function load($data)
    {
        return $this->data_handler($data);
    }

    public function save($data)
    {
        return $this->data_handler($data);
    }
    
    private function data_handler($data)
    {
        $rcmail = rcmail::get_instance();

        try {
            $remote = $rcmail->config->get('hms_autoreply_remote_dcom', false);
            if ($remote)
                $obApp = new COM("hMailServer.Application", $rcmail->config->get('hms_autoreply_remote_server'), CP_UTF8);
            else
                $obApp = new COM("hMailServer.Application", NULL, CP_UTF8);
        }
        catch (Exception $e) {
            rcube::write_log('errors', 'Plugin hms_autoreply (hmail driver): ' . trim(strip_tags($e->getMessage())));
            rcube::write_log('errors', 'Plugin hms_autoreply (hmail driver): This problem is often caused by DCOM permissions not being set.');
            return HMS_ERROR;
        }

        $username = $rcmail->user->data['username'];
        if (strstr($username, '@')){
            $temparr = explode('@', $username);
            $domain = $temparr[1];
        }
        else {
            $domain = $rcmail->config->get('username_domain', false);
            if (!$domain) {
                rcube::write_log('errors', 'Plugin hms_autoreply (hmail driver): $config[\'username_domain\'] is not defined.');
                return HMS_ERROR;
            }
            $username = $username . '@' . $domain;
        }

        $password = $rcmail->decrypt($_SESSION['password']);

        $obApp->Authenticate($username, $password);
        try {
            $obAccount = $obApp->Domains->ItemByName($domain)->Accounts->ItemByAddress($username);

            switch($data['action']){
                case 'autoreply_load':
                    $adata=array(
                        'enabled'     => $obAccount->VacationMessageIsOn ?: 0,
                        'subject'     => $obAccount->VacationSubject,
                        'message'     => $obAccount->VacationMessage,
                        'expires'     => $obAccount->VacationMessageExpires ?: 0,
                        'expiresdate' => substr($obAccount->VacationMessageExpiresDate, 0, 10)
                    );              
                    return $adata;
                case 'autoreply_save':
                    $obAccount->VacationMessageIsOn        = $data['enabled'] == null ? 0 : 1;
                    $obAccount->VacationSubject            = $data['subject'];
                    $obAccount->VacationMessage            = $data['message'];
                    $obAccount->VacationMessageExpires     = $data['expires'] == null ? 0 : 1;
                    $obAccount->VacationMessageExpiresDate = $data['expiresdate'];
                    $obAccount->Save();
                    return HMS_SUCCESS;
            }
            return HMS_ERROR;
        }
        catch (Exception $e) {
            rcube::write_log('errors', 'Plugin hms_autoreply (hmail driver): ' . trim(strip_tags($e->getMessage())));
            rcube::write_log('errors', 'Plugin hms_autoreply (hmail driver): This problem is often caused by Authenticate permissions.');
            return HMS_ERROR;
        }
    }
}
