
@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories Management')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">All Categories</h2>
            <p class="text-gray-600">Manage event categories</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add New Category
            </a>
        </div>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="GET" action="{{ route('categories.index') }}" class="flex items-center space-x-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search categories by name..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('categories.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
            <!-- Category Header -->
            <div class="h-32 bg-gradient-to-r from-purple-500 to-blue-500 relative">
                <div class="flex items-center justify-center h-full">
                    <i class="fas fa-tag text-6xl text-white opacity-50"></i>
                </div>
            </div>

           <!-- Category Details -->
<div class="p-6 space-y-4">
    <div class="flex items-start space-x-4">
        <div class="flex-1">
            <h3 class="text-xl font-semibold text-gray-900">{{ $category->name }}</h3>
            <div class="flex items-center mt-2 text-gray-600 text-sm">
                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                <span>{{ $category->events->count() }} event{{ $category->events->count() == 1 ? '' : 's' }}</span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <a href="{{ route('categories.show', $category) }}" class="inline-flex items-center px-3 py-1.5 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors rounded-md hover:bg-blue-50">
            <i class="fas fa-eye mr-2"></i>
            View Details
        </a>
        <div class="flex items-center space-x-3">
            <a href="{{ route('categories.edit', $category) }}" class="inline-flex items-center p-2 text-gray-600 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50">
                <i class="fas fa-edit text-lg"></i>
            </a>
            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center p-2 text-gray-600 hover:text-red-600 transition-colors rounded-full hover:bg-red-50">
                    <i class="fas fa-trash text-lg"></i>
                </button>
            </form>
        </div>
    </div>
</div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <i class="fas fa-tag text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Categories Found</h3>
                <p class="text-gray-500 mb-6">
                    @if(request('search'))
                        No categories match your search criteria.
                    @else
                        Get started by adding your first category.
                    @endif
                </p>
                @if(!request('search'))
                <a href="{{ route('categories.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Your First Category
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
    <div class="bg-white rounded-xl shadow-lg p-6">
        {{ $categories->appends(request()->query())->links() }}
    </div>
    @endif
</div>

@push('scripts')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
@endsection
