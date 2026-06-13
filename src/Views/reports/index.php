<?php

$initial = $report['initial'];
$selectedYear = $report['year'];
$comparisonYear = $report['comparisonYear'];
$insights = $report['insights'];
$canViewValues = (bool) ($insights['canViewValues'] ?? false);
$filters = $report['filters'] ?? ['locationId' => null, 'employeeId' => null];
$filterOptions = $report['filterOptions'] ?? ['locations' => [], 'employees' => []];
?>
<div class="box report-header-box">
	<div class="box-header report-title-row">
		<div class="box-lf-ctn">
			<h2>Analytics &amp; Reports</h2>
			<p>Performance comparison</p>
		</div>
		<div class="box-rt-ctn report-filter-controls">
			<div class="report-filter-control">
				<label for="report-year">Year</label>
				<select id="report-year" aria-label="Reporting year">
					<?php foreach ($report['years'] as $year): ?>
						<option value="<?php echo e($year); ?>" <?php echo $year === $selectedYear ? 'selected' : ''; ?>>
							<?php echo e($year); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="report-filter-control">
				<label for="report-location">Location</label>
				<select id="report-location" aria-label="Report location filter">
					<option value="">All locations</option>
					<?php foreach ($filterOptions['locations'] as $location): ?>
						<option
							value="<?php echo e($location['id']); ?>"
							<?php echo (int) ($filters['locationId'] ?? 0) === $location['id'] ? 'selected' : ''; ?>
						>
							<?php echo e($location['name']); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="report-filter-control">
				<label for="report-employee">Employee</label>
				<select id="report-employee" aria-label="Report employee filter">
					<option value="">All employees</option>
					<?php foreach ($filterOptions['employees'] as $employee): ?>
						<option
							value="<?php echo e($employee['id']); ?>"
							data-location-id="<?php echo e($employee['locationId']); ?>"
							<?php echo (int) ($filters['employeeId'] ?? 0) === $employee['id'] ? 'selected' : ''; ?>
						>
							<?php echo e($employee['name']); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
	<div class="report-metrics" role="group" aria-label="Report metric">
		<?php foreach ($report['metrics'] as $metric): ?>
			<button
				type="button"
				class="report-metric-button <?php echo $metric['id'] === 'appointments' ? 'is-active' : ''; ?>"
				data-report-metric="<?php echo e($metric['id']); ?>"
				aria-pressed="<?php echo $metric['id'] === 'appointments' ? 'true' : 'false'; ?>"
			>
				<i class="fa <?php echo e($metric['icon']); ?>" aria-hidden="true"></i>
				<?php echo e($metric['buttonLabel']); ?>
			</button>
		<?php endforeach; ?>
	</div>
</div>

<section class="report-summary" aria-label="Year summary">
	<?php foreach ($report['summary'] as $stat): ?>
		<div class="report-stat" data-report-stat="<?php echo e($stat['id']); ?>">
			<div class="report-stat-icon">
				<i class="fa <?php echo e($stat['icon']); ?>" aria-hidden="true"></i>
			</div>
			<p><?php echo e($stat['label']); ?></p>
			<strong data-report-value><?php echo e(number_format((float) $stat['value'])); ?></strong>
			<span data-report-change></span>
		</div>
	<?php endforeach; ?>
</section>

<div class="box report-chart-box">
	<div class="report-panel-header">
		<div>
			<h2 id="report-chart-title"><?php echo e($initial['metric']['label']); ?></h2>
			<p id="report-chart-years"><?php echo e($selectedYear); ?> vs <?php echo e($comparisonYear); ?></p>
		</div>
		<p class="badge badge-blue report-total-change" id="report-total-change"></p>
	</div>
	<div class="report-chart-frame">
		<canvas id="report-comparison-chart"></canvas>
		<div id="report-loading" class="report-loading" hidden>
			<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
		</div>
	</div>
	<p id="report-error" class="report-error" hidden>Report data could not be loaded.</p>
</div>

