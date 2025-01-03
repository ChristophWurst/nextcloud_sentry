<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2018, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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
 *
 */

namespace OCA\Sentry\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Test extends Command {
	public function __construct(private LoggerInterface $logger) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('sentry:test')
			->setDescription('Fire off a test sentry event');
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$this->logger->info("starting sentry test command");
		try {
			$this->logger->warning("you should get at least one Sentry alert soon");
			$this->logger->emergency("This is a sentry emergency test message");
			throw new \Exception('This is a sentry test exception!');
		} catch (Throwable $e) {
			$this->logger->error($e->getMessage(), ['exception' => $e]);
		}
		return 0;
	}
}
