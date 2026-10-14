<?php

namespace App\Enums;

/** What kind of app user someone is – used to tailor content. */
enum UserType: string
{
    case Farmer = 'farmer';
    case Gardener = 'gardener';               // rooftop / homestead gardener
    case Learner = 'learner';                 // student, enthusiast
    case ExtensionWorker = 'extension_worker'; // SAAO, NGO field staff

    public function label(): string
    {
        return match ($this) {
            self::Farmer => 'Farmer',
            self::Gardener => 'Home / rooftop gardener',
            self::Learner => 'Student / enthusiast',
            self::ExtensionWorker => 'Extension worker',
        };
    }
}
