<x-layout>
    <x-slot:title>{{ $title }} — RideMyCars</x-slot>
    <div class="pt-24 pb-12 bg-white dark:bg-[#09090b] min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">{{ $title }}</h1>
            <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400 text-lg">
                <p>Welcome to the {{ $title }} page. We are currently working on adding more information here.</p>
                <p>Please check back later for updates!</p>
            </div>
        </div>
    </div>
</x-layout>
