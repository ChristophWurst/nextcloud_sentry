<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/lib',
		__DIR__ . '/tests',
	])
	->withPreparedSets(
		phpunitCodeQuality: true,
	)
	->withSets([
		PHPUnitSetList::PHPUNIT_100,
	])
	->withPhpSets(
		php80: true,
	)
	->withRules([
		AddTestsVoidReturnTypeWhereNoReturnRector::class,
	]);
