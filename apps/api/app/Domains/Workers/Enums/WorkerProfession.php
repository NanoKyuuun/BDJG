<?php

namespace App\Domains\Workers\Enums;

enum WorkerProfession: string
{
    case Photographer = 'PHOTOGRAPHER';
    case Videographer = 'VIDEOGRAPHER';
    case Editor = 'EDITOR';
    case Colorist = 'COLORIST';
    case MotionDesigner = 'MOTION_DESIGNER';
    case SoundEngineer = 'SOUND_ENGINEER';
    case Gaffer = 'GAFFER';
    case Assistant = 'ASSISTANT';
}
