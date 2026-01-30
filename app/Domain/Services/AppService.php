<?php

namespace App\Domain\Services;

use App\Domain\Services\AccessControl\AccessControlInterface;
use App\Domain\Services\Customer\CustomerServiceInterface;
use App\Domain\Services\Hosting\HostingServiceInterface;
use App\Domain\Services\Installation\InstallationServiceInterface;
use App\Domain\Services\Plugin\PluginServiceInterface;
use App\Domain\Services\Product\ProductServiceInterface;
use App\Domain\Services\Remote\RemoteAdminServiceInterface;
use App\Domain\Services\Software\SoftwareServiceInterface;
use App\Domain\Services\User\UserServiceInterface;
use App\Domain\Services\Webhook\AlertProcessorServiceInterface;
use Illuminate\Contracts\Foundation\Application;

class AppService implements AppServiceInterface
{
    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function user()
    {
        return app(UserServiceInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function accessControl()
    {
        return app(AccessControlInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function customer()
    {
        return app(CustomerServiceInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function installation()
    {
        return app(InstallationServiceInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function product()
    {
        return app(ProductServiceInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function hosting()
    {
        return app(HostingServiceInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function software()
    {
        return app(SoftwareServiceInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function plugin()
    {
        return app(PluginServiceInterface::class);
    }


    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function remoteAdmin()
    {
        return app(RemoteAdminServiceInterface::class);
    }

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function alertProcessor()
    {
        return app(AlertProcessorServiceInterface::class);
    }
}
