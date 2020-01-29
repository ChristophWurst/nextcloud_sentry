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

namespace OCA\Sentry\Test\Unit\Reporter;

use ChristophWurst\Nextcloud\Testing\TestCase;
use Exception;
use OCA\Sentry\Reporter\RecursionAwareReporter;
use OCP\Support\CrashReport\ICollectBreadcrumbs;
use OCP\Support\CrashReport\IMessageReporter;
use OCP\Support\CrashReport\IReporter;
use PHPUnit\Framework\MockObject\MockObject;

class RecursionAwareReporterTest extends TestCase {

	/** @var RecursionAwareReporter */
	private $reporter;

	/** @var MockObject|IReporter|IMessageReporter|ICollectBreadcrumbs */
	private $inner;

	protected function setUp(): void {
		parent::setUp();

		$this->inner = $this->createMock(IReporter::class);

		$this->reporter = new RecursionAwareReporter(
			$this->inner
		);
	}

	public function testCollectRecursively(): void {
		$user = null;
		$this->inner
			->expects($this->once())
			->method('report')
			->willReturnCallback(function () {
				$this->reporter->report(new Exception('ex2'));
			});
		$this->reporter->report(new Exception('ex1'));
	}

}
