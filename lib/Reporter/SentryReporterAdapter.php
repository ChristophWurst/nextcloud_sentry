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

namespace OCA\Sentry\Reporter;

use Exception;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\Support\CrashReport\IReporter;
use OCP\Util;
use Raven_Client;
use Throwable;

class SentryReporterAdapter implements IReporter {

	/** @var IUserSession */
	private $userSession;

	/** @var Raven_Client */
	private $client;

	/** @var array mapping of log levels */
	private $levels = [
		Util::DEBUG => 'debug',
		Util::INFO => 'info',
		Util::WARN => 'warning',
		Util::ERROR => 'error',
		Util::FATAL => 'fatal',
	];

	/** @var int */
	private $minimumLogLevel;

	/**
	 * @param Raven_Client $client
	 */
	public function __construct(Raven_Client $client, IUserSession $userSession, IConfig $config) {
		$this->client = $client;
		$this->userSession = $userSession;
		$this->minimumLogLevel = (int)$config->getSystemValue('sentry.minimum.log.level', Util::WARN);
	}

	/**
	 * Report an (unhandled) exception to Sentry
	 *
	 * @param Exception|Throwable $exception
	 * @param array $context
	 */
	public function report($exception, array $context = []) {
		$sentryContext = [];

		if (isset($context['level'])) {
			if ($context['level'] < $this->minimumLogLevel) {
				return;
			}

			$sentryContext['level'] = $this->levels[$context['level']];
		}

		$user = $this->userSession->getUser();
		if (!is_null($user)) {
			$sentryContext['user'] = [
				'id' => $user->getUID(),
			];
		}

		$this->client->captureException($exception, $sentryContext);
	}

}
