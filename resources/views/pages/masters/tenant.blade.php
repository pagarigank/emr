<x-layouts.app>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Companies / Tenants</h1>
                <p class="mt-1 text-sm text-gray-500">Manage hospital branches and companies.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @livewire('masters.tenant-crud')
        </div>
    </div>
</x-layouts.app>
