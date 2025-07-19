<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Package;
use App\Models\PackageActivity;
use App\Models\PackageFaq;
use App\Models\PackageHighlight;
use App\Models\PackageIncludedExcluded;
use App\Models\PackageItinerary;
use App\Models\PackageMeal;
use App\Models\PackageCancellationPolicy; 
use App\Models\PackagePrice; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $minRate = $request->input('min_rate');
        $tags = $request->input('tags', []);
        $search = $request->input('search');

        if (!is_array($tags)) {
            $tags = $tags ? explode(',', $tags) : [];
        }

        $query = Package::with([
            'images',
            'highlights',
            'itineraries' => function ($query) {
                $query->with([
                    'activities',
                    'meals'
                ]);
            },
            'includedExcluded',
            'faqs',
            'cancellationPolicies',
            'prices'
        ]);

        if ($minRate !== null) {
            $query->where('rate', '>=', (float)$minRate);
        }

        if (!empty($tags)) {
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('tags', 'like', "%{$tag}%");
                }
            });
        }

        if ($search) {
            $searchTerm = strtolower($search);
            $query->where(function($q) use ($searchTerm) {
                $q->where(DB::raw('LOWER(name)'), 'like', "%{$searchTerm}%")
                ->orWhere(DB::raw('LOWER(location)'), 'like', "%{$searchTerm}%");
            });
        }

        // Order by: null orders last, then by order value, then by ID
        $query->orderByRaw('`order` IS NULL') // NULL values come last
              ->orderBy('order', 'asc')
              ->orderBy('id', 'desc');

        $packages = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $packages->items(),
            'pagination' => [
                'current_page' => $packages->currentPage(),
                'per_page' => $packages->perPage(),
                'total' => $packages->total(),
                'last_page' => $packages->lastPage(),
            ]
        ]);
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif,webp|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $path = $request->file('image')->store('public/packages');
            $publicPath = Storage::url($path);
            
            return response()->json([
                'path' => $publicPath,
                'full_url' => asset($publicPath)
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:packages,code',
            'name' => 'required|json',
            'duration' => 'required|json',
            'location' => 'required|json',
            'starting_price' => 'nullable|numeric',
            'original_price' => 'nullable|numeric',
            'rate' => 'nullable|numeric|between:0,5',
            'overview' => 'required|json',
            'tags' => 'required|string',
            'order' => 'nullable|integer|min:1',
            'images' => 'required|array',
            'images.*.path' => 'required|string',
            'images.*.order' => 'sometimes|integer',
            'highlights' => 'sometimes|array', 
            'highlights.*.description' => 'required|string',
            'itineraries' => 'sometimes|array', 
            'itineraries.*.day' => 'required|integer',
            'itineraries.*.title' => 'required|string',
            'itineraries.*.activities' => 'required|array',
            'itineraries.*.activities.*.time' => 'required|string',
            'itineraries.*.activities.*.description' => 'required|string',
            'itineraries.*.meals' => 'required|array',
            'itineraries.*.meals.*.description' => 'required|string',
            'included_excluded' => 'sometimes|array',
            'included_excluded.*.type' => 'required|in:included,excluded',
            'included_excluded.*.description' => 'required|string',
            'faqs' => 'sometimes|array', 
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required|string',
            'cancellation_policies' => 'sometimes|array', 
            'cancellation_policies.*.description' => 'required|string',
            'prices' => 'sometimes|array', 
            'prices.*.description' => 'required|string',
            'prices.*.service_type' => 'required|string',
            'prices.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        
        try {
            $package = Package::create([
                'code' => $request->code,
                'name' => $request->name,
                'duration' => $request->duration,
                'location' => $request->location,
                'starting_price' => $request->starting_price,
                'original_price' => $request->original_price,
                'rate' => $request->rate,
                'overview' => $request->overview,
                'tags' => $request->tags,
                'order' => null, // New packages always have null order
            ]);
            
            $this->processImages($package, $request->images);
            
            if ($request->has('highlights') && is_array($request->highlights)) {
                foreach ($request->highlights as $item) { 
                    PackageHighlight::create([
                        'package_id' => $package->id,
                        'description' => $item['description'], 
                    ]);
                }
            }

            if ($request->has('prices') && is_array($request->prices)) {
                foreach ($request->prices as $item) { 
                    PackagePrice::create([
                        'package_id' => $package->id,
                        'service_type' => $item['service_type'], 
                        'description' => $item['description'], 
                        'price' => $item['price'], 
                    ]);
                }
            }
            
            if ($request->has('itineraries') && is_array($request->itineraries)) {
                foreach ($request->itineraries as $itinerary) {
                    $itineraryRecord = PackageItinerary::create([
                        'package_id' => $package->id,
                        'day' => $itinerary['day'],
                        'title' => $itinerary['title'],
                    ]);
                    
                    foreach ($itinerary['activities'] as $activity) {
                        PackageActivity::create([
                            'itinerary_id' => $itineraryRecord->id,
                            'time' => $activity['time'],
                            'description' => $activity['description'],
                        ]);
                    }
                    
                    foreach ($itinerary['meals'] as $meal) {
                        PackageMeal::create([
                            'itinerary_id' => $itineraryRecord->id,
                            'description' => $meal['description'],
                        ]);
                    }
                }
            }
            
            if ($request->has('included_excluded') && is_array($request->included_excluded)) {
                foreach ($request->included_excluded as $item) {
                    PackageIncludedExcluded::create([
                        'package_id' => $package->id,
                        'type' => $item['type'],
                        'description' => $item['description'],
                    ]);
                }
            }
            
            if ($request->has('faqs') && is_array($request->faqs)) {
                foreach ($request->faqs as $faq) {
                    PackageFaq::create([
                        'package_id' => $package->id,
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                    ]);
                }
            }
            
            if ($request->has('cancellation_policies') && is_array($request->cancellation_policies)) {
                foreach ($request->cancellation_policies as $policy) {
                    PackageCancellationPolicy::create([
                        'package_id' => $package->id,
                        'description' => $policy['description'],
                    ]);
                }
            }
            
            DB::commit();
            return response()->json($package->load([
                'images', 
                'highlights', 
                'itineraries.activities',
                'itineraries.meals',
                'includedExcluded',
                'faqs',
                'cancellationPolicies',
                'prices'
            ]), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function show(Package $package)
    {
        return $package->load([
            'images', 
            'highlights', 
            'itineraries' => function ($query) {
                $query->with([
                    'activities',
                    'meals'
                ]);
            },
            'includedExcluded',
            'faqs',
            'cancellationPolicies',
            'prices'
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|string|unique:packages,code,'.$package->id,
            'name' => 'sometimes|json',
            'duration' => 'sometimes|json',
            'location' => 'sometimes|json',
            'starting_price' => 'nullable|numeric',
            'original_price' => 'nullable|numeric',
            'rate' => 'nullable|numeric|between:0,5',
            'overview' => 'sometimes|json',
            'tags' => 'sometimes|string',
            'order' => 'nullable|integer|min:1',
            'images' => 'sometimes|array',
            'images.*.path' => 'required|string',
            'images.*.order' => 'sometimes|integer',
            'highlights' => 'sometimes|array', 
            'highlights.*.description' => 'required|string',
            'itineraries' => 'sometimes|array',
            'itineraries.*.day' => 'required|integer',
            'itineraries.*.title' => 'required|string',
            'itineraries.*.activities' => 'required|array',
            'itineraries.*.activities.*.time' => 'required|string',
            'itineraries.*.activities.*.description' => 'required|string',
            'itineraries.*.meals' => 'required|array',
            'itineraries.*.meals.*.description' => 'required|string',
            'included_excluded' => 'sometimes|array', 
            'included_excluded.*.type' => 'required|in:included,excluded',
            'included_excluded.*.description' => 'required|string',
            'faqs' => 'sometimes|array', 
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required|string',
            'cancellation_policies' => 'sometimes|array', 
            'cancellation_policies.*.description' => 'required|string',
            'prices' => 'sometimes|array', 
            'prices.*.description' => 'required|string',
            'prices.*.service_type' => 'required|string',
            'prices.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        
        try {
            $package->update($request->only([
                'code', 'name', 'duration', 'location', 'starting_price', 
                'original_price', 'rate', 'overview', 'tags'
            ]));
            
            if ($request->has('images')) {
                $this->processImages($package, $request->images);
            }
            
            if ($request->has('highlights')) {
                $package->highlights()->delete();
                foreach ($request->highlights as $highlight) {
                    PackageHighlight::create([
                        'package_id' => $package->id,
                        'description' => $highlight['description'], 
                    ]);
                }
            }

            if ($request->has('prices')) {
                $package->prices()->delete();
                foreach ($request->prices as $price) { 
                    PackagePrice::create([
                        'package_id' => $package->id,
                        'service_type' => $price['service_type'], 
                        'description' => $price['description'], 
                        'price' => $price['price'], 
                    ]);
                }
            }
            
            if ($request->has('itineraries')) {
                $package->itineraries()->delete();
                foreach ($request->itineraries as $itinerary) {
                    $itineraryRecord = PackageItinerary::create([
                        'package_id' => $package->id,
                        'day' => $itinerary['day'],
                        'title' => $itinerary['title'],
                    ]);
                    
                    foreach ($itinerary['activities'] as $activity) {
                        PackageActivity::create([
                            'itinerary_id' => $itineraryRecord->id,
                            'time' => $activity['time'],
                            'description' => $activity['description'],
                        ]);
                    }
                    
                    foreach ($itinerary['meals'] as $meal) {
                        PackageMeal::create([
                            'itinerary_id' => $itineraryRecord->id,
                            'description' => $meal['description'],
                        ]);
                    }
                }
            }
            
            if ($request->has('included_excluded')) {
                $package->includedExcluded()->delete();
                foreach ($request->included_excluded as $item) {
                    PackageIncludedExcluded::create([
                        'package_id' => $package->id,
                        'type' => $item['type'],
                        'description' => $item['description'],
                    ]);
                }
            }
            
            if ($request->has('faqs')) {
                $package->faqs()->delete();
                foreach ($request->faqs as $faq) {
                    PackageFaq::create([
                        'package_id' => $package->id,
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                    ]);
                }
            }
            
            if ($request->has('cancellation_policies')) {
                $package->cancellationPolicies()->delete();
                foreach ($request->cancellation_policies as $policy) {
                    PackageCancellationPolicy::create([
                        'package_id' => $package->id,
                        'description' => $policy['description'],
                    ]);
                }
            }
            
            DB::commit();
            return response()->json($package->load([
                'images', 
                'highlights', 
                'itineraries.activities',
                'itineraries.meals',
                'includedExcluded',
                'faqs',
                'cancellationPolicies',
                'prices'
            ]), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Package $package)
    {
        DB::transaction(function () use ($package) {
            foreach ($package->images as $image) {
                $path = str_replace('/storage', 'public', $image->path);
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
            
            $package->highlights()->delete();
            $package->itineraries()->each(function ($itinerary) {
                $itinerary->activities()->delete();
                $itinerary->meals()->delete();
                $itinerary->delete();
            });
            $package->includedExcluded()->delete();
            $package->faqs()->delete();
            $package->cancellationPolicies()->delete();
            $package->prices()->delete();
            $package->images()->delete();
            $package->delete();
        });
        
        return response()->json(null, 204);
    }

    private function processImages($model, $images)
    {
        $existingImages = $model->images;

        $newImagePaths = collect($images)->pluck('path')->toArray();

        foreach ($existingImages as $existingImage) {
            if (!in_array($existingImage->path, $newImagePaths)) {
                $path = str_replace('/storage', 'public', $existingImage->path);
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
                $existingImage->delete();
            }
        }

        foreach ($images as $index => $imageData) {
            $image = $model->images()->firstOrNew(['path' => $imageData['path']]);
            $image->order = $imageData['order'] ?? $index;
            $image->save();
        }
    }

    public function manageImages(Request $request, Package $package)
    {
        $request->validate([
            'action' => 'required|in:add,remove,reorder',
            'images' => 'required_if:action,add,reorder|array',
            'images.*.path' => 'required_if:action,add|string',
            'images.*.order' => 'required_if:action,reorder|integer',
            'image_id' => 'required_if:action,remove|exists:images,id',
        ]);

        DB::beginTransaction();
        try {
            switch ($request->action) {
                case 'add':
                    foreach ($request->images as $image) {
                        $package->images()->create([
                            'path' => $image['path'],
                            'order' => $package->images()->max('order') + 1,
                        ]);
                    }
                    break;
                    
                case 'remove':
                    $image = $package->images()->findOrFail($request->image_id);
                    $path = str_replace('/storage', 'public', $image->path);
                    if (Storage::exists($path)) {
                        Storage::delete($path);
                    }
                    $image->delete();
                    break;
                    
                case 'reorder':
                    foreach ($request->images as $image) {
                        $package->images()
                            ->where('id', $image['id'])
                            ->update(['order' => $image['order']]);
                    }
                    break;
            }
            
            DB::commit();
            return response()->json($package->images()->orderBy('order')->get());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function swapOrder(Request $request)
    {
        $request->validate([
            'first_package_id'  => 'required|exists:packages,id',
            'second_package_id' => 'required|exists:packages,id',
        ]);

        $first  = Package::findOrFail($request->first_package_id);
        $second = Package::findOrFail($request->second_package_id);

        DB::beginTransaction();
        try {
            $maxOrder = Package::max('order') ?? 0;
            $nextOrder = $maxOrder;

            if (is_null($first->order)) {
                $nextOrder++;
                $first->order = $nextOrder;
            }
            if (is_null($second->order)) {
                $nextOrder++;
                $second->order = $nextOrder;
            }

            $temp        = $first->order;
            $first->order  = $second->order;
            $second->order = $temp;

            $first->save();
            $second->save();

            DB::commit();

            return response()->json([
                'message' => 'Order swapped successfully',
                'first'   => $first,
                'second'  => $second,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Swap failed: ' . $e->getMessage()
            ], 500);
        }
    }
}