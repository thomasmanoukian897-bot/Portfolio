<?php

namespace App\Enums;

enum MessageType: string
{
    case Text = 'text';
    case GroupNameChanged = 'group_name_changed';
    case GroupAvatarChanged = 'group_avatar_changed';

    public function isSystem(): bool
    {
        return $this !== self::Text;
    }
}
