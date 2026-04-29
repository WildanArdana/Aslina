<x-filament-widgets::widget>
    <x-filament::card>
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
            
            <div>
                <p class="text-sm text-gray-500 mb-1 ml-10">Waktu Sekarang</p>
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 x-text="time" class="text-4xl font-bold text-black tracking-wider">Memuat...</h2>
                </div>
                <p x-text="date" class="text-sm text-gray-500 mt-1 ml-10"></p>
            </div>

            <div class="mt-4 md:mt-0 px-6 py-3 border border-gray-200 rounded-lg text-right">
                <h3 class="text-sm font-bold text-black">Status: Aktif</h3>
                <p class="text-xs text-gray-500">Sistem Berjalan Normal</p>
            </div>
            
        </div>
    </x-filament::card>
</x-filament-widgets::widget>