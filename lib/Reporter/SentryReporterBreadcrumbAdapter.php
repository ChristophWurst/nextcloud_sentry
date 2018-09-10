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

namespace OCA\Sentry\Reporter;

use OCP\Support\CrashReport\ICollectBreadcrumbs;

class SentryReporterBreadcrumbAdapter extends SentryReporterSimpleAdapter implements ICollectBreadcrumbs {

	public function collect(string $message, string $category, array $context = []) {
		$sentryContext = $this->buildSentryContext($context);

		$sentryContext['message'] = $message;
		$sentryContext['category'] = $category;

		$this->client->breadcrumbs->record($sentryContext);
	}

}
