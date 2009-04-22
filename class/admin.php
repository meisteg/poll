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
 * @version $Id: admin.php,v 1.10 2008/07/12 02:45:15 blindman1344 Exp $
 */

class Poll_Admin
{
    function action()
    {
        if (!Current_User::allow('poll'))
        {
            Current_User::disallow();
            return;
        }

        $panel = & Poll_Admin::cpanel();
        if (isset($_REQUEST['action']))
        {
            $action = $_REQUEST['action'];
        }
        else
        {
            $tab = $panel->getCurrentTab();
            if (empty($tab))
            {
                $action = 'managePolls';
            }
            else
            {
                $action = &$tab;
            }
        }

        $panel->setContent(Poll_Admin::route($action, $panel));
        Layout::add(PHPWS_ControlPanel::display($panel->display()));
    }

    function &cpanel()
    {
        PHPWS_Core::initModClass('controlpanel', 'Panel.php');

        $linkBase = 'index.php?module=poll';
        if (Current_User::allow('poll', 'edit_polls'))
        {
            $tabs['newPoll'] = array('title'=>dgettext('poll', 'New Poll'), 'link'=> $linkBase);
        }
        $tabs['managePolls'] = array('title'=>dgettext('poll', 'Manage Polls'), 'link'=> $linkBase);

        $panel = new PHPWS_Panel('poll');
        $panel->enableSecure();
        $panel->quickSetTabs($tabs);
        $panel->setModule('poll');
        return $panel;
    }

    function route($action, &$panel)
    {
        $title   = NULL;
        $content = NULL;
        $message = Poll_User::getMessage();

        if (isset($_REQUEST['poll_id']))
        {
            $poll = new Poll($_REQUEST['poll_id']);
        }
        else
        {
            $poll = new Poll();
        }

        switch ($action)
        {
            case 'newPoll':
                $title = dgettext('poll', 'New Poll');
                $content = Poll_Admin::editPoll($poll);
                break;

            case 'deletePoll':
                $poll->kill();
                Poll_User::sendMessage(dgettext('poll', 'Poll deleted'), 'managePolls');
                break;

            case 'editPoll':
                $title = dgettext('poll', 'Edit Poll');
                $content = Poll_Admin::editPoll($poll);
                break;

            case 'hidePoll':
                $result = $poll->toggle();
                if (PHPWS_Error::logIfError($result))
                {
                    Poll_User::sendMessage(dgettext('poll', 'Poll activation could not be changed'), 'managePolls');
                }
                else
                {
                    Poll_User::sendMessage(dgettext('poll', 'Poll activation changed'), 'managePolls');
                }
                break;

            case 'pinPoll':
                $_SESSION['Pinned_Polls'][$poll->getId()] = $poll;
                Poll_User::sendMessage(dgettext('poll', 'Poll pinned'), 'managePolls');
                break;

            case 'pinPollAll':
                Poll_Admin::pinPollAll($poll->getId());
                Poll_User::sendMessage(dgettext('poll', 'Poll pinned'), 'managePolls');
                break;

            case 'unpinPoll':
                unset($_SESSION['Pinned_Polls'][$poll->getId()]);
                Poll_User::sendMessage(dgettext('poll', 'Poll unpinned'), 'managePolls');
                break;

            case 'removePollPin':
                Poll_Admin::removePollPin();
                PHPWS_Core::goBack();
                break;

            case 'copyPoll':
                Clipboard::copy($poll->getTitle(), $poll->getTag());
                PHPWS_Core::goBack();
                break;

            case 'postPoll':
                $result = Poll_Admin::postPoll($poll);
                if (is_array($result))
                {
                    $title = dgettext('poll', 'Edit Poll');
                    $content = Poll_Admin::editPoll($poll, FALSE, $result);
                }
                else
                {
                    $result = $poll->save();
                    if (PHPWS_Error::logIfError($result))
                    {
                        Poll_User::sendMessage(dgettext('poll', 'Poll could not be saved'), 'managePolls');
                    }
                    else
                    {
                        Poll_User::sendMessage(dgettext('poll', 'Poll saved'), 'managePolls');
                    }
                }
                break;

            case 'postJSPoll':
                $result = Poll_Admin::postPoll($poll);
                if (is_array($result))
                {
                    $template['TITLE'] = dgettext('poll', 'Edit Poll');
                    $template['CONTENT'] = Poll_Admin::editPoll($poll, TRUE, $result);
                    $content = PHPWS_Template::process($template, 'poll', 'admin.tpl');
                    Layout::nakedDisplay($content);
                }
                else
                {
                    $result = $poll->save();
                    if (!PHPWS_Error::logIfError($result) && (isset($_REQUEST['key_id'])))
                    {
                        Poll_Admin::lockPoll($poll->id, $_REQUEST['key_id']);
                    }

                    javascript('close_refresh');
                }
                break;

            case 'lockPoll':
                $result = Poll_Admin::lockPoll($_GET['poll_id'], $_GET['key_id']);
                PHPWS_Error::logIfError($result);
                PHPWS_Core::goBack();
                break;

            case 'managePolls':
                /* Need to set tab in case we got here from another action. */
                $panel->setCurrentTab('managePolls');
                $title = dgettext('poll', 'Manage Polls');
                $content = Poll_Admin::listPolls();
                break;

            case 'editJSPoll':
                $template['TITLE'] = dgettext('poll', 'New Poll');
                $template['CONTENT'] = Poll_Admin::editPoll($poll, TRUE);
                $content = PHPWS_Template::process($template, 'poll', 'admin.tpl');
                Layout::nakedDisplay($content);
                break;

            default:
                /* This should not happen. */
                return;
        }

        $template['TITLE'] = &$title;
        if (isset($message))
        {
            $template['MESSAGE'] = &$message;
        }
        $template['CONTENT'] = &$content;

        return PHPWS_Template::process($template, 'poll', 'admin.tpl');
    }

