<?php
/**
 * Copyright (c) 2007-2009 Gregory Meiste
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Poll
 * @author Greg Meiste <greg.meiste+github@gmail.com>
 */

if (!defined('PHPWS_SOURCE_DIR'))
{
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('poll', 'user.php');

/* mod_rewrite support */
if (isset($_GET['var1']) && is_numeric($_GET['var1']))
{
    $_REQUEST['user'] = 'result';
    $_REQUEST['poll_id'] = $_GET['var1'];
}

if (isset($_REQUEST['user']))
{
    Poll_User::action();
}
else
{
    PHPWS_Core::initModClass('poll', 'admin.php');
    Poll_Admin::action();
}

?>