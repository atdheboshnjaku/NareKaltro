<?php

declare(strict_types=1);

use Fin\Narekaltro\Core\Application;
use Fin\Narekaltro\Core\Container;
use Fin\Narekaltro\Core\Router;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\AccessControl;
use Fin\Narekaltro\Domain\Auth\AccessPolicyRepository;
use Fin\Narekaltro\Domain\Auth\AccountPolicyProvisioner;
use Fin\Narekaltro\Domain\Auth\AuthenticationRepository;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\CurrentUserProvider;
use Fin\Narekaltro\Domain\Auth\PasswordResetMailer;
use Fin\Narekaltro\Domain\Auth\PasswordResetThrottle;
use Fin\Narekaltro\Domain\Auth\PasswordResetTokenService;
use Fin\Narekaltro\Domain\Auth\RegistrationMailer;
use Fin\Narekaltro\Domain\Auth\RegistrationThrottle;
use Fin\Narekaltro\Domain\Appointments\AppointmentAccessControl;
use Fin\Narekaltro\Domain\Appointments\AppointmentCalendarRepository;
use Fin\Narekaltro\Domain\Appointments\AppointmentReferenceRepository;
use Fin\Narekaltro\Domain\Appointments\AppointmentWriteRepository;
use Fin\Narekaltro\Domain\Billing\AccountSubscriptionRepository;
use Fin\Narekaltro\Domain\Billing\PlanCatalog;
use Fin\Narekaltro\Domain\Billing\PlanEntitlementService;
use Fin\Narekaltro\Domain\Billing\PlanUsageRepository;
use Fin\Narekaltro\Domain\Clients\ClientHistoryRepository;
use Fin\Narekaltro\Domain\Clients\ClientRepository;
use Fin\Narekaltro\Domain\Clients\GeographyRepository;
use Fin\Narekaltro\Domain\Locations\LocationRepository;
use Fin\Narekaltro\Domain\Reports\ReportRepository;
use Fin\Narekaltro\Domain\Reports\ReportAccessControl;
use Fin\Narekaltro\Domain\Services\ServiceRepository;
use Fin\Narekaltro\Domain\Shared\CacheStore;
use Fin\Narekaltro\Domain\Shared\TransactionManager;
use Fin\Narekaltro\Domain\Staff\StaffRepository;
use Fin\Narekaltro\Infrastructure\Auth\CachedAccessPolicyRepository;
use Fin\Narekaltro\Infrastructure\Auth\MysqliAccessPolicyRepository;
use Fin\Narekaltro\Infrastructure\Auth\FileRegistrationThrottle;
use Fin\Narekaltro\Infrastructure\Auth\MailPasswordResetMailer;
use Fin\Narekaltro\Infrastructure\Auth\MailRegistrationMailer;
use Fin\Narekaltro\Infrastructure\Auth\MysqliAccountPolicyProvisioner;
use Fin\Narekaltro\Infrastructure\Auth\MysqliAuthenticationRepository;
use Fin\Narekaltro\Infrastructure\Appointments\MysqliAppointmentReferenceRepository;
use Fin\Narekaltro\Infrastructure\Appointments\MysqliAppointmentCalendarRepository;
use Fin\Narekaltro\Infrastructure\Appointments\MysqliAppointmentWriteRepository;
use Fin\Narekaltro\Infrastructure\Auth\SessionCurrentUserProvider;
use Fin\Narekaltro\Infrastructure\Billing\MysqliAccountSubscriptionRepository;
use Fin\Narekaltro\Infrastructure\Billing\MysqliPlanUsageRepository;
use Fin\Narekaltro\Infrastructure\Cache\CacheStoreFactory;
use Fin\Narekaltro\Infrastructure\Clients\CachedGeographyRepository;
use Fin\Narekaltro\Infrastructure\Clients\MysqliClientHistoryRepository;
use Fin\Narekaltro\Infrastructure\Clients\MysqliClientRepository;
use Fin\Narekaltro\Infrastructure\Clients\MysqliGeographyRepository;
use Fin\Narekaltro\Infrastructure\Database\Connection;
use Fin\Narekaltro\Infrastructure\Database\MysqliTransactionManager;
use Fin\Narekaltro\Infrastructure\Locations\CachedLocationRepository;
use Fin\Narekaltro\Infrastructure\Locations\MysqliLocationRepository;
use Fin\Narekaltro\Infrastructure\Reports\MysqliReportRepository;
use Fin\Narekaltro\Infrastructure\Services\CachedServiceRepository;
use Fin\Narekaltro\Infrastructure\Services\MysqliServiceRepository;
use Fin\Narekaltro\Infrastructure\Staff\CachedStaffRepository;
use Fin\Narekaltro\Infrastructure\Staff\MysqliStaffRepository;
use Fin\Narekaltro\Support\Environment;

require_once __DIR__ . '/../Support/helpers.php';

$basePath = dirname(__DIR__, 2);

