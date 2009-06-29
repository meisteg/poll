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

PHPWS_Core::initModClass('poll', 'poll.php');

class Poll_Runtime
{
    function show()
    {
        Poll_Runtime::showAllPolls();

        $key = Key::getCurrent();
        if (!empty($key) && !$key->isDummy(true))
        {
            Poll_Runtime::showPolls($key);
            Poll_Runtime::viewPinnedPolls($key);
        }
    }

    function showAllPolls()
    {
        $key = new Key;
        $key->id = -1;
        Poll_Runtime::showPolls($key);
    }

    function viewPinnedPolls($key)
    {
        if (isset($_SESSION['Pinned_Polls']))
        {
            $poll_list = &$_SESSION['Pinned_Polls'];
            if (!empty($poll_list))
            {
                foreach ($poll_list as $poll_id => $poll)
                {
                    if (!isset($GLOBALS['Current_Polls'][$poll_id]))
                    {
                        $poll->setPinKey($key);
                        $content[] = $poll->view(TRUE);
                    }
                }

                if (!empty($content))
                {
                    $complete = implode('', $content);
                    Layout::add($complete, 'poll', 'Poll_List');
                }
            }
        }
    }

    function showPolls($key)
    {
        $db = new PHPWS_DB('poll');
        $db->addWhere('poll_pins.key_id', $key->id);
        $db->addWhere('id', 'poll_pins.poll_id');
        Key::restrictView($db, 'poll');
        $result = $db->getObjects('Poll');

        if (!PHPWS_Error::logIfError($result) && !empty($result))
        {
            foreach ($result as $poll)
            {
                $poll->setPinKey($key);
                Layout::add($poll->view(), 'poll', $poll->getLayoutContentVar());
                $GLOBALS['Current_Polls'][$poll->id] = TRUE;
            }
        }
    }
}

?>