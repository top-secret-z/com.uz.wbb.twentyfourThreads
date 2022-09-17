<?php
namespace wbb\data\thread;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a list of threads of last 24h.
 * 
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wbb.unansweredThreads
 */
class TwentyfourThreadList extends AccessibleThreadList {
	/**
	 * Creates a new TwentyfourThreadList object.
	 */
	public function __construct() {
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
		if (LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$this->getConditionBuilder()->add('(thread.languageID IN (?) OR thread.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
		}
	}
}
