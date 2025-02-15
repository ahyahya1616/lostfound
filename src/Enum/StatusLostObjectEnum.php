<?php
namespace App\Enum;

enum StatusLostObjectEnum: string
{
    case PERDU = 'perdu';
    case RETROUVE = 'retrouvé';
}
