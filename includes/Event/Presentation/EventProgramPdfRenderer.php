<?php

declare(strict_types=1);

namespace Contexis\Events\Event\Presentation;

use Contexis\Events\Event\Application\DTOs\EventCalendarEntry;
use Contexis\Events\Event\Application\DTOs\EventProgramData;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

final class EventProgramPdfRenderer
{
	/**
	 * @return never
	 */
	public function download(EventProgramData $program, bool $showEmptyDays = true): void
	{
		$days = $this->buildDayMap($program, $showEmptyDays);

		$html = $this->renderTemplate($program->mode, [
			'days' => $days,
			'mode' => $program->mode,
			'showEmptyDays' => $showEmptyDays,
			'start' => $program->startDate,
			'end' => $program->endDate,
			'title' => $this->buildTitle($program),
		]);

		$pdf = new Mpdf($this->getPdfConfig());
		$pdf->WriteHTML($html);
		$pdf->Output(
			sanitize_file_name(sprintf('%s-%s.pdf', $this->getFilePrefix($program->mode), $program->startDate->format('Y-m-d'))),
			'D',
		);
		exit;
	}

	/**
	 * @return array<string, array{timestamp: string, count: int, name: string, weekday: int, events: array<int, array<string, mixed>>, is_sunday: bool}>
	 */
	private function buildDayMap(EventProgramData $program, bool $showEmptyDays): array
	{
		$days = [];
		$current = $program->startDate;

		while ($current <= $program->endDate) {
			$dayKey = $current->format('Y-m-d');
			$days[$dayKey] = [
				'timestamp' => $current->format('Y-m-d'),
				'count' => (int) $current->format('j'),
				'name' => wp_date('D', $current->getTimestamp()),
				'weekday' => (int) $current->format('N'),
				'events' => [],
				'is_sunday' => (int) $current->format('N') === 7,
			];

			$current = $current->modify('+1 day');
		}

		foreach ($program->events as $event) {
			foreach ($this->buildEntries($event, $program) as $entry) {
				$dayKey = $entry['dayKey'];
				if (!isset($days[$dayKey])) {
					continue;
				}

				unset($entry['dayKey']);
				$days[$dayKey]['events'][] = $entry;
			}
		}

		if (!$showEmptyDays) {
			$days = array_filter(
				$days,
				static fn (array $day): bool => $day['events'] !== [],
			);
		}

		return $days;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function buildEntries(EventCalendarEntry $event, EventProgramData $program): array
	{
		if ($program->mode === 'year') {
			if ($event->startDate < $program->startDate || $event->startDate > $program->endDate) {
				return [];
			}

			return [[
				'dayKey' => $event->startDate->format('Y-m-d'),
				'title' => $event->title,
				'excerpt' => $event->description,
				'location' => $event->locationName,
				'person' => $event->personName,
				'timeLabel' => $this->buildTimeLabel($event),
				'isContinuation' => false,
				'dateLabel' => $this->buildDateLabel($event->startDate, $event->endDate),
			]];
		}

		$visibleStart = $event->startDate > $program->startDate ? $event->startDate : $program->startDate;
		$visibleEnd = $event->endDate < $program->endDate ? $event->endDate : $program->endDate;
		if ($visibleEnd < $visibleStart) {
			return [];
		}

		$entries = [];
		$current = $visibleStart->setTime(0, 0, 0);
		$lastDay = $visibleEnd->setTime(0, 0, 0);

		while ($current <= $lastDay) {
			$entries[] = [
				'dayKey' => $current->format('Y-m-d'),
				'title' => $event->title,
				'excerpt' => $event->description,
				'location' => $event->locationName,
				'person' => $event->personName,
				'timeLabel' => $this->buildTimeLabel($event),
				'isContinuation' => $current > $event->startDate->setTime(0, 0, 0),
				'dateLabel' => $this->buildDateLabel($event->startDate, $event->endDate),
			];
			$current = $current->modify('+1 day');
		}

		return $entries;
	}

	private function buildTimeLabel(EventCalendarEntry $event): string
	{
		$label = wp_date(get_option('time_format'), $event->startDate->getTimestamp());
		if ($event->startDate->format('H:i') !== $event->endDate->format('H:i')) {
			$label .= ' - ' . wp_date(get_option('time_format'), $event->endDate->getTimestamp());
		}

		return $label;
	}

	private function buildDateLabel(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): string
	{
		if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
			return '';
		}

		return wp_date(get_option('date_format'), $startDate->getTimestamp()) . ' - ' . wp_date(get_option('date_format'), $endDate->getTimestamp());
	}

	private function buildTitle(EventProgramData $program): string
	{
		return match ($program->mode) {
			'week' => sprintf(
				__('Week program %s', 'ctx-events'),
				wp_date(get_option('date_format'), $program->startDate->getTimestamp()) . ' - ' . wp_date(get_option('date_format'), $program->endDate->getTimestamp()),
			),
			'year' => sprintf(
				__('Year program %s', 'ctx-events'),
				wp_date('Y', $program->startDate->getTimestamp()),
			),
			default => sprintf(
				__('Month program %s', 'ctx-events'),
				wp_date('F Y', $program->startDate->getTimestamp()),
			),
		};
	}

	private function getFilePrefix(string $mode): string
	{
		return match ($mode) {
			'week' => 'week_program',
			'year' => 'year_program',
			default => 'month_program',
		};
	}

	/**
	 * @param array<string, mixed> $context
	 */
	private function renderTemplate(string $mode, array $context): string
	{
		$template = $this->locateTemplate($mode);

		ob_start();
		extract($context, EXTR_SKIP);
		include $template;

		return (string) ob_get_clean();
	}

	private function locateTemplate(string $mode): string
	{
		$templateNames = [
			sprintf('plugins/events/pdf/%s-events.php', $mode),
			sprintf('ctx-events/pdf/%s-events.php', $mode),
			'plugins/events/pdf/program.php',
			'ctx-events/pdf/program.php',
			'plugins/events/pdf/monthly-events.php',
			'ctx-events/pdf/monthly-events.php',
		];

		$themeTemplate = locate_template($templateNames, false, false);
		if (is_string($themeTemplate) && $themeTemplate !== '') {
			return $themeTemplate;
		}

		$pluginTemplate = dirname(__DIR__, 3) . sprintf('/templates/pdf/%s-events.php', $mode);
		if (is_file($pluginTemplate)) {
			return $pluginTemplate;
		}

		$genericPluginTemplate = dirname(__DIR__, 3) . '/templates/pdf/program.php';
		if (is_file($genericPluginTemplate)) {
			return $genericPluginTemplate;
		}

		return dirname(__DIR__, 3) . '/templates/pdf/monthly-events.php';
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getPdfConfig(): array
	{
		$fontPath = get_stylesheet_directory() . '/plugins/events/pdf/fonts';
		if (!is_dir($fontPath)) {
			return [];
		}

		$defaultConfig = (new ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];
		$defaultFontConfig = (new FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];

		return [
			'fontDir' => array_merge($fontDirs, [$fontPath]),
			'fontdata' => $fontData + [
				'ctxpdf' => [
					'R' => 'regular.ttf',
					'B' => 'bold.ttf',
					'I' => 'italic.ttf',
				],
			],
			'default_font' => 'ctxpdf',
		];
	}
}
