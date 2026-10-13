<?php

declare(strict_types=1);

/**
 * @copyright 2026 Fahim Salam Chowdhury <fahim.chowdury.ext@murena.com>
 *
 * @author 2026 Fahim Salam Chowdhury <fahim.chowdury.ext@murena.com>
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

namespace OCA\Sentry\Command;

use OCP\HintException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseIgnoreCommand extends Command {
	protected const ACTIONS = ['list', 'add', 'remove'];

	protected const ACTION = 'action';
	protected const ARGS = 'args';
	protected const OUTPUT = 'output';

	/**
	 * @return list<string>
	 */
	abstract protected function getListFromConfig(): array;

	/**
	 * @param list<string> $ignoredItems
	 * @throws HintException if the config is read-only
	 */
	abstract protected function writeListToConfig(array $ignoredItems): void;

	protected function normalize(string $item): string {
		return $item;
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		/** @var string $action */
		$action = $input->getArgument(self::ACTION);
		/** @var string|null $item */
		$item = $input->getArgument(self::ARGS);

		if ($action === 'list') {
			return $this->list($input, $output);
		}

		if (!in_array($action, self::ACTIONS, true)) {
			$output->writeln("<error>Unknown action $action, expected one of: " . implode(', ', self::ACTIONS) . '</error>');
			return 1;
		}

		$item = $this->normalize(trim($item ?? ''));
		if ($item === '') {
			$output->writeln("<error>The $action action requires one argument</error>");
			return 1;
		}

		return $action === 'add' ? $this->add($item, $output) : $this->remove($item, $output);
	}

	private function list(InputInterface $input, OutputInterface $output): int {
		$ignoredItems = $this->getListFromConfig();

		if ($input->getOption(self::OUTPUT) === 'json') {
			$output->writeln(json_encode($ignoredItems, JSON_THROW_ON_ERROR));
			return 0;
		}

		if ($ignoredItems === []) {
			$output->writeln('No ignored item configured');
			return 0;
		}

		foreach ($ignoredItems as $ignoredItem) {
			$output->writeln($ignoredItem);
		}

		return 0;
	}

	private function add(string $item, OutputInterface $output): int {
		$ignoredItems = $this->getListFromConfig();

		if (in_array(mb_strtolower($item), array_map('mb_strtolower', $ignoredItems), true)) {
			$output->writeln("<comment>$item is already ignored</comment>");
			return 0;
		}

		$ignoredItems[] = $item;
		if ($this->save($ignoredItems, $output) !== 0) {
			return 1;
		}

		$output->writeln("<info>Added $item</info>");
		return 0;
	}

	private function remove(string $item, OutputInterface $output): int {
		$ignoredItems = $this->getListFromConfig();

		$remaining = array_values(array_filter(
			$ignoredItems,
			static fn (string $ignored): bool => mb_strtolower($ignored) !== mb_strtolower($item),
		));

		if (count($remaining) === count($ignoredItems)) {
			$output->writeln("<comment>$item is not ignored</comment>");
			return 1;
		}

		if ($this->save($remaining, $output) !== 0) {
			return 1;
		}

		$output->writeln("<info>Removed $item</info>");
		return 0;
	}

	/**
	 * @param list<string> $ignoredItems
	 */
	private function save(array $ignoredItems, OutputInterface $output): int {
		try {
			$this->writeListToConfig($ignoredItems);
		} catch (HintException $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return 1;
		}

		return 0;
	}
}
