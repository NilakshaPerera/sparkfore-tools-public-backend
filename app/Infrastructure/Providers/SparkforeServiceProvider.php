<?php

namespace App\Infrastructure\Providers;

use App\Domain\Repositories\AccessControl\AccessControlRepository;
use App\Domain\Repositories\AccessControl\AccessControlRepositoryInterface;
use App\Domain\Repositories\Command\CommandRepository;
use App\Domain\Repositories\Command\CommandRepositoryInterface;
use App\Domain\Repositories\Customer\CustomerRepository;
use App\Domain\Repositories\Customer\CustomerRepositoryInterface;
use App\Domain\Repositories\Hosting\HostingRepository;
use App\Domain\Repositories\Hosting\HostingRepositoryInterfce;
use App\Domain\Repositories\Installation\InstallationRepository;
use App\Domain\Repositories\Installation\InstallationRepositoryInterface;
use App\Domain\Repositories\Log\LogRepository;
use App\Domain\Repositories\Log\LogRepositoryInterface;
use App\Domain\Repositories\Plugin\PluginRepository;
use App\Domain\Repositories\Plugin\PluginRepositoryInterface;
use App\Domain\Repositories\Product\ProductRepository;
use App\Domain\Repositories\Product\ProductRepositoryInterface;
use App\Domain\Repositories\Remote\RemoteAdminRepository;
use App\Domain\Repositories\Remote\RemoteAdminRepositoryInterface;
use App\Domain\Repositories\Software\SoftwareRepository;
use App\Domain\Repositories\Software\SoftwareRepositoryInterface;
use App\Domain\Repositories\User\UserRepository;
use App\Domain\Repositories\User\UserRepositoryInterface;
use App\Domain\Repositories\Webhook\AlertProcessorRepository;
use App\Domain\Repositories\Webhook\AlertProcessorRepositoryInterface;
use App\Domain\Services\AccessControl\AccessControlInterface;
use App\Domain\Services\AccessControl\AccessControlService;
use App\Domain\Services\AppService;
use App\Domain\Services\AppServiceInterface;
use App\Domain\Services\Customer\CustomerService;
use App\Domain\Services\Customer\CustomerServiceInterface;
use App\Domain\Services\Hosting\HostingService;
use App\Domain\Services\Hosting\HostingServiceInterface;
use App\Domain\Services\Installation\InstallationService;
use App\Domain\Services\Installation\InstallationServiceInterface;
use App\Domain\Services\OpenAI\SparkforeOpenAI;
use App\Domain\Services\OpenAI\SparkforeOpenAIInterface;
use App\Domain\Services\Plugin\PluginService;
use App\Domain\Services\Plugin\PluginServiceInterface;
use App\Domain\Services\Product\ProductService;
use App\Domain\Services\Product\ProductServiceInterface;
use App\Domain\Services\Remote\RemoteAdminService;
use App\Domain\Services\Remote\RemoteAdminServiceInterface;
use App\Domain\Services\Remote\RemoteCallHandler;
use App\Domain\Services\Remote\RemoteCallHandlerInterface;
use App\Domain\Services\ServiceApi\GiteaApiService;
use App\Domain\Services\ServiceApi\PrometheusApiService;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use App\Domain\Services\ServiceApi\PrometheusApiServiceInterface;
use App\Domain\Services\Software\SoftwareService;
use App\Domain\Services\Software\SoftwareServiceInterface;
use App\Domain\Services\User\UserService;
use App\Domain\Services\User\UserServiceInterface;
use App\Domain\Services\Webhook\AlertProcessorService;
use App\Domain\Services\Webhook\AlertProcessorServiceInterface;
use Illuminate\Support\ServiceProvider;

class SparkforeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Service Bindings
        $this->app->bind(AppServiceInterface::class, AppService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(AccessControlInterface::class, AccessControlService::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(InstallationServiceInterface::class, InstallationService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(HostingServiceInterface::class, HostingService::class);
        $this->app->bind(SoftwareServiceInterface::class, SoftwareService::class);
        $this->app->bind(PluginServiceInterface::class, PluginService::class);
        $this->app->singleton(RemoteAdminServiceInterface::class, RemoteAdminService::class);
        $this->app->bind(RemoteCallHandlerInterface::class, RemoteCallHandler::class);
        $this->app->bind(AlertProcessorServiceInterface::class, AlertProcessorService::class);
        $this->app->bind(GiteaApiServiceInterface::class, GiteaApiService::class);
        $this->app->bind(PrometheusApiServiceInterface::class, PrometheusApiService::class);

        // Repository Bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AccessControlRepositoryInterface::class, AccessControlRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(InstallationRepositoryInterface::class, InstallationRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(HostingRepositoryInterfce::class, HostingRepository::class);
        $this->app->bind(SoftwareRepositoryInterface::class, SoftwareRepository::class);
        $this->app->bind(PluginRepositoryInterface::class, PluginRepository::class);
        $this->app->bind(CommandRepositoryInterface::class, CommandRepository::class);
        $this->app->bind(RemoteAdminRepositoryInterface::class, RemoteAdminRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
        $this->app->bind(AlertProcessorRepositoryInterface::class, AlertProcessorRepository::class);
        $this->app->bind(SparkforeOpenAIInterface::class, SparkforeOpenAI::class);

        // Facade binding
        $this->app->bind('AppHelper', function () {
            return new AppHelper();
        });

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
