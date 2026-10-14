@extends('admin.layouts.app')
@section('title', $article->exists ? 'Edit article' : 'New article')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
    @csrf
    @if($article->exists) @method('PUT') @endif
    <div class="card">
        <div class="card-body">
            <x-form.bilingual name="title" label="Title" :model="$article" required />
            <div class="row">
                <div class="col-md-3"><x-form.select name="category" label="Category" :options="\App\Enums\Options::ARTICLE_CATEGORIES" :value="$article->category" required /></div>
                <div class="col-md-3"><x-form.select name="audience" label="Audience" :options="\App\Enums\Options::AUDIENCES" :value="$article->audience" required /></div>
                <div class="col-md-2"><x-form.input name="icon" label="Emoji" :value="$article->icon" /></div>
                <div class="col-md-2"><x-form.input name="sort_order" type="number" label="Sort order" :value="$article->sort_order ?? 0" /></div>
                <div class="col-md-2"><x-form.input name="slug" label="Slug" :value="$article->slug" /></div>
            </div>
            <div class="row">
                <div class="col-md-6"><x-form.input name="image" type="file" label="Cover image" accept="image/*" /></div>
                <div class="col-md-6">@if($article->image_path)<img src="{{ $article->imageUrl() }}" class="img-thumbnail" style="max-height: 100px" alt="">@endif</div>
            </div>
            <x-form.bilingual name="summary" label="Summary" :model="$article" textarea :rows="2" />
            <x-form.bilingual name="body" label="Body" :model="$article" textarea :rows="14" required
                              help="Plain text. One step per line. Lines starting with a number or • are shown as a list." />
            <x-form.check name="is_published" label="Published" :checked="$article->is_published" />
        </div>
        <div class="card-footer bg-white text-end">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-brand"><i class="bi bi-save"></i> Save</button>
        </div>
    </div>
</form>
@endsection