$container = new Container();
$container->set('base_path', $basePath);
$container->set(View::class, fn () => new View($basePath . '/src/Views'));
$container->set(Connection::class, fn () => Connection::fromEnv($basePath));
$container->set(CacheStore::class, fn () => CacheStoreFactory::fromEnv($basePath));
$container->set(TransactionManager::class, fn (Container $container) => new MysqliTransactionManager(
	$container->get(Connection::class)
));
$container->set(AccessPolicyRepository::class, fn (Container $container) => new CachedAccessPolicyRepository(
	new MysqliAccessPolicyRepository($container->get(Connection::class)),
	$container->get(CacheStore::class)
));
$container->set(AuthenticationRepository::class, fn (Container $container) => new MysqliAuthenticationRepository(
	$container->get(Connection::class)
));
$container->set(AccountPolicyProvisioner::class, fn (Container $container) => new MysqliAccountPolicyProvisioner(
	$container->get(Connection::class)->mysqli()
));
$container->set(RegistrationMailer::class, fn () => new MailRegistrationMailer());
$container->set(PasswordResetMailer::class, fn () => new MailPasswordResetMailer());
$container->set(RegistrationThrottle::class, fn () => new FileRegistrationThrottle(
	sys_get_temp_dir() . '/narekaltro/auth-registration-throttle.json'
));
$container->set(PasswordResetThrottle::class, fn () => new FileRegistrationThrottle(
	sys_get_temp_dir() . '/narekaltro/auth-password-reset-throttle.json'
));
$container->set(PasswordResetTokenService::class, function (Container $container): PasswordResetTokenService {
	$container->get(Connection::class);
	$secret = Environment::get('APP_KEY', '') ?? '';
	if ($secret === '') {
		$secret = hash('sha256', implode('|', [
			$container->get('base_path'),
			Environment::get('DB_USER', '') ?? '',
			Environment::get('DB_PASS', '') ?? '',
			Environment::get('DB_NAME', '') ?? '',
		]));
	}

	return new PasswordResetTokenService($secret);
});
$container->set(ServiceRepository::class, fn (Container $container) => new CachedServiceRepository(
	new MysqliServiceRepository($container->get(Connection::class)),
	$container->get(CacheStore::class)
));
$container->set(LocationRepository::class, fn (Container $container) => new CachedLocationRepository(
	new MysqliLocationRepository($container->get(Connection::class)),
	$container->get(CacheStore::class)
));
$container->set(ClientRepository::class, fn (Container $container) => new MysqliClientRepository(
	$container->get(Connection::class)
));
$container->set(GeographyRepository::class, fn (Container $container) => new CachedGeographyRepository(
	new MysqliGeographyRepository($container->get(Connection::class)),
	$container->get(CacheStore::class)
));
$container->set(ClientHistoryRepository::class, fn (Container $container) => new MysqliClientHistoryRepository(
	$container->get(Connection::class)
));
$container->set(AppointmentReferenceRepository::class, fn (Container $container) => new MysqliAppointmentReferenceRepository(
	$container->get(Connection::class)
));
$container->set(AppointmentCalendarRepository::class, fn (Container $container) => new MysqliAppointmentCalendarRepository(
	$container->get(Connection::class)
));
$container->set(AppointmentWriteRepository::class, fn (Container $container) => new MysqliAppointmentWriteRepository(
	$container->get(Connection::class)
));
$container->set(StaffRepository::class, fn (Container $container) => new CachedStaffRepository(
	new MysqliStaffRepository($container->get(Connection::class)),
	$container->get(CacheStore::class)
));
$container->set(ReportRepository::class, fn (Container $container) => new MysqliReportRepository(
	$container->get(Connection::class)
));
$container->set(PlanCatalog::class, fn () => new PlanCatalog());
$container->set(AccountSubscriptionRepository::class, fn (Container $container) => new MysqliAccountSubscriptionRepository(
	$container->get(Connection::class)
));
$container->set(PlanUsageRepository::class, fn (Container $container) => new MysqliPlanUsageRepository(
	$container->get(Connection::class)
));
$container->set(PlanEntitlementService::class, fn (Container $container) => new PlanEntitlementService(
	$container->get(PlanCatalog::class),
	$container->get(AccountSubscriptionRepository::class),
	$container->get(PlanUsageRepository::class)
));
$container->set(ReportAccessControl::class, fn (Container $container) => new ReportAccessControl(
	$container->get(AccessPolicyRepository::class)
));
$container->set(CurrentUserProvider::class, fn (Container $container) => $container->get(SessionCurrentUserProvider::class));
$container->set(AccessControl::class, fn (Container $container) => new AccessControl(
	$container->get(AccessPolicyRepository::class)
));
$container->set(Authorization::class, fn (Container $container) => new Authorization(
	$container->get(CurrentUserProvider::class),
	$container->get(AccessControl::class)
));
$container->set(AppointmentAccessControl::class, fn (Container $container) => new AppointmentAccessControl(
	$container->get(AccessPolicyRepository::class)
));

$router = new Router($container);

(require __DIR__ . '/../Routes/web.php')($router);

return new Application($router);
