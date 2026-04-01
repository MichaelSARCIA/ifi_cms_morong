<?php

namespace App\Helpers;

class ServiceHelper
{


    /**
     * Get the full set of Tailwind classes for a service badge.
     * Matches the appearance of Status and Payment badges exactly.
     *
     * @param string $serviceType
     * @return string
     */
    public static function getServiceBadgeClass($serviceType)
    {
        $type = strtolower($serviceType ?? '');
        $colorName = self::getServiceColorName($type);

        return "bg-{$colorName}-100 text-{$colorName}-700 border-{$colorName}-200 dark:bg-{$colorName}-900/40 dark:text-{$colorName}-300 dark:border-{$colorName}-800/50";
    }

    /**
     * Internal helper to map a service type to a base color name.
     */
    private static function getServiceColorName($type)
    {
        // 1. Check DB for specific name or hex mapping
        static $dbMap = null;
        if ($dbMap === null) {
            $dbMap = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('service_types')) {
                    $serviceTypes = \App\Models\ServiceType::all(['name', 'color']);
                    foreach ($serviceTypes as $service) {
                        $dbMap[strtolower($service->name)] = self::mapHexToColorName($service->color);
                    }
                }
            } catch (\Exception $e) {}
        }

        if (isset($dbMap[$type])) return $dbMap[$type];

        // 2. Static Default Map
        $staticMap = [
            'wedding'      => 'pink',
            'baptism'      => 'blue',
            'mass'         => 'purple',
            'funeral'      => 'rose',
            'burial'       => 'slate',
            'confirmation' => 'emerald',
            'communion'    => 'amber',
            'anointing'    => 'orange',
            'ordination'   => 'indigo',
        ];

        if (isset($staticMap[$type])) return $staticMap[$type];

        // 3. Fallback partial match
        foreach ($staticMap as $key => $color) {
            if (str_contains($type, $key)) return $color;
        }

        return 'gray';
    }

    /**
     * Maps common hex codes back to tailwind color names
     */
    private static function mapHexToColorName($hex)
    {
        $hex = strtolower($hex ?? '');
        $map = [
            '#3b82f6' => 'blue',
            '#a855f7' => 'purple',
            '#ec4899' => 'pink',
            '#f43f5e' => 'rose',
            '#6366f1' => 'indigo',
            '#10b981' => 'emerald',
            '#f59e0b' => 'amber',
            '#f97316' => 'orange',
            '#64748b' => 'slate',
            '#ef4444' => 'red',
        ];

        return $map[$hex] ?? 'blue';
    }

    /**
     * Get the badge classes for donation types.
     */
    public static function getDonationBadgeClass($type)
    {
        $type = strtolower($type ?? '');
        return match (true) {
            str_contains($type, 'donation') => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/40',
            str_contains($type, 'tithe') => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/40',
            str_contains($type, 'love offering') => 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800/40',
            default => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'
        };
    }

    /**
     * Get the full service color map (Tailwind classes).
     * Used by the details modal and other UI components.
     *
     * @return array
     */
    public static function getServiceColorMap()
    {
        // 1. Static Defaults
        $map = [
            'wedding'      => 'bg-pink-100 text-pink-700 border-pink-200 dark:bg-pink-900/40 dark:text-pink-300 dark:border-pink-800/50',
            'baptism'      => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-800/50',
            'mass'         => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-800/50',
            'funeral'      => 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-800/50',
            'burial'       => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-900/40 dark:text-slate-300 dark:border-slate-800/50',
            'confirmation' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-800/50',
            'communion'    => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-800/50',
            'anointing'    => 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-800/50',
            'ordination'   => 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:border-indigo-800/50',
        ];

        // 2. Add dynamic ones from DB
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('service_types')) {
                $serviceTypes = \App\Models\ServiceType::all(['name', 'color']);
                foreach ($serviceTypes as $service) {
                    $colorName = self::mapHexToColorName($service->color);
                    $map[strtolower($service->name)] = "bg-{$colorName}-100 text-{$colorName}-700 border-{$colorName}-200 dark:bg-{$colorName}-900/40 dark:text-{$colorName}-300 dark:border-{$colorName}-800/50";
                }
            }
        } catch (\Exception $e) {}

        return $map;
    }
}
