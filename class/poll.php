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

class Poll
{
    var $id             = 0;
    var $key_id         = 0;
    var $title          = NULL;
    var $question       = NULL;
    var $active         = 1;
    var $users_only     = POLL_DEFAULT_USERS_ONLY;
    var $allow_comments = POLL_DEFAULT_ALLOW_COMMENTS;
    var $created        = 0;
    var $_pin_key       = NULL;
    var $_options       = array();


    function Poll($id=NULL)
    {
        if (!empty($id))
        {
            $this->setId($id);

            $db = new PHPWS_DB('poll');
            $db->loadObject($this);
        }
    }

    function getLayoutContentVar()
    {
        return 'poll_' . $this->id;
    }

    function setId($id)
    {
        $this->id = (int)$id;
    }

    function getId()
    {
        return $this->id;
    }

    function setTitle($title)
    {
        $this->title = PHPWS_Text::parseInput(strip_tags($title));
    }

    function getTitle($format=TRUE)
    {
        if ($format)
        {
            return PHPWS_Text::parseOutput($this->title);
        }
        return $this->title;
    }

    function setQuestion($question)
    {
        $this->question = PHPWS_Text::parseInput($question);
    }

    function getQuestion($format=TRUE)
    {
        if ($format)
        {
            return PHPWS_Text::parseOutput($this->question);
        }
        return $this->question;
    }

    function getActive()
    {
        $active = dgettext('poll', 'Active');
        $inactive = dgettext('poll', 'Inactive');

        if (Current_User::allow('poll', 'hide_polls', $this->getId()))
        {
            $vars['poll_id'] = $this->getId();
            $vars['action'] = 'hidePoll';
            return PHPWS_Text::secureLink(($this->active ? $active : $inactive), 'poll', $vars);
        }

        return ($this->active ? $active : $inactive);
    }

    function setUsersOnly($users_only)
    {
        $this->users_only = (int)$users_only;
    }

    function getUsersOnly()
    {
        if ($this->users_only == 0)
        {
            return dgettext('poll', 'No');
        }
        return dgettext('poll', 'Yes');
    }

    function setAllowComments($allow_comments)
    {
        $this->allow_comments = (int)$allow_comments;
    }

    function getAllowComments()
    {
        if ($this->allow_comments == 0)
        {
            return dgettext('poll', 'No');
        }
        return dgettext('poll', 'Yes');
    }

    function setCreated($created)
    {
        $this->created = (int)$created;
    }

    function getCreated($format=POLL_DATE_FORMAT)
    {
        return strftime($format, PHPWS_Time::getUserTime($this->created));
    }

    function setPinKey($key)
    {
        $this->_pin_key = $key;
    }

    function storeOption($option)
    {
        $this->_options[] = PHPWS_Text::parseInput($option);
    }

    function getOptions()
    {
        $db = new PHPWS_DB('poll_options');
        $db->addWhere('poll_id', $this->getId());
        $db->addOrder('id');
        return $db->select();
    }

    function voteForOption($option_num)
    {
        if ($this->users_only && !Current_User::isLogged())
        {
            Poll_User::sendMessage(dgettext('poll', 'This vote is for registered users only, please log in to vote.'),
                                   array('user'=>'results', 'poll_id'=>$this->getId()), FALSE);
        }

        $values['poll_id'] = $this->getId();
        $values['ip'] = $_SERVER['REMOTE_ADDR'];
        $db_ip = new PHPWS_DB('poll_voted_ips');
        $db_ip->addWhere($values);
        $result = $db_ip->select();
        if ($result != NULL)
        {
            PHPWS_Error::logIfError($result);
            Poll_User::sendMessage(dgettext('poll', 'You have already voted!'),
                                   array('user'=>'results', 'poll_id'=>$this->getId()), FALSE);
        }
        $db_ip->resetWhere();
        $db_ip->addValue($values);
        $db_ip->insert();

        $options = $this->getOptions();
        if (!PHPWS_Error::logIfError($options))
        {
            $db = new PHPWS_DB('poll_options');
            $db->addWhere('id', $options[$option_num-1]['id']);
            $result = $db->incrementColumn('votes');
            if (!PHPWS_Error::logIfError($result))
            {
                Poll_User::sendMessage(dgettext('poll', 'Thank you for your vote.'),
                                       array('user'=>'results', 'poll_id'=>$this->getId()), FALSE);
            }
        }
    }

