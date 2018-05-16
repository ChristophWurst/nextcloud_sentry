<?php

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\Sentry\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;

class ScriptController extends Controller {

	/** @var IConfig */
	private $config;

	public function __construct(IRequest $request, IConfig $config) {
		parent::__construct('sentry', $request);
		$this->config = $config;
	}

	/**
	 * @PublicPage
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function dsn(): Response {
		$pubDsn = $this->config->getSystemValue('sentry.public-dsn', null);

		$tmpl = new TemplateResponse('sentry', 'dsn.js', [
			'dsn' => $pubDsn,
			], 'blank');
		$raw = $tmpl->render();
		return new DataDownloadResponse($raw, 'dsn.js', 'application/javascript');
	}

}
