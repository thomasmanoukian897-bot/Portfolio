<?php

namespace App\Enums;

enum GroupAddPermission: string
{
    case Everyone = 'everyone';
    case FollowingOnly = 'following_only';
}
