<?php

declare(strict_types=1);

/**
 * @copyright 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\Sentry\Reporter;

use OCP\Support\CrashReport\ICollectBreadcrumbs;
use OCP\Support\CrashReport\IMessageReporter;
use OCP\Support\CrashReport\IReporter;

/**
 * Decorator that detects and stops recursive calls to reporter methods
 */
class RecursionAwareReporter implements IMessageReporter, ICollectBreadcrumbs, ISentryReporter {

	/** @var ISentryReporter */
	private $reporter;

	private $reporting = false;

	public function __construct(ISentryReporter $reporter) {
		$this->reporter = $reporter;
	}

	private function guard(callable $run): void {
		try {
			if ($this->reporting) {
				// Break the recursion
				return;
			}
			$this->reporting = true;
			$run();
		} finally {
			$this->reporting = false;
		}
	}

	public function collect(string $message, string $category, array $context = []): void {
		if ($this->reporter instanceof ICollectBreadcrumbs) {
			$this->guard(function() use ($context, $category, $message) {
				$this->reporter->collect($message, $category, $context);
			});
		}
	}

	public function reportMessage(string $message, array $context = []): void {
		if ($this->reporter instanceof IMessageReporter) {
			$this->guard(function() use ($context, $message) {
				$this->reporter->reportMessage($message, $context);
			});
		}
	}

	public function report($exception, array $context = []): void {
		$this->guard(function() use ($context, $exception) {
			$this->reporter->report($exception, $context);
		});
	}

}
