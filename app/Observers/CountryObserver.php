<?php

namespace App\Observers;

use App\Models\Country;
use Illuminate\Support\Facades\Log;
use Rinvex\Country\CountryLoader;
class CountryObserver
{
    /**
     * Handle the Country "created" event.
     */
    public function created(Country $country)
    {
        // Get the country information using the Rinvex package

        $countryData  = collect(countries())->firstWhere('name', $country->name);

        if (!$countryData) {
            abort(404, "Country not found.");
        }

        if ($countryData) {
            $country->update([
                'iso2' => $countryData['iso_3166_1_alpha2'],
                'iso3' => $countryData['iso_3166_1_alpha3'],
            ]);
        }
    }

    /**
     * Handle the Country "updated" event.
     */
    public function updated(Country $country): void
    {
        //
    }

    /**
     * Handle the Country "deleted" event.
     */
    public function deleted(Country $country): void
    {
        //
    }

    /**
     * Handle the Country "restored" event.
     */
    public function restored(Country $country): void
    {
        //
    }

    /**
     * Handle the Country "force deleted" event.
     */
    public function forceDeleted(Country $country): void
    {
        //
    }
}