    function getKey()
    {
        $key = new Key($this->key_id);
        return $key;
    }

    function getTag()
    {
        return '[poll:' . $this->id . ']';
    }

    function getTotalNumberVotes($format=TRUE)
    {
        $db = new PHPWS_DB('poll_voted_ips');
        $db->addWhere('poll_id', $this->getId());
        $total = $db->count();

        if ($format)
        {
            if ($total <= 0)
            {
                return dgettext('poll', 'No votes');
            }
            elseif ($total == 1)
            {
                return dgettext('poll', '1 vote');
            }
            return sprintf(dgettext('poll', '%s votes'), number_format($total));
        }
        return $total;
    }

    function save($save_key=TRUE, $save_options=TRUE)
    {
        if (!Current_User::authorized('poll', 'edit_polls', ($this->getId() ? $this->id : NULL)))
        {
            Current_User::disallow();
            return;
        }

        $db = new PHPWS_DB('poll');
        $result = $db->saveObject($this);
        if (PEAR::isError($result))
        {
            return $result;
        }

        if ($save_key)
        {
            $result = $this->saveKey();
            if (PEAR::isError($result))
            {
                return $result;
            }
        }

        if ($save_options)
        {
            /* Clear any options that may have already been saved for this poll. */
            $this->clear('options');
            $this->clear('voted_ips');

            /* Save new options. */
            $db2 = new PHPWS_DB('poll_options');
            $db2->addValue('poll_id', $this->getId());
            $db2->addValue('votes', 0);
            foreach ($this->_options as $option)
            {
                $db2->addValue('name', $option);
                $db2->insert();
            }

            if ($this->allow_comments)
            {
                PHPWS_Core::initModClass('comments', 'Comments.php');
                $thread = Comments::getThread($this->key_id);
                $thread->allowAnonymous(POLL_DEFAULT_ALLOW_ANONYMOUS_COMMENTS);
                $thread->save();
            }
        }
    }

    function saveKey()
    {
        if (empty($this->key_id))
        {
            $key = new Key;
            $key->setModule('poll');
            $key->setItemName('poll');
            $key->setItemId($this->getId());
            $key->setUrl(PHPWS_Text::linkAddress('poll', array('user'=>'results', 'poll_id'=>$this->getId())));
        }
        else
        {
            $key = new Key($this->key_id);
        }

        $key->setEditPermission('edit_polls');
        $key->setTitle($this->getTitle());
        $result = $key->save();
        if (PEAR::isError($result))
        {
            return $result;
        }

        if (empty($this->key_id))
        {
            $this->key_id = $key->id;
            $this->save(FALSE, FALSE);
        }
    }

    function toggle()
    {
        if (!Current_User::authorized('poll', 'hide_polls', $this->id))
        {
            Current_User::disallow();
            return;
        }

        $this->active = ($this->active ? 0 : 1);
        return $this->save(FALSE, FALSE);
    }

    function clear($table)
    {
        $db = new PHPWS_DB('poll_'.$table);
        $db->addWhere('poll_id', $this->getId());
        $result = $db->delete();
        PHPWS_Error::logIfError($result);
    }

    function kill()
    {
        if (!Current_User::authorized('poll', 'delete_polls', $this->getId()))
        {
            Current_User::disallow();
            return;
        }

        $this->clear('pins');
        $this->clear('options');
        $this->clear('voted_ips');
        $db = new PHPWS_DB('poll');
        $db->addWhere('id', $this->getId());

        $result = $db->delete();
        PHPWS_Error::logIfError($result);

        $key = new Key($this->key_id);
        $result = $key->delete();
        PHPWS_Error::logIfError($result);
    }

