<?php

/**
 * hMailserver remote autoreply driver
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

class rcube_hmail_remote_autoreply
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

        $hmailRemoteUrl = $rcmail->config->get('hms_autoreply_remote_url', false);
        if (!$hmailRemoteUrl) {
            rcube::write_log('errors', 'Plugin hms_autoreply (hmail remote driver): $config[\'hms_autoreply_remote_url\'] is not defined.');
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
                rcube::write_log('errors', 'Plugin hms_autoreply (hmail remote driver): $config[\'username_domain\'] is not defined.');
                return HMS_ERROR;
            }
            $username = $username . '@' . $domain;
        }

        $password = $rcmail->decrypt($_SESSION['password']);

        $dataToSend = $data;
        $dataToSend['email'] = $username;
        $dataToSend['password'] = $password;

        $result = $this->remote_access($hmailRemoteUrl, $dataToSend);

        if(!is_array($result)) {
            rcube::write_log('errors', 'Plugin hms_autoreply (hmail remote driver): ' . $result);
            return HMS_CONNECT_ERROR;
        }
        elseif($result['error'] != HMS_SUCCESS) {
            rcube::write_log('errors', 'Plugin hms_autoreply (hmail remote driver): ' . $result['text']);
            return $result['error'];
        }

        return $result['text'];
    }

    private function remote_access($url, $data)
    {
        $data_string = http_build_query($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $response = curl_exec($ch);

        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:  # OK
                    $return = unserialize($response);
                    break;
                default:
                    $return = 'Unexpected HTTP code: ' . $http_code . ' ' . strip_tags($response);
            }
        }
        else
            $return = 'Curl error: ' . curl_error($ch);

        curl_close($ch);
        return $return;
    }
}
