<?php

namespace FA;

/**
 * Location Repository
 *
 * Handles database operations for inventory locations.
 * Follows SRP by separating location data access from business logic.
 *
 * @package FA
 */
class LocationRepository
{
    /**
     * Get all locations ordered by location code
     *
     * @return array Array of location records with 'loccode' and 'locationname' keys
     */
    public function getAllLocations(): array
    {
        $sql = "SELECT loccode, locationname FROM " . TB_PREF . "locations ORDER BY loccode";
        $result = \db_query($sql, "could not get locations");

        $locations = [];
        while ($row = \db_fetch($result)) {
            $locations[] = $row;
        }

        return $locations;
    }

    /**
     * Get locations as associative array for form selects
     *
     * @param bool $includeAllOption Whether to include "All Locations" option
     * @return array Associative array with loccode => locationname
     */
    public function getLocationsForSelect(bool $includeAllOption = false): array
    {
        $locations = [];

        if ($includeAllOption) {
            $locations['All'] = _('All Locations');
        }

        foreach ($this->getAllLocations() as $location) {
            $locations[$location['loccode']] = $location['locationname'];
        }

        return $locations;
    }

    /**
     * Get a specific location by code
     *
     * @param string $locationCode Location code
     * @return array|null Location record or null if not found
     */
    public function getLocationByCode(string $locationCode): ?array
    {
        $sql = "SELECT * FROM " . TB_PREF . "locations WHERE loccode = " . \db_escape($locationCode);
        $result = \db_query($sql, "could not get location");

        return \db_fetch($result) ?: null;
    }

    /**
     * Check if a location exists
     *
     * @param string $locationCode Location code
     * @return bool True if location exists
     */
    public function locationExists(string $locationCode): bool
    {
        return $this->getLocationByCode($locationCode) !== null;
    }

    /**
     * Get location codes only
     *
     * @return array Array of location codes
     */
    public function getLocationCodes(): array
    {
        $locations = $this->getAllLocations();
        return array_column($locations, 'loccode');
    }
}