<div class="report-insight-grid">
	<div class="box report-insight-box">
		<div class="report-panel-header">
			<div>
				<h2>Most booked services</h2>
				<p data-insight-year><?php echo e($selectedYear); ?> active appointments</p>
			</div>
			<?php if ($canViewValues): ?>
				<div class="report-mode-switch" data-ranking-controls="services">
					<button type="button" class="report-mode-button is-active" data-ranking-mode="appointments">Appointments</button>
					<button type="button" class="report-mode-button" data-ranking-mode="visibleValue">Value</button>
				</div>
			<?php endif; ?>
		</div>
		<div class="report-ranking-frame">
			<canvas id="report-services-chart"></canvas>
			<p class="report-empty" id="report-services-empty" hidden>No booked services in this year.</p>
		</div>
	</div>
	<div class="box report-insight-box">
		<div class="report-panel-header">
			<div>
				<h2>Location performance</h2>
				<p data-insight-year><?php echo e($selectedYear); ?> active appointments</p>
			</div>
			<?php if ($canViewValues): ?>
				<div class="report-mode-switch" data-ranking-controls="locations">
					<button type="button" class="report-mode-button is-active" data-ranking-mode="appointments">Appointments</button>
					<button type="button" class="report-mode-button" data-ranking-mode="visibleValue">Value</button>
				</div>
			<?php endif; ?>
		</div>
		<div class="report-ranking-frame">
			<canvas id="report-locations-chart"></canvas>
			<p class="report-empty" id="report-locations-empty" hidden>No booked locations in this year.</p>
		</div>
	</div>
	<div class="box report-insight-box">
		<div class="report-panel-header">
			<div>
				<h2>Employee performance</h2>
				<p data-insight-year><?php echo e($selectedYear); ?> active appointments</p>
			</div>
			<?php if ($canViewValues): ?>
				<div class="report-mode-switch" data-ranking-controls="employees">
					<button type="button" class="report-mode-button is-active" data-ranking-mode="appointments">Appointments</button>
					<button type="button" class="report-mode-button" data-ranking-mode="visibleValue">Value</button>
				</div>
			<?php endif; ?>
		</div>
		<div class="report-ranking-frame">
			<canvas id="report-employees-chart"></canvas>
			<p class="report-empty" id="report-employees-empty" hidden>No assigned employees in this year.</p>
		</div>
	</div>
</div>

