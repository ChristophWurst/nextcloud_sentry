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

namespace OCA\Sentry\Test\Unit\Command;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Sentry\Command\IgnoredMessages;
use OCA\Sentry\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

final class IgnoredMessagesTest extends TestCase {

	private Config|MockObject $config;
	private CommandTester $tester;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(Config::class);
		$this->tester = new CommandTester(new IgnoredMessages($this->config));
	}

	public function testAddKeepsLeadingBackslash(): void {
		$this->config->method('getIgnoredMessages')->willReturn([]);
		$this->config->expects($this->once())
			->method('setIgnoredMessages')
			->with(['\\Some message']);

		$this->assertSame(0, $this->tester->execute(['action' => 'add', 'args' => '\\Some message']));
	}

	public function testRemoveIsCaseInsensitive(): void {
		$this->config->method('getIgnoredMessages')->willReturn(['Some message', 'other']);
		$this->config->expects($this->once())
			->method('setIgnoredMessages')
			->with(['other']);

		$this->assertSame(0, $this->tester->execute(['action' => 'remove', 'args' => 'some MESSAGE']));
	}
}
