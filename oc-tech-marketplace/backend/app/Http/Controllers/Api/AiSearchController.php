<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\OpenAiClient;
use Illuminate\Http\Request;

class AiSearchController extends Controller
{
    public function __construct(private readonly OpenAiClient $ai) {}

    public function search(Request $request)
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:300'],
        ]);
        $query = $data['query'];

        $interpretation = $this->interpret($query);

        $keywords = $interpretation['keywords'] ?? $query;
        $categoryName = $interpretation['category'] ?? null;
        $maxPrice = $interpretation['max_price'] ?? null;

        $results = $this->searchProducts($keywords, $categoryName, $maxPrice);

        if ($results->isEmpty() && $interpretation) {
            $results = $this->searchProducts($query, null, null);
        }

        return response()->json([
            'ai_powered' => $interpretation !== null,
            'summary' => $interpretation['summary'] ?? "Showing keyword results for \"{$query}\".",
            'results' => $results,
        ]);
    }

    private function interpret(string $query): ?array
    {
        $categories = Category::pluck('name')->implode(', ');

        $raw = $this->ai->chat([
            [
                'role' => 'system',
                'content' => 'You translate a shopper\'s natural-language request into search filters for '
                    .'a digital marketplace selling websites, Laravel projects, UI kits, and similar digital '
                    .'products. Available categories: '.$categories.'. Respond with ONLY compact JSON of the '
                    .'shape {"keywords": string, "category": string|null, "max_price": number|null, '
                    .'"summary": string}. "category" must be one of the available categories or null. '
                    .'"summary" is a short, friendly one-sentence restatement of what you understood, to show the shopper.',
            ],
            ['role' => 'user', 'content' => $query],
        ], ['response_format' => ['type' => 'json_object']]);

        if (! $raw) {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function searchProducts(?string $keywords, ?string $categoryName, ?float $maxPrice)
    {
        return Product::query()
            ->with(['vendor', 'category'])
            ->where('status', 'published')
            ->when($keywords, fn ($q, $term) => $q->where(fn ($qq) => $qq
                ->where('title', 'like', "%{$term}%")
                ->orWhere('summary', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")))
            ->when($categoryName, fn ($q, $name) => $q->whereHas(
                'category',
                fn ($qq) => $qq->where('name', 'like', "%{$name}%")
            ))
            ->when($maxPrice, fn ($q, $price) => $q->where('base_price', '<=', $price))
            ->latest('published_at')
            ->limit(24)
            ->get();
    }
}
