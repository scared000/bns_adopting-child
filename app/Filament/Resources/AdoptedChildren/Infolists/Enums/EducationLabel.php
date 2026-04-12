<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Enums;

enum EducationLabel: string
{
    case NoFormalEducation   = 'no_formal_education';
    case ElementaryUndergrad = 'elementary_undergraduate';
    case ElementaryGraduate  = 'elementary_graduate';
    case JhsUndergrad        = 'jhs_undergraduate';
    case JhsGraduate         = 'jhs_graduate';
    case ShsUndergrad        = 'shs_undergraduate';
    case ShsGraduate         = 'shs_graduate';
    case Vocational          = 'vocational';
    case CollegeUndergrad    = 'college_undergraduate';
    case CollegeGraduate     = 'college_graduate';
    case Masters             = 'masters';
    case Doctorate           = 'doctorate';

    public function label(): string
    {
        return match ($this) {
            self::NoFormalEducation   => 'No Formal Education',
            self::ElementaryUndergrad => 'Elementary Undergraduate',
            self::ElementaryGraduate  => 'Elementary Graduate',
            self::JhsUndergrad        => 'Junior High School Undergraduate',
            self::JhsGraduate         => 'Junior High School Graduate (Grade 10)',
            self::ShsUndergrad        => 'Senior High School Undergraduate',
            self::ShsGraduate         => 'Senior High School Graduate (Grade 12)',
            self::Vocational          => 'Vocational / Technical Course',
            self::CollegeUndergrad    => 'College Undergraduate',
            self::CollegeGraduate     => 'College Graduate',
            self::Masters             => "Master's Degree",
            self::Doctorate           => 'Doctorate Degree',
        };
    }

    public static function resolve(string $value): string
    {
        return self::tryFrom($value)?->label()
            ?? ucwords(str_replace('_', ' ', $value));
    }
}
