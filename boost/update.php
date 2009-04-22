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
 * @version $Id: update.php,v 1.4 2008/07/12 02:45:14 blindman1344 Exp $
 */

function poll_update(&$content, $currentVersion)
{
    switch ($currentVersion)
    {
        case version_compare($currentVersion, '1.1.0', '<'):
            $content[] = '- Updated to new translation functions.';
            $content[] = '- Fixed defect which prevented all users from voting.';
            $content[] = '- Fixed case where a poll could be saved and only have one option.';
            $content[] = '- Fixed case where edit link would appear when user didn\'t have permission.';
            $content[] = '- Now using the new logIfError() function in the core.';

        case version_compare($currentVersion, '1.1.1', '<'):
            $files = array('templates/poll/list.tpl');
            poll_update_files($files, $content);

            $content[] = '- Now using mod_rewrite for poll results page.';
            $content[] = '- Added calls to cacheQueries and addSortHeader when using DBPager.';
            $content[] = '- Saving a poll now sets the manage tab correctly.';
            $content[] = '- Removed calls to help module which were not working anyway.';
            $content[] = '- Corrected a few phrases that were not being translated.';
    }

    return TRUE;
}

function poll_update_files($files, &$content)
{
    if (PHPWS_Boost::updateFiles($files, 'poll'))
    {
        $content[] = '- Updated the following files:';
    }
    else
    {
        $content[] = '- Unable to update the following files:';
    }

    foreach ($files as $file)
    {
        $content[] = '--- ' . $file;
    }
}

?>