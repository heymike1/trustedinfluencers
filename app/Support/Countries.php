<?php

namespace App\Support;

class Countries
{
    /** ISO 3166-1 alpha-2 → display name for the countries that show up in creator audiences. */
    public const NAMES = [
        'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada', 'AU' => 'Australia', 'DE' => 'Germany',
        'FR' => 'France', 'NL' => 'Netherlands', 'IN' => 'India', 'BR' => 'Brazil', 'JP' => 'Japan', 'IE' => 'Ireland',
        'AT' => 'Austria', 'CH' => 'Switzerland', 'ES' => 'Spain', 'IT' => 'Italy', 'SE' => 'Sweden', 'NO' => 'Norway',
        'DK' => 'Denmark', 'FI' => 'Finland', 'PL' => 'Poland', 'PT' => 'Portugal', 'BE' => 'Belgium', 'MX' => 'Mexico',
        'AR' => 'Argentina', 'KR' => 'South Korea', 'SG' => 'Singapore', 'NZ' => 'New Zealand', 'ZA' => 'South Africa',
        'AE' => 'United Arab Emirates', 'PH' => 'Philippines', 'ID' => 'Indonesia', 'TR' => 'Türkiye', 'NG' => 'Nigeria',
    ];
}
