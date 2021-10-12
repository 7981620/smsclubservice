<?php

namespace Agenta\SmsClubService;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Agenta\SmsClubService\Skeleton\SkeletonClass
 */
class SmsClubServiceFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'smsclubservice';
    }
}