    function editPoll(&$poll, $js=FALSE, $errors=NULL)
    {
        if (!Current_User::allow('poll', 'edit_polls'))
        {
            Current_User::disallow();
            return;
        }

        $form = new PHPWS_Form;
        $form->addHidden('module', 'poll');

        if ($js)
        {
            $form->addHidden('action', 'postJSPoll');
            if (isset($_REQUEST['key_id']))
            {
                $form->addHidden('key_id', (int)$_REQUEST['key_id']);
            }
            $form->addButton('cancel', dgettext('poll', 'Cancel'));
            $form->setExtra('cancel', 'onclick="window.close()"');
        }
        else
        {
            $form->addHidden('action', 'postPoll');
        }

        if (empty($poll->id))
        {
            $form->addSubmit('submit', dgettext('poll', 'Save New Poll'));
        }
        else
        {
            $form->addHidden('poll_id', $poll->getId());
            $form->addSubmit('submit', dgettext('poll', 'Update Poll'));
        }

        $form->addText('title', $poll->getTitle(FALSE));
        $form->setLabel('title', dgettext('poll', 'Title'));
        $form->setSize('title', 50, 100);

        $form->addText('question', $poll->getQuestion(FALSE));
        $form->setLabel('question', dgettext('poll', 'Question'));
        $form->setSize('question', 50);

        $form->addTplTag('OPTIONS_LABEL', dgettext('poll', 'Options'));
        $op_cnt = 1;
        if (isset($_POST['option1']))
        {
            while (isset($_POST["option$op_cnt"]) && ($_POST["option$op_cnt"] != "") && ($op_cnt <= POLL_MAX_OPTIONS))
            {
                $form->addText("option$op_cnt", $_POST["option$op_cnt"]);
                $form->setSize("option$op_cnt", 30, 100);
                $op_cnt++;
            }
        }
        else if (!empty($poll->id))
        {
            $result = $poll->getOptions();
            if (!PHPWS_Error::logIfError($result))
            {
                foreach ($result as $option)
                {
                    $form->addText("option$op_cnt", $option['name']);
                    $form->setSize("option$op_cnt", 30, 100);
                    $op_cnt++;
                }
            }
        }
        if ($op_cnt <= POLL_MAX_OPTIONS)
        {
            $form->addText("option$op_cnt");
            $form->setSize("option$op_cnt", 30, 100);
        }
        if ($op_cnt < POLL_MAX_OPTIONS)
        {
            $form->addSubmit('add_option', dgettext('poll', 'Add Option'));
        }

        $form->addCheck('users_only');
        $form->setMatch('users_only', $poll->users_only);
        $form->setLabel('users_only', dgettext('poll', 'Users Only'));

        $form->addCheck('allow_comments');
        $form->setMatch('allow_comments', $poll->allow_comments);
        $form->setLabel('allow_comments', dgettext('poll', 'Allow Comments'));

        $template = $form->getTemplate();
        if (isset($errors['title']))
        {
            $template['TITLE_ERROR'] = $errors['title'];
        }
        if (isset($errors['question']))
        {
            $template['QUESTION_ERROR'] = $errors['question'];
        }
        if (isset($errors['options']))
        {
            $template['OPTIONS_ERROR'] = $errors['options'];
        }

        return PHPWS_Template::process($template, 'poll', 'poll/edit.tpl');
    }

