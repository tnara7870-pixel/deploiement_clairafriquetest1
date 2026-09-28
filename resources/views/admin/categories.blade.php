@extends('layouts.admin')
@section('title', 'Catégories')
@section('page_title', 'Catégories')

@section('content')

<div class="mb-4">
    <span class="text-sm text-gray-500">{{ $categories->total() }} catégorie(s)</span>
</div>

<div class="bg-white border border-primary-pale rounded-xl overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-primary-bg border-b border-primary-pale">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Nom</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Description</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Articles</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-primary-pale">
            @forelse($categories as $cat)
            <tr class="hover:bg-primary-bg">
                <td class="px-4 py-3 text-sm font-medium text-gray-800">
                    {{ $cat->nomCategorie }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ $cat->description ?? '—' }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                    {{ $cat->articles_count }}
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $cat->statut
                            ? 'bg-primary-pale text-primary-dark'
                            : 'bg-red-100 text-red-600' }}">
                        {{ $cat->statut ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucune catégorie.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-primary-pale">
        {{ $categories->links() }}
    </div>
</div>

@endsection