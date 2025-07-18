<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
define('PHPUNIT_RUN', 1);

require_once __DIR__ . '/../../../tests/autoload.php';
require_once __DIR__.'/../../../lib/base.php';
require_once __DIR__.'/../vendor/autoload.php';

\OC_App::loadApp('sentry');
