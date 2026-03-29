<?php

namespace App\Helpers;

class ServiceHelper
{
    /**
     * Get the badge classes for a given service type.
     *
     * @param string $serviceType
     * @return string
     */
    public static function getServiceBadgeClass($serviceType)
    {
        $type = strtolower($serviceType ?? '');
        $map = self::getServiceColorMap();

        if (isset($map[$type])) {
            return $map[$type];
        }

        // Fallback for partial matches (like "wedding" inside "Special Wedding")
        foreach ($map as $key => $class) {
            if (str_contains($type, $key)) {
                return $class;
            }
        }

        return 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
    }

    /**
     * Get the badge classes for donation types.
     *
     * @param string $type
     * @return string
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
     * Get the full service color map.
     *
     * @return array
     */
    public static function getServiceColorMap()
    {
        // Default static map as fallback and for fixed types
        $map = [
            'wedding'      => 'bg-pink-100 text-pink-700 border-pink-200 dark:bg-pink-900/30 dark:text-pink-300 dark:border-pink-800/40',
            'baptism'      => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/40',
            'mass'         => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800/40',
            'funeral'      => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
            'burial'       => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
            'confirmation' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/40',
            'communion'    => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800/40',
            'anointing'    => 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800/40',
            'ordination'   => 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800/40',
        ];

        // Fetch dynamic color map from database
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('service_types')) {
                $serviceTypes = \App\Models\ServiceType::all(['name', 'color']);
                foreach ($serviceTypes as $service) {
                    $color = strtolower($service->color ?? 'blue');
                    // In Tailwind, black/white/transparent don't have -100/-700 shades out of the box. Ensure standard palette.
                    if (in_array($color, ['black', 'white', 'transparent'])) {
                        $color = 'gray';
                    }
                    $map[strtolower($service->name)] = "bg-{$color}-100 text-{$color}-700 border-{$color}-200 dark:bg-{$color}-900/30 dark:text-{$color}-300 dark:border-{$color}-800/40";
                }
            }
        } catch (\Exception $e) {
            // Ignore DB errors if not migrated yet
        }

        return $map;
    }
}
