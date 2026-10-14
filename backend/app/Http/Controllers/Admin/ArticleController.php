<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(): View
    {
        return view('admin.articles.index', [
            'articles' => Article::orderBy('sort_order')->orderByDesc('id')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.form', ['article' => new Article(['audience' => 'all', 'is_published' => true])]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $article = new Article($request->safe()->except('image'));
        if ($request->hasFile('image')) {
            $article->image_path = $this->images->store($request->file('image'), 'articles');
        }
        $article->save();

        return redirect()->route('admin.articles.index')->with('status', 'Article created.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $article->fill($request->safe()->except('image'));
        if ($request->hasFile('image')) {
            $this->images->delete($article->image_path);
            $article->image_path = $this->images->store($request->file('image'), 'articles');
        }
        $article->save();

        return redirect()->route('admin.articles.index')->with('status', 'Article saved.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->images->delete($article->image_path);
        $article->delete();

        return back()->with('status', 'Article deleted.');
    }
}
