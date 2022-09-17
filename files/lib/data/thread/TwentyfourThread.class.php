<?php
namespace wbb\data\thread;
use wbb\data\board\Board;
use wbb\data\board\BoardCache;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents an 24h thread.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wbb.twentyfourThreads
 */
class TwentyfourThread extends DatabaseObjectDecorator {
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
	public static function getTwentyfourThreads() {
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
				if (!WBB_BOARD_THREAD_TWENTYFOUR_CLOSED) $conditionBuilder->add("thread.isClosed =  ?", [0]);
				if (!WBB_BOARD_THREAD_TWENTYFOUR_DONE) $conditionBuilder->add("thread.isDone =  ?", [0]);
				
				// apply period for threads
				$conditionBuilder->add("thread.lastPostTime > ?", [TIME_NOW - WBB_BOARD_THREAD_TWENTYFOUR_TIME * 3600]);
				
				// apply language filter
				if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
					$conditionBuilder->add('(thread.languageID IN (?) OR thread.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
				}
				
				$sql = "SELECT	COUNT(*) AS count
						FROM	wbb".WCF_N."_thread thread
						".$conditionBuilder;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditionBuilder->getParameters());
				$row = $statement->fetchArray();
				self::$twentyfourThreads = $row['count'];
			}
		}
		
		return self::$twentyfourThreads;
	}
}
