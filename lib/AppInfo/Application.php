<?php

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

use OCA\Sentry\Reporter\SentryReporterAdapter;
use OCP\AppFramework\App;
use OCP\IConfig;
use OCP\Support\CrashReport\IRegistry;
use Raven_Client;
use Raven_ErrorHandler;

class Application extends App {

	/** @var Raven_Client */
	private $client;

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = []) {
		parent::__construct('sentry', $urlParams);

		$container = $this->getContainer();

		/* @var $config IConfig */
		$config = $container->query(IConfig::class);
		$dsn = $config->getSystemValue('sentry.dsn', null);
		if (!is_null($dsn)) {
			$this->registerClient($dsn);
		}
	}

	/**
	 * @param string $dsn
	 */
	private function registerClient($dsn) {
		$container = $this->getContainer();
		/* @var $config IConfig */
		$config = $container->query(IConfig::class);

		$client = new Raven_Client($dsn);
		$client->setRelease($config->getSystemValue('version', '0.0.0'));
		$container->registerService(Raven_Client::class, function() use ($client) {
			return $client;
		});

		/* @var $registry IRegistry */
		$registry = $container->query(IRegistry::class);
		$reporter = $container->query(SentryReporterAdapter::class);
		$registry->register($reporter);

		$this->registerErrorHandlers($client);
	}

	private function registerErrorHandlers(Raven_Client $client) {
		$errorHandler = new Raven_ErrorHandler($client);
		$errorHandler->registerExceptionHandler();
		$errorHandler->registerErrorHandler();
		$errorHandler->registerShutdownFunction();
	}

}
