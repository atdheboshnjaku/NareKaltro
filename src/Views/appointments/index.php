<?php
$serviceCatalog = [];
foreach ($services as $service) {
	$serviceCatalog[(string) $service->id] = [
		'id' => $service->id,
		'name' => $service->name,
		'background' => $service->background,
		'color' => $service->color,
	];
}
?>
<div class="box" id="appointments-box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Appointments Calendar</h2>
			<p><?php echo e($upcomingCount); ?> upcoming appointments in total</p>
		</div>
		<?php if ($canManageSettings): ?>
			<div class="box-rt-ctn">
				<a href="/appointments/settings">
					<button class="action-btn align-middle" title="Appointment settings">
						<i class="fa fa-cog" aria-hidden="true"></i>&nbsp; Settings
					</button>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<br>
	<div>
		<button class="hideme">Add Appointment</button>
	</div>
	<br>
	<div id="calendar"></div>

	<div class="modal fade" id="openappointment" tabindex="-1" aria-labelledby="appointment-details-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="appointment-details-title">
						<?php echo $canUpdate ? 'Appointment Info/Edit' : 'Appointment Info'; ?>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="editevent" method="post" class="add-form">
						<?php echo csrf_field(); ?>
						<input type="hidden" name="id" id="edit-appointment-id">
						<input type="hidden" name="client_id" id="edit-client-id">
						<span>Client</span>
						<p class="appointment-client-row" data-edit-client-row>
							<input type="text" id="edit-client-name" disabled>
							<?php if ($canViewClientHistory): ?>
								<button
									type="button"
									class="btn btn-icon appointment-history-btn"
									title="View recent client history"
									aria-label="View recent client history"
									data-edit-client-history
									hidden
								>
									<i class="fa fa-history" aria-hidden="true"></i>
								</button>
							<?php endif; ?>
						</p>
						<span>Location</span>
						<p>
							<?php if ($canUpdate): ?>
								<select class="form-select" name="location_id" id="edit-location-id">
									<option disabled value="">Choose location</option>
									<optgroup label="User location">
										<?php foreach ($locations as $location): ?>
											<option value="<?php echo e($location->id); ?>"><?php echo e($location->name); ?></option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							<?php else: ?>
								<input type="text" id="edit-location-name" disabled>
							<?php endif; ?>
						</p>
						<span>Employee</span>
						<p>
							<?php if ($canUpdate): ?>
								<select class="form-select" name="employee_id" id="edit-employee-id" required>
									<option disabled value="">Choose employee</option>
									<optgroup label="Staff">
										<?php foreach ($employees as $employee): ?>
											<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							<?php else: ?>
								<input type="text" id="edit-employee-name" disabled>
							<?php endif; ?>
						</p>
						<span>Services</span>
						<?php if ($canUpdate): ?>
							<p>
								<select multiple class="csc-select required-field" name="service_ids[]" id="edit-service-ids" required>
									<?php foreach ($services as $service): ?>
										<option value="<?php echo e($service->id); ?>"><?php echo e($service->name); ?></option>
									<?php endforeach; ?>
								</select>
								<span class="required-warning warn">Please choose at least 1 service</span>
							</p>
							<p data-edit-readonly-costs></p>
							<div id="edit-service-costs"></div>
						<?php else: ?>
							<p data-detail-services></p>
						<?php endif; ?>
						<span>Appointment Start Date &amp; Time</span>
						<p>
							<input
								type="<?php echo $canUpdate ? 'datetime-local' : 'text'; ?>"
								name="start_date"
								id="edit-start-date"
								<?php echo $canUpdate ? '' : 'disabled'; ?>
							>
						</p>
						<div data-edit-end-group>
							<span>Appointment Ending Date &amp; Time</span>
							<p>
								<input
									type="<?php echo $canUpdate ? 'datetime-local' : 'text'; ?>"
									name="end_date"
									id="edit-end-date"
									<?php echo $canUpdate ? '' : 'disabled'; ?>
								>
							</p>
						</div>
						<span>Appointment Notes</span>
						<p>
							<textarea rows="4" name="appointment_notes" id="edit-appointment-notes" <?php echo $canUpdate ? '' : 'disabled'; ?>></textarea>
						</p>
						<?php if ($canUpdate || $canDelete): ?>
							<p>
								<?php if ($canUpdate): ?>
									<input type="button" id="submitAppUpdate" class="blue-btn alab" value="Update">
								<?php endif; ?>
								<?php if ($canDelete): ?>
									<input type="button" id="submitRemove" class="red-btn fl-rt" value="Delete">
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</form>
				</div>
			</div>
		</div>
	</div>

	<?php if ($canCreate): ?>
		<div class="modal fade" id="addappointment" tabindex="-1" aria-labelledby="add-appointment-title" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="add-appointment-title">Create Appointment</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form id="addevent" method="post" class="add-form">
							<?php echo csrf_field(); ?>
							<span>Location</span>
							<p>
								<select class="form-select" name="location_id" id="add-location-id">
									<option disabled value="">Choose location</option>
									<optgroup label="User location">
										<?php foreach ($locations as $location): ?>
											<option
												value="<?php echo e($location->id); ?>"
												<?php echo $currentUser->locationId === $location->id ? 'selected' : ''; ?>
											>
												<?php echo e($location->name); ?>
											</option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							</p>
							<span>Employee</span>
							<p>
								<select class="form-select" name="employee_id" id="add-employee-id" required>
									<option disabled value="">Choose employee</option>
									<optgroup label="Staff">
										<?php foreach ($employees as $employee): ?>
											<option
												value="<?php echo e($employee->id); ?>"
												<?php echo $currentUser->id === $employee->id ? 'selected' : ''; ?>
											>
												<?php echo e($employee->name); ?>
											</option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							</p>
							<span>Client</span>
							<p class="appointment-client-row" data-add-client-row>
								<select class="csc-select client_id required-field" name="client_id" id="add-client-id" required>
									<option value="">Select Client</option>
								</select>
								<?php if ($canViewClientHistory): ?>
									<button
										type="button"
										class="btn btn-icon appointment-history-btn"
										title="View recent client history"
										aria-label="View recent client history"
										data-add-client-history
										hidden
									>
										<i class="fa fa-history" aria-hidden="true"></i>
									</button>
								<?php endif; ?>
							</p>
							<span class="required-warning warn">Please choose a client</span>
							<span>Services</span>
							<p>
								<select class="csc-select required-field" name="service_ids[]" id="add-service-ids" multiple required>
									<?php foreach ($services as $service): ?>
										<option value="<?php echo e($service->id); ?>"><?php echo e($service->name); ?></option>
									<?php endforeach; ?>
								</select>
								<span class="required-warning warn">Please choose at least 1 service</span>
							</p>
							<div id="add-service-costs"></div>
							<span>Appointment Start Date &amp; Time</span>
							<p>
								<input type="datetime-local" name="start_date" id="add-start-date">
							</p>
							<div data-add-end-group hidden>
								<span>Appointment Ending Date &amp; Time</span>
								<p>
									<input type="datetime-local" name="end_date" id="add-end-date">
								</p>
							</div>
							<span>Appointment Notes</span>
							<p>
								<textarea rows="4" name="appointment_notes" id="add-appointment-notes"></textarea>
							</p>
						</form>
						<input type="button" id="submitApp" class="blue-btn alab" value="Add Appointment">
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($canCreateClient): ?>
		<div class="modal fade" id="addclient" tabindex="-1" aria-labelledby="add-client-title" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="add-client-title">Add Client</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form id="addclientform" method="post" class="add-form">
							<?php echo csrf_field(); ?>
							<div class="profile-card">
								<div class="client-profile-pic" data-new-client-initials></div>
								<p>
									<span>Phone</span>
									<input type="tel" name="number" placeholder="Client number" autocomplete="off">
								</p>
								<p>
									<span>Country</span>
									<select class="csc-select" name="country" id="new-client-country">
										<option value="0">Select Country</option>
										<?php foreach ($countries as $country): ?>
											<option value="<?php echo e($country->id); ?>"><?php echo e($country->name); ?></option>
										<?php endforeach; ?>
									</select>
								</p>
								<p>
									<span>State</span>
									<select class="csc-select" name="state" id="new-client-state">
										<option value="0">Select State</option>
									</select>
								</p>
								<p>
									<span>City</span>
									<select class="csc-select" name="city" id="new-client-city">
										<option value="0">Select City</option>
									</select>
								</p>
							</div>
							<div class="profile-info">
								<p>
									<span>Location</span>
									<select name="location_id">
										<option disabled value="">Choose location</option>
										<optgroup label="User location">
											<?php foreach ($locations as $location): ?>
												<option
													value="<?php echo e($location->id); ?>"
													<?php echo $currentUser->locationId === $location->id ? 'selected' : ''; ?>
												>
													<?php echo e($location->name); ?>
												</option>
											<?php endforeach; ?>
										</optgroup>
									</select>
								</p>
								<p>
									<span>Email</span>
									<input type="email" name="email" autocomplete="off">
								</p>
								<p>
									<span>Client name</span>
									<input type="text" name="name" data-new-client-name required>
								</p>
								<p>
									<input type="button" id="submitAddClient" class="blue-btn alab" value="Add client">
								</p>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php if ($canViewClientHistory): ?>
	<div class="modal fade" id="appointment-client-history" tabindex="-1" aria-labelledby="appointment-history-title" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="appointment-history-title">Recent History</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<table class="action-table align-middle history-table">
						<thead>
							<tr>
								<th>Location</th>
								<th>Service</th>
								<th>Start</th>
								<th>End</th>
								<th class="align-left">Notes</th>
							</tr>
						</thead>
						<tbody data-history-rows></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		const mayCreate = <?php echo json_encode($canCreate); ?>;
		const mayUpdate = <?php echo json_encode($canUpdate); ?>;
		const mayDelete = <?php echo json_encode($canDelete); ?>;
		const mayCreateClient = <?php echo json_encode($canCreateClient); ?>;
		const hasLocations = <?php echo json_encode($locations !== []); ?>;
		const serviceCatalog = <?php echo json_encode($serviceCatalog, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		const currentUserId = <?php echo json_encode((string) $currentUser->id); ?>;
		const currentUserName = <?php echo json_encode($currentUser->name); ?>;
		const calendarElement = document.getElementById('calendar');
		const editElement = document.getElementById('openappointment');
		const editModal = new bootstrap.Modal(editElement);
		const addElement = document.getElementById('addappointment');
		const addModal = addElement ? new bootstrap.Modal(addElement) : null;
		const newClientElement = document.getElementById('addclient');
		const newClientModal = newClientElement ? new bootstrap.Modal(newClientElement) : null;
		const historyElement = document.getElementById('appointment-client-history');
		const historyModal = historyElement ? new bootstrap.Modal(historyElement) : null;
		let returnFromHistory = null;
		let returnFromNewClient = null;
		let originalEditCosts = {};
		let currentEvent = null;

		const setHistoryAction = function (row, button, clientId) {
			if (!button) {
				return;
			}

			const available = String(clientId || '') !== '';
			button.dataset.clientId = available ? clientId : '';
			button.hidden = !available;
			row?.classList.toggle('has-history-action', available);
		};

		if (!hasLocations) {
			swal({
				title: 'No location associated with your account!',
				text: 'Please add a location first.',
				icon: 'info',
				button: 'Add Location',
				closeOnClickOutside: false,
				closeOnEsc: false
			}).then(function (willAdd) {
				if (willAdd) {
					window.location.href = '/locations';
				}
			});
		}

		const toInputValue = function (value) {
			return value ? String(value).replace(' ', 'T').slice(0, 16) : '';
		};

		const localDateTime = function (date) {
			if (!date) {
				return '';
			}
			const pad = function (value) {
				return String(value).padStart(2, '0');
			};
			return [
				date.getFullYear(),
				pad(date.getMonth() + 1),
				pad(date.getDate())
			].join('-') + 'T' + [pad(date.getHours()), pad(date.getMinutes()), pad(date.getSeconds())].join(':');
		};

		const serviceBadge = function (service) {
			const badge = document.createElement('span');
			badge.className = 'badge';
			badge.style.backgroundColor = service.background;
			badge.style.color = service.color;
			badge.textContent = service.name + (service.cost !== null && service.cost !== undefined ? ': EUR ' + service.cost : '');
			return badge;
		};

		const appendBadges = function (container, services) {
			if (!container) {
				return;
			}
			container.replaceChildren();
			services.forEach(function (service) {
				container.appendChild(serviceBadge(service));
				container.appendChild(document.createTextNode(' '));
			});
		};

		const errorsText = function (payload) {
			const errors = payload && payload.errors ? Object.values(payload.errors) : [];
			return errors.length ? errors.join('\n') : 'Unable to complete the appointment action.';
		};

		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

		const jsonRequest = function (url, options) {
			return fetch(url, options).then(function (response) {
				return response.json().catch(function () {
					return {};
				}).then(function (payload) {
					if (!response.ok) {
						throw new Error(errorsText(payload));
					}
					return payload;
				});
			});
		};

		const formRequest = function (url, formData) {
			return jsonRequest(url, {
				method: 'POST',
				headers: {'Accept': 'application/json', 'X-CSRF-Token': csrfToken},
				body: new URLSearchParams(formData)
			});
		};

		const capabilityRequest = function (appointmentId, locationId, serviceIds) {
			const params = new URLSearchParams();
			params.set('location_id', locationId);
			if (appointmentId) {
				params.set('appointment_id', appointmentId);
			}
			serviceIds.forEach(function (id) {
				params.append('service_ids[]', id);
			});
			return jsonRequest('/appointments/capabilities?' + params.toString(), {
				headers: {'Accept': 'application/json'}
			});
		};

		const renderCostInputs = function (container, readonlyContainer, serviceIds, capabilities, values) {
			container.replaceChildren();
			if (readonlyContainer) {
				readonlyContainer.replaceChildren();
			}
			serviceIds.forEach(function (serviceId) {
				const service = serviceCatalog[String(serviceId)] || {
					id: serviceId,
					name: 'Unavailable service',
					background: '#f1faff',
					color: '#009ef7'
				};
				const access = capabilities.serviceCosts[serviceId] || capabilities.serviceCosts[String(serviceId)] || {};
				const value = values[serviceId] || '';
				if (!access.canUpdate) {
					if (readonlyContainer && access.canView && value !== '') {
						readonlyContainer.appendChild(serviceBadge(Object.assign({}, service, {cost: value})));
						readonlyContainer.appendChild(document.createTextNode(' '));
					}
					return;
				}

				const wrapper = document.createElement('div');
				wrapper.className = 'mb-2';
				const label = document.createElement('label');
				label.htmlFor = container.id + '-cost-' + serviceId;
				label.textContent = service.name + ' price:';
				const input = document.createElement('input');
				input.type = 'number';
				input.step = '0.01';
				input.min = '0';
				input.name = 'service_cost[' + serviceId + ']';
				input.id = label.htmlFor;
				input.className = 'form-control';
				input.value = value;
				wrapper.append(label, input);
				container.appendChild(wrapper);
			});
		};

		const ensureOption = function (select, value, text) {
			if (!select || select.querySelector('option[value="' + CSS.escape(String(value)) + '"]')) {
				return;
			}
			select.appendChild(new Option(text, String(value)));
		};

		const editServices = document.getElementById('edit-service-ids');
		const editLocation = document.getElementById('edit-location-id');
		const editEmployee = document.getElementById('edit-employee-id');
		const editCostContainer = document.getElementById('edit-service-costs');
		const editReadonlyCosts = document.querySelector('[data-edit-readonly-costs]');
		const editEndGroup = document.querySelector('[data-edit-end-group]');

		const refreshEditCapabilities = function () {
			if (!mayUpdate || !currentEvent || !editServices || !editLocation) {
				return Promise.resolve();
			}
			const ids = $(editServices).val() || [];
			if (!editLocation.value || ids.length === 0) {
				editCostContainer.replaceChildren();
				editReadonlyCosts.replaceChildren();
				return Promise.resolve();
			}
			editCostContainer.querySelectorAll('input[name^="service_cost"]').forEach(function (input) {
				const match = input.name.match(/\[(\d+)\]/);
				if (match) {
					originalEditCosts[match[1]] = input.value;
				}
			});
			return capabilityRequest(currentEvent.id, editLocation.value, ids).then(function (capabilities) {
				editEndGroup.hidden = !capabilities.canUseEndTime;
				ids.forEach(function (serviceId) {
					const costAccess = capabilities.serviceCosts[serviceId] || capabilities.serviceCosts[String(serviceId)] || {};
					if (originalEditCosts[serviceId] === undefined && costAccess.cost !== null && costAccess.cost !== undefined) {
						originalEditCosts[serviceId] = costAccess.cost;
					}
				});
				renderCostInputs(editCostContainer, editReadonlyCosts, ids, capabilities, originalEditCosts);
			});
		};

		const populateEdit = function (event) {
			currentEvent = event;
			const details = event.extendedProps;
			document.getElementById('edit-appointment-id').value = event.id;
			document.getElementById('edit-client-id').value = details.clientId;
			document.getElementById('edit-client-name').value = details.clientName;
			document.getElementById('edit-start-date').value = mayUpdate ? toInputValue(details.startDate) : String(details.startDate).replace('T', ' ');
			document.getElementById('edit-end-date').value = mayUpdate ? toInputValue(details.endDate) : String(details.endDate || '').replace('T', ' ');
			document.getElementById('edit-appointment-notes').value = details.notes;
			editEndGroup.hidden = !details.canUseEndTime;

			const historyRow = editElement.querySelector('[data-edit-client-row]');
			const historyButton = editElement.querySelector('[data-edit-client-history]');
			setHistoryAction(historyRow, historyButton, details.clientId);

			if (!mayUpdate) {
				document.getElementById('edit-location-name').value = details.locationName;
				document.getElementById('edit-employee-name').value = details.employeeName || 'Unassigned';
				appendBadges(document.querySelector('[data-detail-services]'), details.services);
				return;
			}

			ensureOption(editLocation, details.locationId, details.locationName);
			editLocation.value = String(details.locationId);
			if (details.employeeId) {
				ensureOption(editEmployee, details.employeeId, details.employeeName || 'Unavailable staff');
				editEmployee.value = String(details.employeeId);
			} else {
				editEmployee.value = '';
			}
			details.services.forEach(function (service) {
				ensureOption(editServices, service.id, service.name);
			});
			$(editServices).val(details.services.map(function (service) {
				return String(service.id);
			})).trigger('change.select2');
			originalEditCosts = {};
			details.services.forEach(function (service) {
				if (service.cost !== null) {
					originalEditCosts[service.id] = service.cost;
				}
			});
			refreshEditCapabilities().catch(function (error) {
				swal(error.message, {icon: 'error'});
			});
		};

		if (editServices) {
			$(editServices).select2({placeholder: 'Select Services', dropdownParent: $(editElement)});
			$(editServices).on('change', refreshEditCapabilities);
			editLocation.addEventListener('change', refreshEditCapabilities);
		}

		const addServices = document.getElementById('add-service-ids');
		const addLocation = document.getElementById('add-location-id');
		const addEmployee = document.getElementById('add-employee-id');
		const addCosts = document.getElementById('add-service-costs');
		const addEndGroup = document.querySelector('[data-add-end-group]');

		const resetAddEmployee = function () {
			if (!addEmployee || !currentUserId) {
				return;
			}
			ensureOption(addEmployee, currentUserId, currentUserName);
			addEmployee.value = currentUserId;
		};
		resetAddEmployee();

		const refreshAddCapabilities = function () {
			if (!mayCreate || !addServices || !addLocation) {
				return Promise.resolve();
			}
			const ids = $(addServices).val() || [];
			if (!addLocation.value) {
				addCosts.replaceChildren();
				return Promise.resolve();
			}
			return capabilityRequest(null, addLocation.value, ids).then(function (capabilities) {
				addEndGroup.hidden = !capabilities.canUseEndTime;
				renderCostInputs(addCosts, null, ids, capabilities, {});
			});
		};

		if (addServices) {
			$(addServices).select2({placeholder: 'Select Services', dropdownParent: $(addElement)});
			$(addServices).on('change', refreshAddCapabilities);
			addLocation.addEventListener('change', refreshAddCapabilities);
		}

		const addClient = document.getElementById('add-client-id');
		const addClientRow = addElement?.querySelector('[data-add-client-row]');
		if (addClient) {
			$(addClient).select2({
				placeholder: 'Select Client',
				dropdownParent: $(addElement),
				ajax: {
					url: '/appointments/clients/search',
					dataType: 'json',
					delay: 250,
					cache: true,
					data: function (params) {
						return {
							term: params.term || '',
							page: params.page || 1
						};
					},
					processResults: function (data) {
						return data;
					}
				},
				language: mayCreateClient ? {
					noResults: function () {
						return '<input value="Add Client" style="width: 100%" type="button" class="btn blue-btn wt-on-hv" data-open-client-modal>';
					}
				} : undefined,
				escapeMarkup: function (markup) {
					return markup;
				}
			});
			$(addClient).on('change', function () {
				const historyButton = addElement.querySelector('[data-add-client-history]');
				setHistoryAction(addClientRow, historyButton, addClient.value);
			});
			$(document).on('click', '[data-open-client-modal]', function () {
				returnFromNewClient = addModal;
				addModal.hide();
				newClientModal.show();
			});
		}

		const historyRows = historyElement ? historyElement.querySelector('[data-history-rows]') : null;
		const historyTitle = historyElement ? historyElement.querySelector('#appointment-history-title') : null;
		const openHistory = function (clientId, sourceModal) {
			if (!historyModal || !historyRows || !clientId) {
				return;
			}
			jsonRequest('/appointments/client-history?client_id=' + encodeURIComponent(clientId), {
				headers: {'Accept': 'application/json'}
			}).then(function (history) {
				historyTitle.textContent = 'Recent History: ' + history.client.name;
				historyRows.replaceChildren();
				history.entries.forEach(function (entry) {
					const row = document.createElement('tr');
					const location = document.createElement('td');
					const service = document.createElement('td');
					const start = document.createElement('td');
					const end = document.createElement('td');
					const notes = document.createElement('td');
					location.textContent = entry.locationName;
					if (!entry.active) {
						const cancelled = document.createElement('p');
						cancelled.className = 'badge badge-red';
						cancelled.textContent = 'Cancelled/Deleted';
						location.append(document.createElement('br'), cancelled);
					}
					appendBadges(service, entry.services);
					start.textContent = String(entry.startDate).replace('T', ' ');
					end.textContent = entry.endDate ? String(entry.endDate).replace('T', ' ') : '';
					notes.className = 'align-left';
					notes.textContent = entry.notes;
					row.append(location, service, start, end, notes);
					historyRows.appendChild(row);
				});
				if (history.entries.length === 0) {
					const row = document.createElement('tr');
					const cell = document.createElement('td');
					cell.colSpan = 5;
					cell.textContent = 'No appointment history available.';
					row.appendChild(cell);
					historyRows.appendChild(row);
				}
				returnFromHistory = sourceModal;
				sourceModal.hide();
				historyModal.show();
			}).catch(function (error) {
				swal(error.message, {icon: 'error'});
			});
		};

		editElement.querySelector('[data-edit-client-history]')?.addEventListener('click', function (event) {
			openHistory(event.currentTarget.dataset.clientId, editModal);
		});
		addElement?.querySelector('[data-add-client-history]')?.addEventListener('click', function (event) {
			openHistory(event.currentTarget.dataset.clientId, addModal);
		});
		historyElement?.addEventListener('hidden.bs.modal', function () {
			if (returnFromHistory) {
				const target = returnFromHistory;
				returnFromHistory = null;
				target.show();
			}
		});

		const calendar = new FullCalendar.Calendar(calendarElement, {
			initialView: 'dayGridMonth',
			timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
			dayMaxEventRows: true,
			editable: mayUpdate,
			selectable: mayCreate,
			eventLongPressDelay: 500,
			selectLongPressDelay: 0.5,
			eventDisplay: 'block',
			eventTimeFormat: {
				hour: 'numeric',
				minute: '2-digit'
			},
			headerToolbar: {
				left: 'prev,next today',
				center: 'title',
				right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
			},
			events: {
				url: '/appointments/events',
				method: 'POST',
				extraParams: function () {
					return {_csrf_token: csrfToken};
				}
			},
			eventClick: function (info) {
				populateEdit(info.event);
				editModal.show();
			},
			select: function (info) {
				if (!addModal) {
					return;
				}
				document.getElementById('addevent').reset();
				resetAddEmployee();
				$(addServices).val(null).trigger('change');
				$(addClient).val(null).trigger('change');
				document.getElementById('add-start-date').value = localDateTime(info.start).slice(0, 16);
				addModal.show();
			},
			eventDrop: function (info) {
				const data = new FormData();
				data.append('id', info.event.id);
				data.append('start_date', localDateTime(info.event.start));
				data.append('end_date', localDateTime(info.event.end));
				formRequest('/appointments/reschedule', data).then(function () {
					calendar.refetchEvents();
				}).catch(function (error) {
					info.revert();
					swal(error.message, {icon: 'error'});
				});
			},
			eventResize: function (info) {
				const data = new FormData();
				data.append('id', info.event.id);
				data.append('start_date', localDateTime(info.event.start));
				data.append('end_date', localDateTime(info.event.end));
				formRequest('/appointments/reschedule', data).then(function () {
					calendar.refetchEvents();
				}).catch(function (error) {
					info.revert();
					swal(error.message, {icon: 'error'});
				});
			}
		});
		calendar.render();

		document.getElementById('submitApp')?.addEventListener('click', function () {
			formRequest('/appointments/store', new FormData(document.getElementById('addevent')))
				.then(function () {
					addModal.hide();
					calendar.refetchEvents();
				})
				.catch(function (error) {
					swal(error.message, {icon: 'error'});
				});
		});

		document.getElementById('submitAppUpdate')?.addEventListener('click', function () {
			formRequest('/appointments/update', new FormData(document.getElementById('editevent')))
				.then(function () {
					editModal.hide();
					calendar.refetchEvents();
				})
				.catch(function (error) {
					swal(error.message, {icon: 'error'});
				});
		});

		document.getElementById('submitRemove')?.addEventListener('click', function () {
			swal({
				title: 'Are you sure?',
				text: 'This appointment will be cancelled and kept in client history.',
				icon: 'warning',
				buttons: true,
				dangerMode: true
			}).then(function (confirmed) {
				if (!confirmed) {
					return;
				}
				const data = new FormData();
				data.append('id', document.getElementById('edit-appointment-id').value);
				formRequest('/appointments/cancel', data).then(function () {
					editModal.hide();
					calendar.refetchEvents();
					swal('Appointment Deleted Successfully!', {icon: 'success'});
				}).catch(function (error) {
					swal(error.message, {icon: 'error'});
				});
			});
		});

		if (newClientElement) {
			const country = document.getElementById('new-client-country');
			const state = document.getElementById('new-client-state');
			const city = document.getElementById('new-client-city');
			$(newClientElement).find('.csc-select').select2({dropdownParent: $(newClientElement)});
			newClientElement.querySelector('[data-new-client-name]').addEventListener('input', function (event) {
				const initials = event.currentTarget.value.trim().split(/\s+/).filter(Boolean).slice(0, 2).map(function (word) {
					return word.charAt(0).toUpperCase();
				}).join('');
				newClientElement.querySelector('[data-new-client-initials]').textContent = initials;
			});
			$(country).on('change', function () {
				state.replaceChildren(new Option('Select State', '0'));
				city.replaceChildren(new Option('Select City', '0'));
				if (country.value === '0') {
					return;
				}
				$.getJSON('/clients/geography/states', {country_id: country.value}, function (options) {
					options.forEach(function (option) {
						state.appendChild(new Option(option.name, option.id));
					});
					$(state).trigger('change.select2');
				});
			});
			$(state).on('change', function () {
				city.replaceChildren(new Option('Select City', '0'));
				if (state.value === '0') {
					return;
				}
				$.getJSON('/clients/geography/cities', {
					country_id: country.value,
					state_id: state.value
				}, function (options) {
					options.forEach(function (option) {
						city.appendChild(new Option(option.name, option.id));
					});
					$(city).trigger('change.select2');
				});
			});
			document.getElementById('submitAddClient').addEventListener('click', function () {
				formRequest('/appointments/clients/store', new FormData(document.getElementById('addclientform')))
					.then(function (client) {
						ensureOption(addClient, client.id, client.name);
						$(addClient).val(String(client.id)).trigger('change');
						newClientModal.hide();
						returnFromNewClient.show();
						returnFromNewClient = null;
					})
					.catch(function (error) {
						swal(error.message, {icon: 'error'});
					});
			});
		}
	});
</script>
