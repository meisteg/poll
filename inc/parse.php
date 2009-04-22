<?php
/**
 * Poll
 *
 * See docs/CREDITS for copyright information
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
 * @author Greg Meiste <blindman1344 at users dot sourceforge dot net>
 * @version $Id: parse.php,v 1.1 2007/02/20 05:09:41 blindman1344 Exp $
 */

function poll_view($poll_id)
{
    $poll = new Poll((int)$poll_id);
    if (empty($poll->id))
    {
        return NULL;
    }
    $template['POLL'] = $poll->view(FALSE, FALSE);

    if (empty($template['POLL']))
    {
        return NULL;
    }
    else
    {
        return PHPWS_Template::process($template, 'poll', 'poll/embedded.tpl');
    }
}

?>