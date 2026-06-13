<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Reports;

enum ReportMetric: string
{
	case Appointments = 'appointments';
	case Cancellations = 'cancellations';
	case NewClients = 'new_clients';
	case BookedValue = 'booked_value';

	public function label(): string
	{
		return match ($this) {
			self::Appointments => 'Appointments',
			self::Cancellations => 'Cancelled appointments',
			self::NewClients => 'New clients',
			self::BookedValue => 'Visible booked value',
		};
	}

	public function buttonLabel(): string
	{
		return match ($this) {
			self::Cancellations => 'Cancelled',
			self::BookedValue => 'Booked value',
			default => $this->label(),
		};
	}

	public function icon(): string
	{
		return match ($this) {
			self::Appointments => 'fa-calendar-check-o',
			self::Cancellations => 'fa-calendar-times-o',
			self::NewClients => 'fa-user-plus',
			self::BookedValue => 'fa-eur',
		};
	}

	public function format(): string
	{
		return $this === self::BookedValue ? 'currency' : 'number';
	}

	public function color(): string
	{
		return match ($this) {
			self::Appointments => '#019ef7',
			self::Cancellations => '#f1416c',
			self::NewClients => '#50cd89',
			self::BookedValue => '#019ef7',
		};
	}

	public function toArray(): array
	{
		return [
			'id' => $this->value,
			'label' => $this->label(),
			'buttonLabel' => $this->buttonLabel(),
			'icon' => $this->icon(),
			'format' => $this->format(),
			'color' => $this->color(),
		];
	}
}
