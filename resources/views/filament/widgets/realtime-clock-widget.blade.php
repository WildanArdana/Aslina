<x-filament-widgets::widget>
    <x-filament::card class="mb-4 border-0 shadow-sm">
        <div 
            x-data="{
                time: 'Memuat...',
                date: '',
                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                },
                updateClock() {
                    const now = new Date();
                    this.time = now.toLocaleTimeString('id-ID', { hour12: false }).replace(/\./g, ':');
                    this.date = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                }
            }"
            class="flex flex-col md:flex-row justify-between items-center w-full"
        >
            
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-green-100 text-green-600 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Waktu Sekarang</p>
                    <h2 x-text="time" class="text-3xl font-bold text-gray-800 tracking-wider">Memuat...</h2>
                    <p x-text="date" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>

            <div class="mt-4 md:mt-0 px-6 py-3 bg-green-50 border border-green-200 rounded-lg text-right">
                <h3 class="text-sm font-bold text-green-700 tracking-wide uppercase">Status: Aktif</h3>
                <p class="text-xs text-green-600">Sistem Berjalan Normal</p>
            </div>
            
        </div>
    </x-filament::card>
</x-filament-widgets::widget>