<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Sentry\InitialState;

use OCA\Sentry\Config;
use OCP\AppFramework\Services\InitialStateProvider;

class DsnProvider extends InitialStateProvider {

	/** @var Config */
	private $config;

	public function __construct(Config $config) {
		$this->config = $config;
	}

	public function getKey(): string {
		return 'dsn';
	}

	public function getData() {
		return $this->config->getPublicDsn();
	}
}
