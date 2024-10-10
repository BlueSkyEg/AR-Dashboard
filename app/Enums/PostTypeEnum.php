<?php

namespace App\Enums;

enum PostTypeEnum: string
{
    case BLOG = 'blog';
    case PROJECT = 'project';
    case CAREER = 'career';
}
