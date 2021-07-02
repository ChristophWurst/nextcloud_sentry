<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\Sentry\Listener;

use OCA\Sentry\Config;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use function parse_url;

class CustomCspListener implements IEventListener {

	/** @var Config */
	private $config;

	public function __construct(Config $config) {
		$this->config = $config;
	}

	public function handle(Event $event): void {
		if (!($event instanceof AddContentSecurityPolicyEvent)) {
			return;
		}

		$publicDsn = $this->config->getPublicDsn();
		$reportUrl = $this->config->getCspReportUrl();
		if ($publicDsn === null && $reportUrl === null) {
			// Don't add any custom CSP
			return;
		}

		$csp = new ContentSecurityPolicy();
		if ($publicDsn !== null) {
			$parsedUrl = parse_url($publicDsn);
			if (isset($parsedUrl['scheme'], $parsedUrl['host'])) {
				$domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
				$csp->addAllowedConnectDomain($domain);
			}
		}
		if ($reportUrl !== null) {
			$csp->addReportTo($reportUrl);
		}

		$event->addPolicy($csp);
	}

}
