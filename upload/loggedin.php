<?php
/*
MCCodes FREE
loggedin.php Rev 1.1.0
Copyright (C) 2005-2012 Dabomstew

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

session_start();
require "global_func.php";

include "mysql.php";
require_once(dirname(__FILE__) . "/models/setting.php");

$GAME_NAME = Setting::get('GAME_NAME')->value;
if ($_SESSION['loggedin'] == 0)
{
    header("Location: login.php");
    exit;
}
$userid = $_SESSION['userid'];
require_once(dirname(__FILE__) . "/models/user.php");
$user = User::get($userid);
require "header.php";
$h = new Header();
$h->startheaders();

global $c;

check_level();
$h->userdata($user);
$h->menuarea();
print
        "<h1>You have logged on, {$user->username}!</h1>
<h2>Welcome back, your last visit was: $lv.</h2>";
$q = mysqli_query($c, "SELECT * FROM papercontent LIMIT 1");
$content = mysqli_result($q, 0);
print "{$GAME_NAME} Latest News:<br />
$content
";
$h->endpage();
