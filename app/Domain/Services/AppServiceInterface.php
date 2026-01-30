<?php

namespace App\Domain\Services;

use Illuminate\Contracts\Foundation\Application;

interface AppServiceInterface
{
    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function user();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function accessControl();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function customer();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function installation();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function product();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function hosting();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function software();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function plugin();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function remoteAdmin();

    /**
     * @return Application|\Illuminate\Foundation\Application|mixed
     */
    public static function alertProcessor();
}
