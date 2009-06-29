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

function poll_uninstall(&$content)
{
    PHPWS_DB::dropTable('poll');
    PHPWS_DB::dropTable('poll_pins');
    PHPWS_DB::dropTable('poll_options');
    PHPWS_DB::dropTable('poll_voted_ips');

    $content[] = dgettext('poll', 'Poll tables removed.');

    return TRUE;
}

?>