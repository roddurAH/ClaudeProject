@extends('admin.layouts.app')
@section('title', 'Articles & tips')

@section('content')
<div class="text-end mb-3">
    <a href="{{ route('admin.articles.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New article</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Title</th><th>Category</th><th>Audience</th><th>Status</th><th>Updated</th><th></th></tr></thead>
            <tbody>
            @forelse($articles as $a)
                <tr>
                    <td>{{ $a->icon }} <strong lang="bn">{{ $a->title_bn }}</strong><br><small class="text-muted">{{ $a->title_en }}</small></td>
                    <td>{{ \App\Enums\Options::ARTICLE_CATEGORIES[$a->category] ?? $a->category }}</td>
                    <td>{{ \App\Enums\Options::AUDIENCES[$a->audience] ?? $a->audience }}</td>
                    <td>{!! $a->is_published ? '<span class="badge text-bg-success">Published</span>' : '<span class="badge text-bg-secondary">Draft</span>' !!}</td>
                    <td class="small text-muted">{{ $a->updated_at->diffForHumans() }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.articles.edit', $a) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <x-delete-button :action="route('admin.articles.destroy', $a)" label="" />
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No articles yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $articles->links() }}</div>
@endsection
