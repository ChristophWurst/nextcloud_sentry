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

namespace OCA\Tests\Unit\Reporter;

use ChristophWurst\Nextcloud\Testing\ServiceMockObject;
use ChristophWurst\Nextcloud\Testing\TestCase;
use Exception;
use OCA\Sentry\Config;
use OCA\Sentry\Helper\CredentialStoreHelper;
use OCA\Sentry\Reporter\SentryReporterAdapter;
use OCP\Defaults;
use OCP\IConfig;
use OCP\IUserSession;
use Sentry\ClientInterface;
use Sentry\SentrySdk;

final class SentryReporterAdapterTest extends TestCase {

	/** @var ServiceMockObject */
	private $serviceMock;

	/** @var SentryReporterAdapter */
	private $adapter;

	protected function setUp(): void {
		parent::setUp();

		$this->serviceMock = $this->createServiceMock(SentryReporterAdapter::class);
		$this->adapter = $this->serviceMock->getService();
	}

	protected function tearDown(): void {
		// Reset the global hub in case a test bound a client mock
		SentrySdk::init();

		parent::tearDown();
	}

	public function testReportAnonymously(): void {
		$user = null;
		$this->serviceMock->getParameter('userSession')
			->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->adapter->report(new Exception());
	}

	public function testCollectAnonymously(): void {
		$user = null;
		$this->serviceMock->getParameter('userSession')
			->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->adapter->collect('msg 1', 'log');
	}

	public function testReportMessageIgnoresMessageContainingIgnoredText(): void {
		$client = $this->createMock(ClientInterface::class);
		$client->expects($this->never())
			->method('captureMessage');
		SentrySdk::getCurrentHub()->bindClient($client);
		$adapter = $this->createAdapterWithIgnoredMessages([
			'file is GONE',
		]);

		$adapter->reportMessage('Uncaught OCP\\Files\\NotFoundException: File is gone', ['app' => 'files']);
	}

	public function testReportMessageIgnoresNonAsciiTextCaseInsensitively(): void {
		$client = $this->createMock(ClientInterface::class);
		$client->expects($this->never())
			->method('captureMessage');
		SentrySdk::getCurrentHub()->bindClient($client);
		$adapter = $this->createAdapterWithIgnoredMessages([
			'ÉCHEC DE CONNEXION',
		]);

		$adapter->reportMessage('échec de connexion au serveur');
	}

	public function testReportMessageReportsOtherMessages(): void {
		$client = $this->createMock(ClientInterface::class);
		$client->expects($this->once())
			->method('captureMessage')
			->with('[files] Uncaught RuntimeException: something broke');
		SentrySdk::getCurrentHub()->bindClient($client);
		$adapter = $this->createAdapterWithIgnoredMessages([
			'file is gone',
		]);

		$adapter->reportMessage('Uncaught RuntimeException: something broke', ['app' => 'files']);
	}

	private function createAdapterWithIgnoredMessages(array $ignoredMessages): SentryReporterAdapter {
		$config = $this->createMock(IConfig::class);
		$config->method('getSystemValueInt')
			->willReturnArgument(1);
		$config->method('getSystemValue')
			->with('sentry.ignored-messages', [])
			->willReturn($ignoredMessages);

		return new SentryReporterAdapter(
			$this->createMock(IUserSession::class),
			$config,
			$this->createMock(CredentialStoreHelper::class),
			$this->createMock(Defaults::class),
			new Config($config),
		);
	}

}
