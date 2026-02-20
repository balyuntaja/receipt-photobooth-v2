<div class="flex items-center justify-center gap-3 border-b border-gray-200 bg-white py-4 dark:border-gray-700 dark:bg-gray-900">
    <button
        type="button"
        @click="zoomOut()"
        class="h-10 w-16 rounded-lg border-2 border-primary-500 bg-white text-lg font-bold text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400">
        -
    </button>
    <span class="min-w-[64px] text-center text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="Math.round(zoom * 100) + '%'"></span>
    <button
        type="button"
        @click="zoomIn()"
        class="h-10 w-16 rounded-lg border-2 border-primary-500 bg-white text-lg font-bold text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400">
        +
    </button>
    <button
        type="button"
        @click="fit()"
        class="flex h-10 w-12 items-center justify-center rounded-lg border-2 border-primary-500 bg-white text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400"
        title="Fit">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m13-5h3a2 2 0 012 2v3M3 16v3a2 2 0 002 2h3m13-5v3a2 2 0 01-2 2h-3"/>
        </svg>
    </button>
    <button
        type="button"
        @click="reset()"
        class="flex h-10 w-12 items-center justify-center rounded-lg border-2 border-primary-500 bg-white text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400"
        title="Reset">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10a7 7 0 0112-2M19 14a7 7 0 01-12 2"/>
        </svg>
    </button>
</div>
