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
use OCA\Sentry\Command\IgnoredExceptions;
use OCA\Sentry\Config;
use OCP\HintException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

final class IgnoredExceptionsTest extends TestCase {

	private Config|MockObject $config;
	private CommandTester $tester;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(Config::class);
		$this->tester = new CommandTester(new IgnoredExceptions($this->config));
	}

	public function testListEmpty(): void {
		$this->config->method('getIgnoredExceptions')->willReturn([]);

		$this->assertSame(0, $this->tester->execute(['action' => 'list']));
		$this->assertStringContainsString('No ignored item configured', $this->tester->getDisplay());
	}

	public function testList(): void {
		$this->config->method('getIgnoredExceptions')->willReturn(['RuntimeException', 'LogicException']);

		$this->assertSame(0, $this->tester->execute(['action' => 'list']));
		$this->assertSame("RuntimeException\nLogicException\n", $this->tester->getDisplay());
	}

	public function testListJson(): void {
		$this->config->method('getIgnoredExceptions')->willReturn(['RuntimeException']);

		$this->assertSame(0, $this->tester->execute(['action' => 'list', '--output' => 'json']));
		$this->assertSame("[\"RuntimeException\"]\n", $this->tester->getDisplay());
	}

	public function testUnknownAction(): void {
		$this->config->expects($this->never())
			->method('setIgnoredExceptions');

		$this->assertSame(1, $this->tester->execute(['action' => 'clear']));
		$this->assertStringContainsString('Unknown action clear', $this->tester->getDisplay());
	}

	public function testAddWithoutArgument(): void {
		$this->config->expects($this->never())
			->method('setIgnoredExceptions');

		$this->assertSame(1, $this->tester->execute(['action' => 'add']));
		$this->assertStringContainsString('requires one argument', $this->tester->getDisplay());
	}

	public function testAddStripsLeadingBackslash(): void {
		$this->config->method('getIgnoredExceptions')->willReturn(['LogicException']);
		$this->config->expects($this->once())
			->method('setIgnoredExceptions')
			->with(['LogicException', 'RuntimeException']);

		$this->assertSame(0, $this->tester->execute(['action' => 'add', 'args' => '\RuntimeException']));
		$this->assertStringContainsString('Added RuntimeException', $this->tester->getDisplay());
	}

	public function testAddSkipsDuplicates(): void {
		$this->config->method('getIgnoredExceptions')->willReturn(['RuntimeException']);
		$this->config->expects($this->never())
			->method('setIgnoredExceptions');

		$this->assertSame(0, $this->tester->execute(['action' => 'add', 'args' => '\runtimeexception']));
		$this->assertStringContainsString('already ignored', $this->tester->getDisplay());
	}

	public function testAddWithReadOnlyConfig(): void {
		$this->config->method('getIgnoredExceptions')->willReturn([]);
		$this->config->method('setIgnoredExceptions')
			->willThrowException(new HintException('Config is set to be read-only'));

		$this->assertSame(1, $this->tester->execute(['action' => 'add', 'args' => 'RuntimeException']));
		$this->assertStringContainsString('read-only', $this->tester->getDisplay());
		$this->assertStringNotContainsString('Added', $this->tester->getDisplay());
	}

	public function testRemove(): void {
		$this->config->method('getIgnoredExceptions')->willReturn(['LogicException', 'RuntimeException']);
		$this->config->expects($this->once())
			->method('setIgnoredExceptions')
			->with(['LogicException']);

		$this->assertSame(0, $this->tester->execute(['action' => 'remove', 'args' => '\runtimeexception']));
		$this->assertStringContainsString('Removed', $this->tester->getDisplay());
	}

	public function testRemoveWithReadOnlyConfig(): void {
		$this->config->method('getIgnoredExceptions')->willReturn(['RuntimeException']);
		$this->config->method('setIgnoredExceptions')
			->willThrowException(new HintException('Config is set to be read-only'));

		$this->assertSame(1, $this->tester->execute(['action' => 'remove', 'args' => 'RuntimeException']));
		$this->assertStringNotContainsString('Removed', $this->tester->getDisplay());
	}

	public function testRemoveUnknown(): void {
		$this->config->method('getIgnoredExceptions')->willReturn(['LogicException']);
		$this->config->expects($this->never())
			->method('setIgnoredExceptions');

		$this->assertSame(1, $this->tester->execute(['action' => 'remove', 'args' => 'RuntimeException']));
		$this->assertStringContainsString('is not ignored', $this->tester->getDisplay());
	}
}
