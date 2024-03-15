<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DishController extends Controller
{
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $dishes = Storage::disk('data')->json('dishes.json');

        if (empty($dishes)) {
            return response()->json([]);
        }

        $result = collect($dishes['dishes']);

        if ($request->getMeal) {
            $result = $result->pluck('availableMeals')->max();
        }
        if ($request->meal) {
            $result = $result->filter(fn (array $item) => in_array($request->meal, $item['availableMeals']))->unique('restaurant');
        }
        if ($request->searchMeal && $request->restaurant) {
            $result = $result->filter(fn (array $item) => in_array($request->searchMeal, $item['availableMeals']))
                ->where('restaurant', $request->restaurant);
        }
        return response()->json($result);
    }
}
