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
 * @version $Id: user.php,v 1.3 2007/10/14 23:30:05 blindman1344 Exp $
 */

class Poll_User
{
    function action()
    {
        if (isset($_REQUEST['poll_id']))
        {
            $poll = new Poll($_REQUEST['poll_id']);

            switch ($_REQUEST['user'])
            {
                case 'vote':
                    if (isset($_POST['option']))
                    {
                        $poll->voteForOption($_POST['option']);
                    }

                case 'results':
                default:
                    Layout::add($poll->results(), 'poll', 'poll_results', TRUE);
                    break;
            }
        }
    }

    function sendMessage($message, $command, $secure=TRUE)
    {
        $_SESSION['poll_message'] = $message;

        if (is_array($command))
        {
            PHPWS_Core::reroute(PHPWS_Text::linkAddress('poll', $command, $secure));
        }
        else if($secure)
        {
            PHPWS_Core::reroute(PHPWS_Text::linkAddress('poll', array('action'=>$command), TRUE));
        }
        PHPWS_Core::reroute(PHPWS_Text::linkAddress('poll', array('user'=>$command), FALSE));
    }

    function getMessage()
    {
        if (isset($_SESSION['poll_message']))
        {
            $message = $_SESSION['poll_message'];
            unset($_SESSION['poll_message']);
            return $message;
        }

        return NULL;
    }
}
?>