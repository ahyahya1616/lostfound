<?php 
namespace App\Enum;

enum ReportStatusEnum: string
{
    case EN_COURS = 'en cours';
    case VALIDE = 'validé';
    case REJETE = 'rejeté';
}
