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
 * @version $Id: config.php,v 1.4 2007/02/25 00:39:27 blindman1344 Exp $
 */

define('POLL_DATE_FORMAT', '%m/%d/%Y %r');

/* Defaults when creating a poll.  Valid values are 0 or 1. */
define('POLL_DEFAULT_USERS_ONLY', 1);
define('POLL_DEFAULT_ALLOW_COMMENTS', 0);
define('POLL_DEFAULT_ALLOW_ANONYMOUS_COMMENTS', 0);

/*
 * The maximum options allowed per poll.  If this number is increased, the
 * module templates need to be modified.
 */
define('POLL_MAX_OPTIONS', 8);

/*
 * Use this to adjust the width of the bars on the results graph.  The width
 * (in pixels) of the bars is determined by:
 *
 * width = percent_of_votes * POLL_GRAPH_MULTIPLIER
 */
define('POLL_GRAPH_MULTIPLIER', 2);

?>