<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace wbb\data\thread;

use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a list of threads of last 24h.
 */
class TwentyfourThreadList extends AccessibleThreadList
{
    /**
     * Creates a new TwentyfourThreadList object.
     */
    public function __construct()
    {
        parent::__construct();

        if (!WBB_BOARD_THREAD_TWENTYFOUR_CLOSED) {
            $this->getConditionBuilder()->add("thread.isClosed = ?", [0]);
        }
        if (!WBB_BOARD_THREAD_TWENTYFOUR_DONE) {
            $this->getConditionBuilder()->add("thread.isDone = ?", [0]);
        }

        // apply period
        $this->getConditionBuilder()->add("thread.lastPostTime >= ?", [TIME_NOW - WBB_BOARD_THREAD_TWENTYFOUR_TIME * 3600]);

        // apply language filter
        if (LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $this->getConditionBuilder()->add('(thread.languageID IN (?) OR thread.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
        }
    }
}
