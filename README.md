hMailServer Autoreply Plugin for Roundcube
==========================================
Plugin that adds a possibility to change users autoreply using two
methods (drivers) via Settings/Autoreply tab.

Requirements
------------
- hMailServer with webserver and PHP >= 5.3.0
- Requires PHP COM (Windows only)
- jqueryui plugin

How to install
--------------
- Copy folder to *roundcube/plugin/hms_autoreply*
- Config *config.inc.php.dist* and save as *config.inc.php*
- Enable plugin in *roundcube/config/config.inc.php*

Drivers
-------
hMailServer Auto-reply plugin supports two change mechanisms which are handled
by included drivers. Just pass driver name in 'hms_autoreply_driver' option.

**hMailServer (hmail)**

Requires PHP COM (Windows only). For access to hMail server on remote host you'll
need to define 'hms_autoreply_remote_dcom' and 'hms_autoreply_remote_server'.
See config.inc.php.dist file for more info.

**hMailServer Remote (hmail_remote)**

Requires PHP COM (Windows only) on remote host.
See config.inc.php.dist file for more info.


Changelog
---------
Version 1.3 (2017-03-18)
- Some CSS and design change

Version 1.2 (2017-03-05)
- Rearrange code
- Fixed some typos

Version 1.1 (2017-03-03)
- Adapted to roundcube plugin repository

Version 1.0 (2017-02-24)
- First release

License
-------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see http://www.gnu.org/licenses/.