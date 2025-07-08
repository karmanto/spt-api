<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Package;
use App\Models\PackageActivity;
use App\Models\PackageFaq;
use App\Models\PackageHighlight;
use App\Models\PackagePrice;
use App\Models\PackageIncludedExcluded;
use App\Models\PackageItinerary;
use App\Models\PackageMeal;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run()
    {
        $packages = json_decode(file_get_contents(database_path('seeders/data/packages.json')), true);

        foreach ($packages as $packageData) {
            // Create main package
            $package = Package::create([
                'code' => $packageData['code'],
                'name' => $packageData['name'],
                'duration' => $packageData['duration'],
                'location' => $packageData['location'],
                'starting_price' => isset($packageData['startingPrice']) ? $packageData['startingPrice'] : null,
                'original_price' => isset($packageData['originalPrice']) ? $packageData['originalPrice'] : null,
                'rate' => $packageData['rate'],
                'overview' => $packageData['overview'],
                'tags' => $packageData['tags'],
                'order' => $packageData['order'],
            ]);

            // Create images
            foreach ($packageData['images'] as $index => $path) {
                $package->images()->create([
                    'path' => $path,
                    'order' => $index,
                ]);
            }

            // Create highlights
            foreach ($packageData['highlights'] as $highlight) {
                PackageHighlight::create([
                    'package_id' => $package->id,
                    'description' => $highlight,
                ]);
            }

            // Create prices
            foreach ($packageData['prices'] as $price) {
                PackagePrice::create([
                    'package_id' => $package->id,
                    'description' => $price['description'],
                    'price' => $price['price'],
                    'service_type' => $price['service_type']
                ]);
            }

            // Create itineraries
            foreach ($packageData['itinerary'] as $itinerary) {
                $itineraryRecord = PackageItinerary::create([
                    'package_id' => $package->id,
                    'day' => $itinerary['day'],
                    'title' => $itinerary['title'],
                ]);

                // Create activities
                foreach ($itinerary['activities'] as $activity) {
                    PackageActivity::create([
                        'itinerary_id' => $itineraryRecord->id,
                        'time' => explode(' - ', $activity['en'])[0],
                        'description' => $activity,
                    ]);
                }

                // Create meals
                foreach ($itinerary['meals'] as $meal) {
                    PackageMeal::create([
                        'itinerary_id' => $itineraryRecord->id,
                        'description' => $meal,
                    ]);
                }
            }

            // Create included
            foreach ($packageData['included'] as $included) {
                PackageIncludedExcluded::create([
                    'package_id' => $package->id,
                    'type' => 'included',
                    'description' => $included,
                ]);
            }

            // Create excluded
            foreach ($packageData['excluded'] as $excluded) {
                PackageIncludedExcluded::create([
                    'package_id' => $package->id,
                    'type' => 'excluded',
                    'description' => $excluded,
                ]);
            }

            // Create FAQs
            foreach ($packageData['faqs'] as $faq) {
                PackageFaq::create([
                    'package_id' => $package->id,
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                ]);
            }
        }
    }
}