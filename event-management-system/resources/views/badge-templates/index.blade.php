@extends('layouts.app')

@section('title', 'Badge Templates')
@section('page-title', 'Badge Templates')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Badge Templates</h2>
            <p class="text-gray-600">Design and manage badge templates for events</p>
        </div>
        <a href="{{ route('badge-templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Create Template
        </a>
    </div>
    
    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $template)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($template->background_image)
            <div class="h-32 bg-gray-200 bg-cover bg-center" style="background-image: url('{{ Storage::url($template->background_image) }}')"></div>
            @else
            <div class="h-32 bg-gradient-to-r from-blue-400 to-purple-500 flex items-center justify-center">
                <i class="fas fa-id-card text-4xl text-white"></i>
            </div>
            @endif
            
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $template->name }}</h3>
                
                <div class="space-y-2 text-sm text-gray-500 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2"></i>
                        {{ $template->ticket->event->name }}
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-ticket-alt mr-2"></i>
                        {{ $template->ticket->name }}
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-ruler-combined mr-2"></i>
                        {{ $template->width }}cm × {{ $template->height }}cm
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-layer-group mr-2"></i>
                        {{ $template->contents()->count() }} elements
                    </div>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t">
                    <div class="flex space-x-2">
                        <a href="{{ route('badge-templates.preview', $template) }}" class="text-green-600 hover:text-green-800" title="Preview">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('badge-templates.show', $template) }}" class="text-blue-600 hover:text-blue-800" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('badge-templates.destroy', $template) }}" class="inline" 
                              onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $template->created_at->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-id-card text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No badge templates found</h3>
            <p class="text-gray-500 mb-4">Create your first badge template to get started.</p>
            <a href="{{ route('badge-templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Create Template
            </a>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($templates->hasPages())
    <div class="bg-white px-4 py-3 rounded-lg shadow">
        {{ $templates->links() }}
    </div>
    @endif
</div>
@endsection