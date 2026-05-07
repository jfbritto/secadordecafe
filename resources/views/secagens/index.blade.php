@extends('layouts.app')

@section('title', 'Secagens')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
    <h1 class="page" style="margin:0;">Secagens</h1>
    @can('create', App\Models\Secagem::class)
        <a href="{{ route('secagens.create') }}" class="btn btn-primary" style="width:auto; padding:8px 14px;">+ Nova secagem</a>
    @endcan
</div>

@if(session('flash'))<div class="card" style="margin-bottom:12px; background:#dcfce7;">{{ session('flash') }}</div>@endif

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">#</th>
                <th style="text-align:left; padding:10px 14px;">Data</th>
                <th style="text-align:left; padding:10px 14px;">Secador</th>
                <th style="text-align:right; padding:10px 14px;">Itens</th>
                <th style="text-align:left; padding:10px 14px;">Status</th>
                <th style="padding:10px 14px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($secagens as $s)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;"><a href="{{ route('secagens.show', $s) }}">#{{ $s->numero }}</a></td>
                    <td style="padding:10px 14px;">{{ $s->data->format('d/m/Y') }}</td>
                    <td style="padding:10px 14px;">{{ $s->secador }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ $s->items_count }}</td>
                    <td style="padding:10px 14px;">
                        @if($s->isConcluida())
                            <span class="badge badge-active">CONCLUÍDA</span>
                        @else
                            <span class="badge badge-trial">RASCUNHO</span>
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:right;">
                        @if($s->isRascunho())
                            @can('update', $s)<a href="{{ route('secagens.edit', $s) }}">editar</a>@endcan
                        @else
                            <a href="{{ route('secagens.show', $s) }}">ver</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:24px; text-align:center; color:#7d6b58;">Nenhuma secagem registrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px;">{{ $secagens->links() }}</div>
@endsection
