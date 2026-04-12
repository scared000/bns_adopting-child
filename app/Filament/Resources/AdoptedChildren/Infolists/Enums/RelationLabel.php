<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Enums;

enum RelationLabel: string
{
    case BiologicalMother = 'biological_mother';
    case BiologicalFather = 'biological_father';
    case AdoptiveMother   = 'adoptive_mother';
    case AdoptiveFather   = 'adoptive_father';
    case Grandmother      = 'grandmother';
    case Grandfather      = 'grandfather';
    case Aunt             = 'aunt';
    case Uncle            = 'uncle';
    case OlderSibling     = 'older_sibling';
    case LegalGuardian    = 'legal_guardian';
    case FosterParent     = 'foster_parent';
    case CourtAppointed   = 'court_appointed';
    case FamilyFriend     = 'family_friend';

    public function label(): string
    {
        return match ($this) {
            self::BiologicalMother => 'Biological Mother',
            self::BiologicalFather => 'Biological Father',
            self::AdoptiveMother   => 'Adoptive Mother',
            self::AdoptiveFather   => 'Adoptive Father',
            self::Grandmother      => 'Grandmother',
            self::Grandfather      => 'Grandfather',
            self::Aunt             => 'Aunt',
            self::Uncle            => 'Uncle',
            self::OlderSibling     => 'Older Sibling',
            self::LegalGuardian    => 'Legal Guardian',
            self::FosterParent     => 'Foster Parent',
            self::CourtAppointed   => 'Court-Appointed Guardian',
            self::FamilyFriend     => 'Family Friend',
        };
    }

    public static function resolve(string $value): string
    {
        return self::tryFrom($value)?->label()
            ?? ucwords(str_replace('_', ' ', $value));
    }
}
