<?php

namespace App\Enums;

enum QuestionStatus: string
{
    case Pending = 'pending';
    case Answered = 'answered';
    case Closed = 'closed';
}