    function view($pin_mode=FALSE, $admin_icon=TRUE)
    {
        if ($this->active && $this->getId())
        {
            $op_cnt = 1;
            $form = new PHPWS_Form;
            $form->addHidden('module', 'poll');
            $form->addHidden('user', 'vote');
            $form->addHidden('poll_id', $this->getId());
            $form->addSubmit('submit_vote', dgettext('poll', 'Vote'));
            $result = $this->getOptions();
            if (PHPWS_Error::logIfError($result))
            {
                return NULL;
            }
            foreach ($result as $option)
            {
                $option_labels[$op_cnt] = PHPWS_Text::parseOutput($option['name']);
                $option_nums[] = $op_cnt++;
            }
            $form->addRadio('option', $option_nums);
            $form->setLabel('option', $option_labels);
            $template = $form->getTemplate();

            if (Current_User::allow('poll'))
            {
                if (Current_User::allow('poll', 'edit_polls', $this->getId()))
                {
                    $link['action'] = 'editPoll';
                    $link['poll_id'] = $this->getId();
                    $img = sprintf('<img src="./images/mod/poll/edit.png" alt="%s" title="%s" />',
                                   dgettext('poll', 'Edit'), dgettext('poll', 'Edit'));
                    $template['EDIT'] = PHPWS_Text::secureLink($img, 'poll', $link);
                }

                if (!empty($this->_pin_key) && $pin_mode)
                {
                    $link['action'] = 'lockPoll';
                    $link['poll_id'] = $this->getId();
                    $link['key_id'] = $this->_pin_key->id;
                    $img = sprintf('<img src="./images/mod/poll/pin.png" alt="%s" title="%s" />',
                                   dgettext('poll', 'Pin'), dgettext('poll', 'Pin'));
                    $template['OPT'] = PHPWS_Text::secureLink($img, 'poll', $link);
                }
                elseif (!empty($this->_pin_key) && $admin_icon)
                {
                    $vars['action'] = 'removePollPin';
                    $vars['poll_id'] = $this->getId();
                    $vars['key_id'] = $this->_pin_key->id;
                    $js_var['ADDRESS'] = PHPWS_Text::linkAddress('poll', $vars, TRUE);
                    $js_var['QUESTION'] = dgettext('poll', 'Are you sure you want to remove this poll from this page?');
                    $js_var['LINK'] = sprintf('<img src="./images/mod/poll/remove.png" alt="%s" title="%s" />',
                                              dgettext('poll', 'Remove'), dgettext('poll', 'Remove'));

                    $template['OPT'] = Layout::getJavascript('confirm', $js_var);
                }
            }

            $template['TITLE']    = $this->getTitle();
            $template['QUESTION'] = $this->getQuestion();

            if (MOD_REWRITE_ENABLED)
            {
                $template['VOTES'] = sprintf('<a href="poll/%d">%s</a>', $this->getId(), $this->getTotalNumberVotes(TRUE));
            }
            else
            {
                $template['VOTES'] = PHPWS_Text::moduleLink($this->getTotalNumberVotes(TRUE), 'poll',
                                                            array('user'=>'results', 'poll_id'=>$this->getId()));
            }

            if ($this->allow_comments)
            {
                PHPWS_Core::initModClass('comments', 'Comments.php');
                $comments = Comments::getThread($this->key_id);

                if (MOD_REWRITE_ENABLED)
                {
                    $template['COMMENTS'] = sprintf('<a href="poll/%d#comments">%s</a>', $this->getId(), $comments->countComments(TRUE));
                }
                else
                {
                    $template['COMMENTS'] = PHPWS_Text::moduleLink($comments->countComments(TRUE), 'poll',
                                                                   array('user'=>'results', 'poll_id'=>$this->getId()));
                }
            }
            return PHPWS_Template::process($template, 'poll', 'poll/boxstyles/default.tpl');
        }

        return NULL;
    }