<div class="box report-client-box">
	<div class="report-panel-header">
		<div>
			<h2>Repeat client activity</h2>
			<p id="report-client-year"><?php echo e($selectedYear); ?> active appointments</p>
		</div>
	</div>
	<table class="action-table report-client-table align-middle">
		<thead>
			<tr>
				<th>Client</th>
				<th>Appointments</th>
			</tr>
		</thead>
		<tbody id="report-client-body">
			<?php if ($insights['clients'] === []): ?>
				<tr>
					<td colspan="2">No booked clients in this year.</td>
				</tr>
			<?php else: ?>
				<?php foreach ($insights['clients'] as $client): ?>
					<tr>
						<td><?php echo e($client['name']); ?></td>
						<td><p class="badge badge-blue"><?php echo e($client['appointments']); ?></p></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<script type="application/json" id="report-overview-data"><?php echo json_encode(
	$report,
	JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
); ?></script>
<script>
	(function () {
		const report = JSON.parse(document.getElementById('report-overview-data').textContent);
		const metricButtons = Array.from(document.querySelectorAll('[data-report-metric]'));
		const yearSelect = document.getElementById('report-year');
		const locationSelect = document.getElementById('report-location');
		const employeeSelect = document.getElementById('report-employee');
		const loading = document.getElementById('report-loading');
		const error = document.getElementById('report-error');
		const countFormatter = new Intl.NumberFormat();
		const moneyFormatter = new Intl.NumberFormat(undefined, {
			style: 'currency',
			currency: 'EUR',
			minimumFractionDigits: 2
		});
		let selectedMetric = 'appointments';
		let selectedFormat = 'number';
		let activeRequest = null;
		let chart = null;
		const rankingCharts = { services: null, locations: null, employees: null };
		const rankingModes = { services: 'appointments', locations: 'appointments', employees: 'appointments' };

		function formatValue(value, format) {
			return format === 'currency'
				? moneyFormatter.format(value)
				: countFormatter.format(value);
		}

		function requestParams(includeMetric) {
			const params = new URLSearchParams();
			params.set('year', yearSelect.value);
			if (includeMetric) {
				params.set('metric', selectedMetric);
			}
			if (locationSelect?.value) {
				params.set('location_id', locationSelect.value);
			}
			if (employeeSelect?.value) {
				params.set('employee_id', employeeSelect.value);
			}
			return params;
		}

		function syncEmployeeOptions() {
			if (!employeeSelect || !locationSelect) {
				return;
			}

			const locationId = locationSelect.value;
			Array.from(employeeSelect.options).forEach(function (option) {
				if (option.value === '') {
					option.hidden = false;
					return;
				}
				option.hidden = locationId !== '' && option.dataset.locationId !== locationId;
			});

			if (employeeSelect.selectedOptions[0]?.hidden) {
				employeeSelect.value = '';
			}
		}

		function signedValue(value, format) {
			const prefix = value > 0 ? '+' : '';
			return prefix + formatValue(value, format);
		}

		function changeText(item, comparisonYear) {
			if (item.difference === null) {
				return 'Current total';
			}

			if (item.percent === null) {
				return Number(item.value) === 0 ? 'No change' : 'New vs ' + comparisonYear;
			}

			const sign = item.percent > 0 ? '+' : '';
			return sign + item.percent + '% vs ' + comparisonYear;
		}

		function updateSummary(summary, year) {
			summary.forEach(function (item) {
				const stat = document.querySelector('[data-report-stat="' + item.id + '"]');
				if (!stat) {
					return;
				}

				stat.querySelector('[data-report-value]').textContent = formatValue(item.value, item.format);
				stat.querySelector('[data-report-change]').textContent = changeText(item, year - 1);
			});
		}

		function updateChart(data) {
			if (typeof Chart === 'undefined') {
				error.hidden = false;
				return;
			}

			const context = document.getElementById('report-comparison-chart').getContext('2d');
			const datasets = [
				{
					label: String(data.year),
					data: data.current.values,
					backgroundColor: data.metric.color,
					borderColor: data.metric.color,
					borderWidth: 0,
					borderRadius: 4,
					maxBarThickness: 34
				},
				{
					label: String(data.comparisonYear),
					data: data.previous.values,
					backgroundColor: '#d7dbe5',
					borderColor: '#d7dbe5',
					borderWidth: 0,
					borderRadius: 4,
					maxBarThickness: 34
				}
			];

			if (chart === null) {
				chart = new Chart(context, {
					type: 'bar',
					data: { labels: data.labels, datasets: datasets },
					options: {
						animation: { duration: 240 },
						maintainAspectRatio: false,
						normalized: true,
						interaction: { intersect: false, mode: 'index' },
						plugins: {
							legend: {
								position: 'top',
								align: 'end',
								labels: { usePointStyle: true, boxWidth: 8, font: { family: 'Poppins' } }
							},
							tooltip: {
								callbacks: {
									label: function (tooltipItem) {
										return tooltipItem.dataset.label + ': '
											+ formatValue(tooltipItem.parsed.y, selectedFormat);
									}
								}
							}
						},
						scales: {
							x: { grid: { display: false }, ticks: { font: { family: 'Poppins' } } },
							y: {
								beginAtZero: true,
								grid: { color: '#f1f1f4' },
								ticks: {
									font: { family: 'Poppins' },
									callback: function (value) { return formatValue(value, selectedFormat); }
								}
							}
						}
					}
				});
				return;
			}

			chart.data.labels = data.labels;
			chart.data.datasets = datasets;
			chart.update();
		}

		function renderComparison(data) {
			selectedFormat = data.metric.format;
			document.getElementById('report-chart-title').textContent = data.metric.label;
			document.getElementById('report-chart-years').textContent = data.year + ' vs ' + data.comparisonYear;
			const totalChange = document.getElementById('report-total-change');
			totalChange.textContent = signedValue(data.difference, data.metric.format) + ' vs ' + data.comparisonYear;
			totalChange.className = 'badge badge-blue report-total-change';
			updateChart(data);
		}

		function rankingRows(type) {
			const mode = rankingModes[type];
			return report.insights[type].slice().sort(function (left, right) {
				return Number(right[mode]) - Number(left[mode]);
			}).slice(0, 6);
		}

		function rankingColor(type) {
			if (type === 'locations') {
				return '#50cd89';
			}

			if (type === 'employees') {
				return '#7e8299';
			}

			return '#019ef7';
		}

		function renderRanking(type) {
			if (typeof Chart === 'undefined') {
				error.hidden = false;
				return;
			}

			if (rankingModes[type] === 'visibleValue' && !report.insights.canViewValues) {
				rankingModes[type] = 'appointments';
			}
			const mode = rankingModes[type];
			const format = mode === 'visibleValue' ? 'currency' : 'number';
			const rows = rankingRows(type);
			const canvas = document.getElementById('report-' + type + '-chart');
			const empty = document.getElementById('report-' + type + '-empty');
			canvas.hidden = rows.length === 0;
			empty.hidden = rows.length !== 0;

			if (rows.length === 0) {
				rankingCharts[type]?.destroy();
				rankingCharts[type] = null;
				return;
			}

			const dataset = {
				data: rows.map(function (row) { return row[mode]; }),
				backgroundColor: rankingColor(type),
				borderWidth: 0,
				borderRadius: 4,
				maxBarThickness: 28
			};

			if (rankingCharts[type] === null) {
				rankingCharts[type] = new Chart(canvas.getContext('2d'), {
					type: 'bar',
					data: {
						labels: rows.map(function (row) { return row.name; }),
						datasets: [dataset]
					},
					options: {
						indexAxis: 'y',
						animation: { duration: 240 },
						maintainAspectRatio: false,
						normalized: true,
						plugins: {
							legend: { display: false },
							tooltip: {
								callbacks: {
									label: function (tooltipItem) {
										return formatValue(tooltipItem.parsed.x, format);
									}
								}
							}
						},
						scales: {
							x: {
								beginAtZero: true,
								grid: { color: '#f1f1f4' },
								ticks: {
									font: { family: 'Poppins' },
									callback: function (value) { return formatValue(value, format); }
								}
							},
							y: { grid: { display: false }, ticks: { font: { family: 'Poppins' } } }
						}
					}
				});
				return;
			}

			rankingCharts[type].data.labels = rows.map(function (row) { return row.name; });
			rankingCharts[type].data.datasets = [dataset];
			rankingCharts[type].options.scales.x.ticks.callback = function (value) {
				return formatValue(value, format);
			};
			rankingCharts[type].options.plugins.tooltip.callbacks.label = function (tooltipItem) {
				return formatValue(tooltipItem.parsed.x, format);
			};
			rankingCharts[type].update();
		}

		function renderClients() {
			const body = document.getElementById('report-client-body');
			body.replaceChildren();

			if (report.insights.clients.length === 0) {
				const row = document.createElement('tr');
				const cell = document.createElement('td');
				cell.colSpan = 2;
				cell.textContent = 'No booked clients in this year.';
				row.appendChild(cell);
				body.appendChild(row);
				return;
			}

			report.insights.clients.forEach(function (client) {
				const row = document.createElement('tr');
				const name = document.createElement('td');
				const appointments = document.createElement('td');
				const badge = document.createElement('p');
				name.textContent = client.name;
				badge.className = 'badge badge-blue';
				badge.textContent = countFormatter.format(client.appointments);
				appointments.appendChild(badge);
				row.appendChild(name);
				row.appendChild(appointments);
				body.appendChild(row);
			});
		}

		function renderInsights(insights) {
			report.insights = insights;
			document.querySelectorAll('[data-insight-year]').forEach(function (subtitle) {
				subtitle.textContent = insights.year + ' active appointments';
			});
			document.getElementById('report-client-year').textContent = insights.year + ' active appointments';
			renderRanking('services');
			renderRanking('locations');
			renderRanking('employees');
			renderClients();
		}

		async function loadData(includeSummary) {
			activeRequest?.abort();
			const request = new AbortController();
			activeRequest = request;
			loading.hidden = false;
			error.hidden = true;
			const year = Number(yearSelect.value);

			try {
				const dataParams = requestParams(true);
				const summaryParams = requestParams(false);
				const requests = [
					fetch('/reports/data?' + dataParams.toString(), {
						signal: request.signal
					})
				];
				if (includeSummary) {
					requests.push(fetch('/reports/summary?' + summaryParams.toString(), {
						signal: request.signal
					}));
					requests.push(fetch('/reports/insights?' + summaryParams.toString(), {
						signal: request.signal
					}));
				}

				const responses = await Promise.all(requests);
				if (responses.some(function (response) { return !response.ok; })) {
					throw new Error('Report request failed.');
				}

				const data = await responses[0].json();
				renderComparison(data);

				if (includeSummary) {
					const summary = await responses[1].json();
					updateSummary(summary.summary, summary.year);
					renderInsights(await responses[2].json());
				}
			} catch (requestError) {
				if (requestError.name !== 'AbortError') {
					error.hidden = false;
				}
			} finally {
				if (activeRequest === request) {
					loading.hidden = true;
				}
			}
		}

		metricButtons.forEach(function (button) {
			button.addEventListener('click', function () {
				selectedMetric = button.dataset.reportMetric;
				metricButtons.forEach(function (candidate) {
					const isActive = candidate === button;
					candidate.classList.toggle('is-active', isActive);
					candidate.setAttribute('aria-pressed', isActive ? 'true' : 'false');
				});
				loadData(false);
			});
		});

		yearSelect.addEventListener('change', function () {
			loadData(true);
		});
		locationSelect?.addEventListener('change', function () {
			syncEmployeeOptions();
			loadData(true);
		});
		employeeSelect?.addEventListener('change', function () {
			loadData(true);
		});

		document.querySelectorAll('[data-ranking-controls]').forEach(function (controls) {
			const type = controls.dataset.rankingControls;
			controls.querySelectorAll('[data-ranking-mode]').forEach(function (button) {
				button.addEventListener('click', function () {
					rankingModes[type] = button.dataset.rankingMode;
					controls.querySelectorAll('[data-ranking-mode]').forEach(function (candidate) {
						candidate.classList.toggle('is-active', candidate === button);
					});
					renderRanking(type);
				});
			});
		});

		syncEmployeeOptions();
		updateSummary(report.summary, report.year);
		renderComparison(report.initial);
		renderInsights(report.insights);
	}());
</script>
