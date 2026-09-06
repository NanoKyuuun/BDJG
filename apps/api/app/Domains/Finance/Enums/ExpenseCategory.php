<?php

namespace App\Domains\Finance\Enums;

enum ExpenseCategory: string
{
    case Travel = 'TRAVEL';
    case Meals = 'MEALS';
    case EquipmentRental = 'EQUIPMENT_RENTAL';
    case Accommodation = 'ACCOMMODATION';
    case Other = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::Travel => 'Travel & Transportation',
            self::Meals => 'Meals & Refreshments',
            self::EquipmentRental => 'Equipment Rental',
            self::Accommodation => 'Accommodation',
            self::Other => 'Other Operational Expense',
        };
    }
}
