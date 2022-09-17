<?php
namespace wbb\system\page\handler;
use wbb\data\thread\TwentyfourThread;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\WCF;

/**
 * Page handler for 24h thread list.
 *
 * @author		2017-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wbb.twentyfourThreads
 */
class TwentyfourThreadListPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return (WCF::getSession()->getPermission('user.board.canViewTwentyfourThreads') && TwentyfourThread::getTwentyfourThreads());
	}
}
