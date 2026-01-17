<div class="space-y-4">
    @foreach ($notifications as $notification)
        <div class="rounded-lg p-3 border
            {{ $notification->is_read ? 'bg-gray-900 border-gray-800' : 'bg-yellow-900/20 border-yellow-600' }}">
            <div class="font-semibold">
                {{ $notification->title }}
            </div>
            <div class="text-sm opacity-80">
                {{ $notification->body }}
            </div>
        </div>
    @endforeach

    <div class="pt-4 border-t border-gray-800">
        <form wire:submit.prevent="sendNotification">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.defer="title"
                    placeholder="Sarlavha"
                />
            </x-filament::input.wrapper>

            <x-filament::input.wrapper class="mt-2">
                <textarea
                    wire:model.defer="body"
                    class="w-full rounded-lg bg-gray-900 border-gray-700"
                    rows="3"
                    placeholder="Xabar matni"
                ></textarea>
            </x-filament::input.wrapper>

            <x-filament::button class="mt-3" type="submit">
                Yangi bildirishnoma yuborish
            </x-filament::button>
        </form>
    </div>
</div>
