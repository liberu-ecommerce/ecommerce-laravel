<?php

namespace App\Interfaces;

use App\Services\Shipping\CarrierRate;

interface CarrierRateInterface
{
    /**
     * Live shipping rates for a parcel between two addresses.
     *
     * @param  array  $parcel  ['weight' => float, 'length'?, 'width'?, 'height'?]
     * @param  array  $from  origin address (name/street1/city/state/zip/country)
     * @param  array  $to  destination address (same shape)
     * @return CarrierRate[] empty when the carrier is unconfigured or unreachable
     */
    public function getRates(array $parcel, array $from, array $to): array;
}
