@extends('admin.layouts.app')
@section('title', 'Fertilizer prices')

@section('content')
<div class="alert alert-info small">
    Government-fixed farmer-level prices. The app shows them next to the fertilizer calculator so farmers can spot overcharging.
    Update when the Ministry of Agriculture announces a change.
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Fertilizer</th><th class="text-end">Official price ৳/kg</th><th>Notes</th><th>Updated</th><th></th></tr></thead>
            <tbody>
            @foreach($fertilizers as $f)
                <tr>
                    <td><strong lang="bn">{{ $f->name_bn }}</strong> <span class="text-muted">{{ $f->name_en }}</span></td>
                    <td class="text-end">{{ $f->official_price_per_kg !== null ? number_format($f->official_price_per_kg, 2) : '—' }}</td>
                    <td class="small" lang="bn">{{ \Illuminate\Support\Str::limit($f->notes_bn, 70) }}</td>
                    <td class="small text-muted">{{ $f->updated_at->format('d M Y') }}</td>
                    <td class="text-end"><a href="{{ route('admin.fertilizers.edit', $f) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
