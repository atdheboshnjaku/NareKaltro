<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Billing;

enum PlanFeature: string
{
	case BasicReports = 'basic_reports';
	case AdvancedReports = 'advanced_reports';
	case AccessPolicies = 'access_policies';
	case ClientHistory = 'client_history';
	case PrioritySupport = 'priority_support';
}
