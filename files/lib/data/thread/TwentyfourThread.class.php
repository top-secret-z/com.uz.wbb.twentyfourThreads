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

use wbb\data\board\Board;
use wbb\data\board\BoardCache;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents an 24h thread.
 */
class TwentyfourThread extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Thread::class;

    /**
     * number of 24h threads
     */
    protected static $twentyfourThreads;

    /**
     * Returns the number of 24h threads.
     */
    public static function getTwentyfourThreads()
    {
        if (self::$twentyfourThreads === null) {
            self::$twentyfourThreads = 0;

            $boardIDs = Board::getAccessibleBoardIDs();
            // removed ignored boards
            foreach ($boardIDs as $key => $boardID) {
                $board = BoardCache::getInstance()->getBoard($boardID);
                if ($board->isIgnored()) {
                    unset($boardIDs[$key]);
                }
            }

            if (!empty($boardIDs)) {
                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add("thread.boardID IN (?)", [$boardIDs]);
                $conditionBuilder->add("thread.isDeleted = 0 AND thread.isDisabled = 0 AND thread.movedThreadID IS NULL");

                // apply options
                if (!WBB_BOARD_THREAD_TWENTYFOUR_CLOSED) {
                    $conditionBuilder->add("thread.isClosed =  ?", [0]);
                }
                if (!WBB_BOARD_THREAD_TWENTYFOUR_DONE) {
                    $conditionBuilder->add("thread.isDone =  ?", [0]);
                }

                // apply period for threads
                $conditionBuilder->add("thread.lastPostTime > ?", [TIME_NOW - WBB_BOARD_THREAD_TWENTYFOUR_TIME * 3600]);

                // apply language filter
                if (LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
                    $conditionBuilder->add('(thread.languageID IN (?) OR thread.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
                }

                $sql = "SELECT    COUNT(*) AS count
                        FROM    wbb" . WCF_N . "_thread thread
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute($conditionBuilder->getParameters());
                $row = $statement->fetchArray();
                self::$twentyfourThreads = $row['count'];
            }
        }

        return self::$twentyfourThreads;
    }
}