    function postPoll(&$poll)
    {
        if (isset($_POST['add_option']))
        {
            /* Need to return an array to return to edit page */
            $errors = array();
        }
        else
        {
            if (empty($_POST['title']))
            {
                $errors['title'] = dgettext('poll', 'Your poll must have a title.');
            }
            if (empty($_POST['question']))
            {
                $errors['question'] = dgettext('poll', 'Your poll must have a question.');
            }
            if (empty($_POST['option1']) || !isset($_POST['option2']) || (isset($_POST['option2']) && empty($_POST['option2'])))
            {
                $errors['options'] = dgettext('poll', 'Your poll must have at least two options.');
            }
        }

        $poll->setTitle($_POST['title']);
        $poll->setQuestion($_POST['question']);
        $poll->setUsersOnly((int)isset($_POST['users_only']));
        $poll->setAllowComments((int)isset($_POST['allow_comments']));
        $poll->setCreated(mktime());

        $op_cnt = 1;
        while (isset($_POST["option$op_cnt"]) && ($_POST["option$op_cnt"] != "") && ($op_cnt <= POLL_MAX_OPTIONS))
        {
            $poll->storeOption($_POST["option$op_cnt"]);
            $op_cnt++;
        }

        if (isset($errors))
        {
            return $errors;
        }
        return TRUE;
    }

    function removePollPin()
    {
        if (!isset($_GET['poll_id']))
        {
            return;
        }

        $db = new PHPWS_DB('poll_pins');
        $db->addWhere('poll_id', $_GET['poll_id']);
        if (isset($_GET['key_id']))
        {
            $db->addWhere('key_id', $_GET['key_id']);
        }

        PHPWS_Error::logIfError($db->delete());
    }

    function listPolls()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pageTags['ACTION'] = dgettext('poll', 'Action');
        $pager = new DBPager('poll', 'Poll');
        $pager->setModule('poll');
        $pager->setTemplate('poll/list.tpl');
        $pager->addToggle(' class="bgcolor1"');
        $pager->addPageTags($pageTags);
        $pager->addRowTags('getTpl');
        $pager->setSearch('title', 'question');
        $pager->setDefaultOrder('title', 'asc');
        $pager->addSortHeader('title', dgettext('poll', 'Title'));
        $pager->addSortHeader('question', dgettext('poll', 'Question'));
        $pager->addSortHeader('created', dgettext('poll', 'Created'));
        $pager->addSortHeader('users_only', dgettext('poll', 'Users Only'));
        $pager->addSortHeader('allow_comments', dgettext('poll', 'Comments'));
        $pager->addSortHeader('active', dgettext('poll', 'Active'));
        $pager->setEmptyMessage(dgettext('poll', 'No polls found.'));
        $pager->cacheQueries();

        return $pager->get();
    }

    function pinPollAll($poll_id)
    {
        $values['poll_id'] = $poll_id;
        $db = new PHPWS_DB('poll_pins');
        $db->addWhere($values);
        $result = $db->delete();
        $db->resetWhere();

        $values['key_id'] = -1;
        $db->addValue($values);

        return $db->insert();
    }

    function lockPoll($poll_id, $key_id)
    {
        $poll_id = (int)$poll_id;
        $key_id = (int)$key_id;

        unset($_SESSION['Pinned_Polls'][$poll_id]);

        $values['poll_id'] = $poll_id;
        $values['key_id'] = $key_id;

        $db = new PHPWS_DB('poll_pins');
        $db->addWhere($values);
        $result = $db->delete();
        $db->addValue($values);
        return $db->insert();
    }
}
?>