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

use OCP\AppFramework\App;
use OCP\IConfig;
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
		$serverContainer = $container->getServer();
		$config = $serverContainer->getConfig();

		/* @var $config IConfig */
		$config = $container->query(IConfig::class);
		$dsn = $config->getSystemValue('sentry.dsn', null);

		$this->client = new Raven_Client($dsn);
		$this->client->setRelease($config->getSystemValue('version', '0.0.0'));
		$serverContainer->registerService('SentryClient', function() {
			return $this->client;
		});
	}

	public function registerSentryClient() {
		$errorHandler = new Raven_ErrorHandler($this->client);
		$errorHandler->registerExceptionHandler();
		$errorHandler->registerErrorHandler();
		$errorHandler->registerShutdownFunction();
	}

}