    function results()
    {
        if ($this->active && $this->getId())
        {
            $key = $this->getKey();
            $key->flag();

            $total_votes = $this->getTotalNumberVotes(FALSE);

            $result = $this->getOptions();
            if (PHPWS_Error::logIfError($result))
            {
                return NULL;
            }
            foreach ($result as $option)
            {
                $option_tags['OPTION_LABEL'] = PHPWS_Text::parseOutput($option['name']);

                $option_tags['VOTES'] = sprintf(dgettext('poll', '%s votes'), number_format($option['votes']));
                if ($option['votes'] == 1)
                {
                    $option_tags['VOTES'] = dgettext('poll', '1 vote');
                }

                if ($option['votes'] > 0)
                {
                    $percent = ($option['votes'] * 100.0) / $total_votes;
                    $option_tags['PERCENT'] = number_format($percent, 1);
                    $option_tags['WIDTH'] = floor($percent * POLL_GRAPH_MULTIPLIER);
                }
                else
                {
                    $option_tags['PERCENT'] = number_format(0,1);
                    $option_tags['WIDTH'] = 0;
                }

                $template['listoptions'][] = $option_tags;
            }

            $template['TITLE']       = $this->getTitle();
            $template['QUESTION']    = $this->getQuestion();
            $template['TOTAL_VOTES'] = $this->getTotalNumberVotes(TRUE);
            $template['MESSAGE']     = Poll_User::getMessage();

            if ($this->allow_comments)
            {
                PHPWS_Core::initModClass('comments', 'Comments.php');
                $comments = Comments::getThread($key);
                $template['COMMENTS'] = $comments->view();
            }

            return PHPWS_Template::process($template, 'poll', 'poll/results.tpl');
        }

        return NULL;
    }

    function isPinned()
    {
        if (!isset($_SESSION['Pinned_Polls']))
        {
            return FALSE;
        }

        return isset($_SESSION['Pinned_Polls'][$this->getId()]);
    }

    function allPinned()
    {
        static $all_pinned = null;

        if (empty($all_pinned))
        {
            $db = new PHPWS_DB('poll_pins');
            $db->addWhere('key_id', -1);
            $db->addColumn('poll_id');
            $result = $db->select('col');
            if (PHPWS_Error::logIfError($result))
            {
                return false;
            }
            if ($result)
            {
                $all_pinned = $result;
            }
            else
            {
                $all_pinned = true;
            }
        }

        if (is_array($all_pinned))
        {
            return in_array($this->id, $all_pinned);
        }

        return false;
    }

    function getTpl()
    {
        $vars['poll_id'] = $this->getId();

        if (Current_User::allow('poll', 'edit_polls', $this->getId()))
        {
            $vars['action'] = 'editPoll';
            $links[] = PHPWS_Text::secureLink(dgettext('poll', 'Edit'), 'poll', $vars);
        }

        if ($this->isPinned())
        {
            $vars['action'] = 'unpinPoll';
            $links[] = PHPWS_Text::secureLink(dgettext('poll', 'Unpin'), 'poll', $vars);
        }
        else
        {
            if ($this->allPinned())
            {
                $vars['action'] = 'removePollPin';
                $links[] = PHPWS_Text::secureLink(dgettext('poll', 'Unpin all'), 'poll', $vars);
            }
            else
            {
                $vars['action'] = 'pinPoll';
                $links[] = PHPWS_Text::secureLink(dgettext('poll', 'Pin'), 'poll', $vars);
                $vars['action'] = 'pinPollAll';
                $links[] = PHPWS_Text::secureLink(dgettext('poll', 'Pin all'), 'poll', $vars);
            }
        }

        if (Current_User::isUnrestricted('poll'))
        {
            $links[] = Current_User::popupPermission($this->key_id);
        }

        $vars['action'] = 'copyPoll';
        $links[] = PHPWS_Text::secureLink(dgettext('poll', 'Copy'), 'poll', $vars);

        if (Current_User::allow('poll', 'delete_polls'))
        {
            $vars['action'] = 'deletePoll';
            $confirm_vars['QUESTION'] = dgettext('poll', 'Are you sure you want to permanently delete this poll?');
            $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('poll', $vars, TRUE);
            $confirm_vars['LINK'] = dgettext('poll', 'Delete');
            $links[] = javascript('confirm', $confirm_vars);
        }

        $template['ACTION']         = implode(' | ', $links);
        $template['QUESTION']       = $this->getQuestion();
        $template['USERS_ONLY']     = $this->getUsersOnly();
        $template['ALLOW_COMMENTS'] = $this->getAllowComments();
        $template['ACTIVE']         = $this->getActive();
        $template['CREATED']        = $this->getCreated();

        return $template;
    }
}

?>