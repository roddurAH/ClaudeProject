<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $articles = Article::published()
            ->when($request->filled('audience'), fn ($q) => $q->whereIn('audience', ['all', $request->string('audience')]))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderBy('sort_order')
            ->paginate(20);

        return ArticleResource::collection($articles);
    }

    public function show(string $slug): ArticleResource
    {
        return new ArticleResource(Article::published()->where('slug', $slug)->firstOrFail());
    }
}
