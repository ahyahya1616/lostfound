<?php 
namespace App\Enum;

enum MatchStatusEnum: string
{
    case EN_ATTENTE = 'en attente';
    case CONFIRME = 'confirmé';
    case REJETE = 'rejeté';
}
