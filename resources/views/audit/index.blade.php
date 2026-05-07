@extends('layouts.app')
@section('title', 'Auditoria')
@section('content')
<h1 class="page">Auditoria</h1>

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">Quando</th>
                <th style="text-align:left; padding:10px 14px;">Por</th>
                <th style="text-align:left; padding:10px 14px;">Descrição</th>
                <th style="text-align:left; padding:10px 14px;">Entidade</th>
                <th style="text-align:left; padding:10px 14px;">ID</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $a)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;">{{ $a->created_at->format('d/m/Y H:i:s') }}</td>
                    <td style="padding:10px 14px;">{{ $a->causer?->name ?? 'sistema' }}</td>
                    <td style="padding:10px 14px;">{{ $a->description }}</td>
                    <td style="padding:10px 14px;">{{ class_basename($a->subject_type) }}</td>
                    <td style="padding:10px 14px;">#{{ $a->subject_id }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:24px; text-align:center; color:#7d6b58;">Sem registros.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px;">{{ $activities->links() }}</div>
@endsection
