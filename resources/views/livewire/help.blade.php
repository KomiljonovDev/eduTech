<div class="space-y-6">
    <div>
        <flux:heading size="xl">Qo'llanma</flux:heading>
        <flux:subheading>Platformadan foydalanish bo'yicha yordam</flux:subheading>
    </div>

    <div class="flex items-start max-md:flex-col">
        {{-- Sidebar Navigation --}}
        <div class="me-10 w-full pb-4 md:w-[220px]">
            <flux:navlist>
                @foreach ($sections as $key => $sectionData)
                    <flux:navlist.item
                        wire:click="$set('section', '{{ $key }}')"
                        :icon="$sectionData['icon']"
                        :current="$section === $key"
                        class="cursor-pointer"
                    >
                        {{ $sectionData['title'] }}
                    </flux:navlist.item>
                @endforeach
            </flux:navlist>
        </div>

        <flux:separator class="md:hidden" />

        {{-- Content --}}
        <div class="flex-1 self-stretch max-md:pt-6">
            <div class="flex items-center gap-3 mb-4">
                <flux:icon :name="$currentSection['icon']" variant="outline" class="size-6" />
                <flux:heading size="lg">{{ $currentSection['title'] }}</flux:heading>
            </div>

            <div class="mt-5 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                {!! $currentSection['content'] !!}
            </div>
        </div>
    </div>
</div>
