<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Brandslider\Model;

class Status {
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 2;

	/**
	 * get available statuses
	 * @return []
	 */
	public static function getAvailableStatuses() {
		return [
			self::STATUS_ENABLED => __('Enabled')
			, self::STATUS_DISABLED => __('Disabled'),
		];
	}
}
