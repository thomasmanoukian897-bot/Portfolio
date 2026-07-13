<?php

namespace App\Enums;

enum MessagePermission: string
{
    case Everyone = 'everyone';
    case FollowersOnly = 'followers_only';
    case NoOne = 'no_one';
}
