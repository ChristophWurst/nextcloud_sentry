<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\Sentry\AppInfo;

use OCA\Sentry\Config;
use OCA\Sentry\Reporter\RecursionAwareReporter;
use OCA\Sentry\Reporter\SentryReporterAdapter;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IConfig;
use OCP\IContainer;
use OCP\IInitialStateService;
use function Sentry\init as initSentry;

class Application extends App implements IBootstrap {

	public const APP_ID = 'sentry';

	public function __construct() {
		parent::__construct(self::APP_ID, []);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerCrashReporter(RecursionAwareReporter::class);

		$context->registerService(RecursionAwareReporter::class, function (IContainer $c) {
			/** @var SentryReporterAdapter $reporter */
			$reporter = $c->query(SentryReporterAdapter::class);

			return new RecursionAwareReporter($reporter);
		});
	}

	public function boot(IBootContext $context): void {
		$this->initSentry(
			$context->getAppContainer()->query(IConfig::class),
			$context->getAppContainer()->query(Config::class)
		);
		$this->setInitialState(
			$context->getAppContainer()->query(IInitialStateService::class),
			$context->getAppContainer()->query(Config::class)
		);
	}

	private function setInitialState(IInitialStateService $stateService,
									 Config $config): void {
		$stateService->provideLazyInitialState('sentry', 'dsn', function () use ($config) {
			return [
				'dsn' => $config->getDsn(),
			];
		});
	}

	private function initSentry(IConfig $sysConfig,
								Config $config) {
		$dsn = $config->getDsn();

		if ($dsn === null) {
			return;
		}

		initSentry([
			'dsn' => $dsn,
			'release' => $sysConfig->getSystemValue('version', '0.0.0'),
		]);
	}

}
