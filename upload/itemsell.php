<?php
/*
MCCodes FREE
itemsell.php Rev 1.1.0
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
include "mysql.php";
global $c;

$user->check_level();
$h->userdata($user);
$h->menuarea();
$_GET['ID'] = abs((int) $_GET['ID']);
$_GET['qty'] = abs((int) $_GET['qty']);
//itemsend
if ($_GET['qty'])
{
    $id = mysqli_query(
        $c,
        "SELECT iv.*,it.* FROM inventory iv LEFT JOIN items it ON iv.inv_itemid=it.itmid WHERE iv.inv_id={$_GET['ID']} AND iv.inv_userid=$userid LIMIT 1"
    );
    if (mysqli_num_rows($id) == 0)
    {
        print "Invalid item ID";
    }
    else
    {
        $r = mysqli_fetch_array($id);
        if ($_GET['qty'] > $r['inv_qty'])
        {
            print "You are trying to send more than you have!";
        }
        else
        {
            $price = $r['itmsellprice'] * $_GET['qty'];
            //are we sending it all
            if ($_GET['qty'] == $r['inv_qty'])
            {
                //just give them possession of the item
                mysqli_query(
                    $c,
                    "DELETE FROM inventory WHERE inv_id={$_GET['ID']}"
                );
            }
            else
            {
                //create seperate
                mysqli_query(
                    $c,
                    "UPDATE inventory SET inv_qty=inv_qty-{$_GET['qty']} WHERE inv_id={$_GET['ID']} LIMIT 1;"
                );
            }
            mysqli_query(
                $c,
                "UPDATE users SET money=money+{$price} WHERE userid=$userid"
            );
            $priceh = "$" . ($price);
            print "You sold {$_GET['qty']} {$r['itmname']}(s) for {$priceh}";
            mysqli_query(
                $c,
                "INSERT INTO itemselllogs VALUES(NULL, $userid, {$r['itmid']}, $price, {$_GET['qty']}, "
                        . time()
                        . ", '{$user->username} sold {$_GET['qty']} {$r['itmname']}(s) for {$priceh}')"
            );
        }
    }
}
else if ($_GET['ID'])
{
    $id = mysqli_query(
        $c,
        "SELECT iv.*,it.* FROM inventory iv LEFT JOIN items it ON iv.inv_itemid=it.itmid WHERE iv.inv_id={$_GET['ID']} and iv.inv_userid=$userid LIMIT 1"
    );
    if (mysqli_num_rows($id) == 0)
    {
        print "Invalid item ID";
    }
    else
    {
        $r = mysqli_fetch_array($id);
        print
                "<b>Enter how many {$r['itmname']} you want to sell. You have {$r['inv_qty']} to sell.</b><br />
<form action='itemsell.php' method='get'>
<input type='hidden' name='ID' value='{$_GET['ID']}' />
Quantity: <input type='text' name='qty' value='' /><br />
<input type='submit' value='Sell Items (no prompt so be sure!' /></form>";
    }
}
else
{
    print "Invalid use of file.";
}
$h->endpage();
