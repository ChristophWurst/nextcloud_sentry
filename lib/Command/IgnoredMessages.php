<?php

declare(strict_types=1);

/**
 * @copyright 2026 Fahim Salam Chowdhury <fahim.chowdury.ext@murena.com>
 *
 * @author 2026 Fahim Salam Chowdhury <fahim.chowdury.ext@murena.com>
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

namespace OCA\Sentry\Command;

use OCA\Sentry\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class IgnoredMessages extends BaseIgnoreCommand {

	public function __construct(
		private Config $config,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('sentry:ignored-messages')
			->setDescription('list, add or remove message texts that are not reported to Sentry')
			->addArgument(self::ACTION, InputArgument::REQUIRED, 'One of: ' . implode(', ', self::ACTIONS))
			->addArgument(self::ARGS, InputArgument::OPTIONAL, 'Text contained in the message (case-insensitive), required for add and remove')
			->addOption(self::OUTPUT, null, InputOption::VALUE_REQUIRED, 'Output format of the list action (plain, json)', 'plain');
	}

	protected function getListFromConfig(): array {
		return $this->config->getIgnoredMessages();
	}

	protected function writeListToConfig(array $ignoredItems): void {
		$this->config->setIgnoredMessages($ignoredItems);
	}
}
